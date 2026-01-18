<?php
/**
 * Payment Receipt email template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$payment_type = $this->current_payment['type'];
$payment_amount = $this->current_payment['amount'];
$payment_method = $this->current_payment['method'];

// Calculate totals
$total_due = $this->contract->calculated->total_compensation;
$deposit_received = floatval( $this->contract->deposit_amount_received ?? 0 );
$balance_received = floatval( $this->contract->balance_amount_received ?? 0 );

// Add current payment to totals if not yet reflected
if ( $payment_type === 'deposit' && ! $this->contract->deposit_paid ) {
    $deposit_received = $payment_amount;
} elseif ( $payment_type === 'balance' && ! $this->contract->balance_paid ) {
    $balance_received = $payment_amount;
}

$total_received = $deposit_received + $balance_received;
$remaining_balance = $total_due - $total_received;

// Payment method labels
$method_labels = array(
    'check' => 'Check',
    'cash'  => 'Cash',
    'card'  => 'Credit Card',
);
$method_label = $method_labels[ $payment_method ] ?? ucfirst( $payment_method );

$content = '
<h1>Payment Receipt</h1>

<p>Dear ' . esc_html( $this->contract->contact_person_name ) . ',</p>

<p>Thank you for your payment! This email confirms that we have received your <strong>' . esc_html( ucfirst( $payment_type ) ) . '</strong> payment.</p>

<div class="details-box" style="background: #ecf7ed; border-left-color: #28a745;">
    <strong style="color: #28a745;">Payment Received</strong>
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="padding: 5px 0;"><strong>Payment Type:</strong></td>
            <td style="padding: 5px 0; text-align: right;">' . esc_html( ucfirst( $payment_type ) ) . '</td>
        </tr>
        <tr>
            <td style="padding: 5px 0;"><strong>Amount:</strong></td>
            <td style="padding: 5px 0; text-align: right; font-size: 20px; color: #28a745;"><strong>' . esc_html( smcb_format_currency( $payment_amount ) ) . '</strong></td>
        </tr>
        <tr>
            <td style="padding: 5px 0;"><strong>Payment Method:</strong></td>
            <td style="padding: 5px 0; text-align: right;">' . esc_html( $method_label ) . '</td>
        </tr>
        <tr>
            <td style="padding: 5px 0;"><strong>Date:</strong></td>
            <td style="padding: 5px 0; text-align: right;">' . esc_html( date( 'F j, Y' ) ) . '</td>
        </tr>
    </table>
</div>

<div class="details-box">
    <strong>Event Details</strong>
    <p><strong>Event:</strong> ' . esc_html( $this->contract->event_name ) . '<br>
    <strong>Date:</strong> ' . esc_html( smcb_format_date( $this->contract->performance_date ) ) . '<br>
    <strong>Contract #:</strong> ' . esc_html( $this->contract->contract_number ) . '</p>
</div>

<h2>Payment Summary</h2>
<table style="width: 100%; border-collapse: collapse; max-width: 400px;">
    <tr>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Total Contract Amount</td>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right;">' . esc_html( smcb_format_currency( $total_due ) ) . '</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Deposit Received</td>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right; color: #28a745;">' . esc_html( smcb_format_currency( $deposit_received ) ) . '</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee;">Balance Received</td>
        <td style="padding: 8px 0; border-bottom: 1px solid #eee; text-align: right; color: #28a745;">' . esc_html( smcb_format_currency( $balance_received ) ) . '</td>
    </tr>
    <tr>
        <td style="padding: 8px 0; border-bottom: 2px solid #333;"><strong>Total Paid</strong></td>
        <td style="padding: 8px 0; border-bottom: 2px solid #333; text-align: right;"><strong style="color: #28a745;">' . esc_html( smcb_format_currency( $total_received ) ) . '</strong></td>
    </tr>';

if ( $remaining_balance > 0 ) {
    $content .= '
    <tr>
        <td style="padding: 8px 0;"><strong>Remaining Balance</strong></td>
        <td style="padding: 8px 0; text-align: right;"><strong style="color: #c41230;">' . esc_html( smcb_format_currency( $remaining_balance ) ) . '</strong></td>
    </tr>';
} else {
    $content .= '
    <tr>
        <td colspan="2" style="padding: 15px 0; text-align: center; background: #ecf7ed; color: #28a745; font-weight: bold;">
            PAID IN FULL
        </td>
    </tr>';
}

$content .= '
</table>';

if ( $remaining_balance > 0 && $payment_type === 'deposit' ) {
    $content .= '
<p style="margin-top: 20px;"><strong>Balance Due:</strong> The remaining balance of ' . esc_html( smcb_format_currency( $remaining_balance ) ) . ' is due on the day of the performance.</p>';
}

$content .= '
<p>Please keep this receipt for your records. If you have any questions, feel free to reach out to us.</p>

<p>Thank you for your business!</p>

<p>Best regards,<br>
<strong>Skinny Moo</strong><br>
' . esc_html( SMCB_COMPANY_NAME ) . '</p>
';

echo SMCB_Email::wrap_in_template( $content, 'Payment Receipt - ' . $this->contract->event_name );
