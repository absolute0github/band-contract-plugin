<?php
/**
 * Admin Notification email template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$admin_url = admin_url( 'admin.php?page=smcb-contracts&action=view&id=' . $this->contract->id );

// Random fun sentences
$fun_sentences = array(
    "Bring the mic's for the drums and grab the merch!",
    "You're not gonna believe this but... they want US to play",
    "They hired us... they actually hired us!!",
    "We fooled em' again",
    "Time to get paid boys!",
    "We gonna turn this mutha out!",
);
$random_sentence = $fun_sentences[ array_rand( $fun_sentences ) ];

$content = '
<h1>Contract Signed!</h1>

<p>Great news! A contract has been signed by the client. <strong style="color: #c41230;">' . esc_html( $random_sentence ) . '</strong></p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
    <tr>
        <td width="48%" valign="top" style="background: #f9f9f9; border-radius: 8px; padding: 20px; border-left: 4px solid #c41230;">
            <strong style="font-size: 14px; text-transform: uppercase; color: #c41230;">Contract Info</strong>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                <strong>#' . esc_html( $this->contract->contract_number ) . '</strong><br>
                ' . esc_html( $this->contract->client_company_name ) . '<br>
                ' . esc_html( $this->contract->contact_person_name ) . '<br>
                <a href="mailto:' . esc_attr( $this->contract->email ) . '">' . esc_html( $this->contract->email ) . '</a><br>
                ' . esc_html( $this->contract->phone ) . '
            </p>
        </td>
        <td width="4%"></td>
        <td width="48%" valign="top" style="background: #f9f9f9; border-radius: 8px; padding: 20px; border-left: 4px solid #1a1a1a;">
            <strong style="font-size: 14px; text-transform: uppercase; color: #1a1a1a;">Event Details</strong>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                <strong>' . esc_html( $this->contract->event_name ) . '</strong><br>
                ' . esc_html( smcb_format_date( $this->contract->performance_date ) ) . '<br>
                ' . esc_html( smcb_format_time( $this->contract->first_set_start_time ) ) . '<br>
                ' . esc_html( ucfirst( $this->contract->inside_outside ) ) . '
            </p>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0;">
    <tr>
        <td width="48%" valign="top" style="background: #ecf7ed; border-radius: 8px; padding: 20px; border-left: 4px solid #28a745;">
            <strong style="font-size: 14px; text-transform: uppercase; color: #28a745;">Compensation</strong>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                <strong style="font-size: 24px; color: #28a745;">' . esc_html( smcb_format_currency( $this->contract->calculated->total_compensation ) ) . '</strong><br>
                Deposit: ' . esc_html( smcb_format_currency( $this->contract->calculated->deposit_amount ) ) . '<br>
                Balance: ' . esc_html( smcb_format_currency( $this->contract->calculated->balance_due ) ) . '
            </p>
        </td>
        <td width="4%"></td>
        <td width="48%" valign="top" style="background: #fff8e5; border-radius: 8px; padding: 20px; border-left: 4px solid #f1c40f;">
            <strong style="font-size: 14px; text-transform: uppercase; color: #d4a200;">Signature</strong>
            <p style="margin: 10px 0 0 0; font-size: 14px;">
                Signed by: <strong>' . esc_html( $this->contract->client_signed_name ) . '</strong><br>
                ' . esc_html( date( 'M j, Y \a\t g:i A', strtotime( $this->contract->client_signed_at ) ) ) . '<br>
                IP: ' . esc_html( $this->contract->client_signed_ip ) . '
            </p>
        </td>
    </tr>
</table>

<p style="text-align: center; margin: 30px 0;">
    <a href="' . esc_url( $admin_url ) . '" class="button">View Contract in Admin</a>
</p>

<p style="font-size: 14px; color: #666;"><strong>Next Steps:</strong> Follow up for deposit payment &bull; Add to calendar &bull; Confirm special requirements</p>
';

echo SMCB_Email::wrap_in_template( $content, 'Contract Signed: ' . $this->contract->client_company_name );
