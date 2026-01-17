<?php
/**
 * Contract Sent email template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$content = '
<h1>Performance Agreement</h1>

<p>Dear ' . esc_html( $this->contract->contact_person_name ) . ',</p>

<p>Thank you for booking Skinny Moo for your upcoming event! We\'re excited to be part of ' . esc_html( $this->contract->event_name ) . '.</p>

<p>Please click the "View & Sign Contract" button below to review the Performance Agreement and Invoice for your event:</p>

<div class="details-box">
    <strong>Event Details</strong>
    <p><strong>Event:</strong> ' . esc_html( $this->contract->event_name ) . '<br>
    <strong>Date:</strong> ' . esc_html( smcb_format_date( $this->contract->performance_date ) ) . '<br>
    <strong>Load-in Time:</strong> ' . esc_html( smcb_format_time( $this->contract->load_in_time ) ) . '<br>
    <strong>Performance Time:</strong> ' . esc_html( smcb_format_time( $this->contract->first_set_start_time ) ) . '<br>
    <strong>Agreed Performance Rate:</strong> <span class="highlight">' . esc_html( smcb_format_currency( $this->contract->calculated->total_compensation ) ) . '</span></p>
</div>

<p style="text-align: center;">
    <a href="' . esc_url( $contract_url ) . '" class="button">View & Sign Contract</a>
</p>

<p>Once you\'ve reviewed the agreement, please sign it electronically using the link above. You\'ll receive a confirmation email with copies of the signed documents.</p>

<h2>Payment Information</h2>
<ul>
    <li><strong>Deposit Due:</strong> ' . esc_html( smcb_format_currency( $this->contract->calculated->deposit_amount ) ) . ' (' . esc_html( $this->contract->deposit_percentage ) . '%)</li>
    <li><strong>Balance Due at Event:</strong> ' . esc_html( smcb_format_currency( $this->contract->calculated->balance_due ) ) . '</li>
</ul>

<p>If you have any questions or need to make changes, please don\'t hesitate to reach out.</p>

<p>We look forward to performing for you!</p>

<p>Best regards,<br>
<strong>Skinny Moo</strong><br>
' . esc_html( SMCB_COMPANY_NAME ) . '</p>
';

echo SMCB_Email::wrap_in_template( $content, 'Performance Agreement - ' . $this->contract->event_name );
