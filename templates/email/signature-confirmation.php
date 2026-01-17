<?php
/**
 * Signature Confirmation email template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$content = '
<h1>Contract Signed Successfully!</h1>

<p>Dear ' . esc_html( $this->contract->contact_person_name ) . ',</p>

<p>Thank you for signing the Performance Agreement for <strong>' . esc_html( $this->contract->event_name ) . '</strong>. This email confirms that your contract has been successfully executed.</p>

<div class="details-box">
    <strong>Signed Contract Details</strong>
    <p><strong>Contract Number:</strong> ' . esc_html( $this->contract->contract_number ) . '<br>
    <strong>Invoice Number:</strong> ' . esc_html( $this->contract->invoice_number ) . '<br>
    <strong>Event:</strong> ' . esc_html( $this->contract->event_name ) . '<br>
    <strong>Date:</strong> ' . esc_html( smcb_format_date( $this->contract->performance_date ) ) . '<br>
    <strong>Signed By:</strong> ' . esc_html( $this->contract->client_signed_name ) . '<br>
    <strong>Signed On:</strong> ' . esc_html( date( 'F j, Y \a\t g:i A', strtotime( $this->contract->client_signed_at ) ) ) . '</p>
</div>

<h2>Next Steps</h2>
<ol>
    <li><strong>Deposit Payment:</strong> Please submit your deposit of ' . esc_html( smcb_format_currency( $this->contract->calculated->deposit_amount ) ) . ' at your earliest convenience to secure your date.</li>
    <li><strong>Balance:</strong> The remaining balance of ' . esc_html( smcb_format_currency( $this->contract->calculated->balance_due ) ) . ' is due on the day of the event.</li>
</ol>

<h2>Payment Methods</h2>
<ul>
    <li><strong>Check:</strong> Make payable to "' . esc_html( SMCB_COMPANY_NAME ) . '"</li>
    <li><strong>Venmo:</strong> @skinnymoo</li>
    <li><strong>PayPal:</strong> ' . esc_html( SMCB_COMPANY_EMAIL ) . '</li>
</ul>

<p>Your signed contract and invoice are attached to this email for your records.</p>

<p>If you have any questions before the event, please don\'t hesitate to contact us.</p>

<p>We\'re looking forward to making your event a success!</p>

<p>Best regards,<br>
<strong>Skinny Moo</strong><br>
' . esc_html( SMCB_COMPANY_NAME ) . '<br>
' . esc_html( SMCB_COMPANY_PHONE ) . '</p>
';

echo SMCB_Email::wrap_in_template( $content, 'Contract Signed - ' . $this->contract->event_name );
