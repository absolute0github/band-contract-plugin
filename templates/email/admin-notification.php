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

$content = '
<h1>Contract Signed!</h1>

<p>Great news! A contract has been signed by the client.</p>

<div class="details-box">
    <strong>Contract Details</strong>
    <p><strong>Contract Number:</strong> ' . esc_html( $this->contract->contract_number ) . '<br>
    <strong>Client:</strong> ' . esc_html( $this->contract->client_company_name ) . '<br>
    <strong>Contact:</strong> ' . esc_html( $this->contract->contact_person_name ) . '<br>
    <strong>Email:</strong> ' . esc_html( $this->contract->email ) . '<br>
    <strong>Phone:</strong> ' . esc_html( $this->contract->phone ) . '</p>
</div>

<div class="details-box">
    <strong>Event Details</strong>
    <p><strong>Event:</strong> ' . esc_html( $this->contract->event_name ) . '<br>
    <strong>Date:</strong> ' . esc_html( smcb_format_date( $this->contract->performance_date ) ) . '<br>
    <strong>Time:</strong> ' . esc_html( smcb_format_time( $this->contract->first_set_start_time ) ) . '<br>
    <strong>Location:</strong> ' . esc_html( ucfirst( $this->contract->inside_outside ) ) . '</p>
</div>

<div class="details-box">
    <strong>Compensation</strong>
    <p><strong>Total:</strong> ' . esc_html( smcb_format_currency( $this->contract->calculated->total_compensation ) ) . '<br>
    <strong>Deposit:</strong> ' . esc_html( smcb_format_currency( $this->contract->calculated->deposit_amount ) ) . '<br>
    <strong>Balance:</strong> ' . esc_html( smcb_format_currency( $this->contract->calculated->balance_due ) ) . '</p>
</div>

<div class="details-box">
    <strong>Signature Details</strong>
    <p><strong>Signed By:</strong> ' . esc_html( $this->contract->client_signed_name ) . '<br>
    <strong>Signed At:</strong> ' . esc_html( date( 'F j, Y \a\t g:i A', strtotime( $this->contract->client_signed_at ) ) ) . '<br>
    <strong>IP Address:</strong> ' . esc_html( $this->contract->client_signed_ip ) . '</p>
</div>

<p style="text-align: center;">
    <a href="' . esc_url( $admin_url ) . '" class="button">View Contract in Admin</a>
</p>

<h2>Next Steps</h2>
<ul>
    <li>Follow up with client for deposit payment</li>
    <li>Add event to calendar</li>
    <li>Confirm any special requirements or arrangements</li>
</ul>
';

echo SMCB_Email::wrap_in_template( $content, 'Contract Signed: ' . $this->contract->client_company_name );
