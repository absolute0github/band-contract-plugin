<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php printf( esc_html__( 'Performance Agreement - %s', 'skinny-moo-contract-builder' ), esc_html( $contract->event_name ) ); ?></title>
    <?php wp_head(); ?>
</head>
<body class="smcb-contract-page">

<div class="smcb-page-wrapper">
    <!-- Header -->
    <header class="smcb-header">
        <div class="smcb-container">
            <div class="smcb-logo">
                <img src="<?php echo esc_url( SMCB_PLUGIN_URL . 'assets/images/logo.png' ); ?>" alt="<?php echo esc_attr( SMCB_COMPANY_NAME ); ?>">
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="smcb-main">
        <div class="smcb-container">

            <?php if ( $contract->status === 'signed' ) : ?>
                <!-- Already Signed Message -->
                <div class="smcb-message smcb-message-success">
                    <span class="smcb-message-icon">&#10003;</span>
                    <div class="smcb-message-content">
                        <h2><?php esc_html_e( 'Contract Signed', 'skinny-moo-contract-builder' ); ?></h2>
                        <p><?php printf(
                            esc_html__( 'This contract was signed by %s on %s.', 'skinny-moo-contract-builder' ),
                            esc_html( $contract->client_signed_name ),
                            esc_html( smcb_format_date( $contract->client_signed_at ) )
                        ); ?></p>
                        <p><?php esc_html_e( 'A confirmation email with the signed documents was sent to your email address.', 'skinny-moo-contract-builder' ); ?></p>
                    </div>
                </div>
            <?php elseif ( ! $can_sign['valid'] ) : ?>
                <!-- Cannot Sign Message -->
                <div class="smcb-message smcb-message-warning">
                    <span class="smcb-message-icon">!</span>
                    <div class="smcb-message-content">
                        <h2><?php esc_html_e( 'Notice', 'skinny-moo-contract-builder' ); ?></h2>
                        <p><?php echo esc_html( $can_sign['message'] ); ?></p>
                        <p><?php printf(
                            esc_html__( 'Please contact us at %s for assistance.', 'skinny-moo-contract-builder' ),
                            '<a href="mailto:' . esc_attr( SMCB_COMPANY_EMAIL ) . '">' . esc_html( SMCB_COMPANY_EMAIL ) . '</a>'
                        ); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Contract Header -->
            <div class="smcb-contract-header">
                <h1><?php esc_html_e( 'Performance Agreement', 'skinny-moo-contract-builder' ); ?></h1>
                <p class="smcb-contract-number"><?php echo esc_html( $contract->contract_number ); ?></p>
            </div>

            <!-- Event Summary -->
            <div class="smcb-event-summary">
                <div class="smcb-event-summary-item">
                    <span class="smcb-label"><?php esc_html_e( 'Event', 'skinny-moo-contract-builder' ); ?></span>
                    <span class="smcb-value"><?php echo esc_html( $contract->event_name ); ?></span>
                </div>
                <div class="smcb-event-summary-item">
                    <span class="smcb-label"><?php esc_html_e( 'Date', 'skinny-moo-contract-builder' ); ?></span>
                    <span class="smcb-value"><?php echo esc_html( smcb_format_date( $contract->performance_date ) ); ?></span>
                </div>
                <div class="smcb-event-summary-item">
                    <span class="smcb-label"><?php esc_html_e( 'Performance Time', 'skinny-moo-contract-builder' ); ?></span>
                    <span class="smcb-value"><?php echo esc_html( smcb_format_time( $contract->first_set_start_time ) ); ?></span>
                </div>
                <div class="smcb-event-summary-item">
                    <span class="smcb-label"><?php esc_html_e( 'Agreed Performance Rate', 'skinny-moo-contract-builder' ); ?></span>
                    <span class="smcb-value smcb-value-highlight"><?php echo esc_html( smcb_format_currency( $contract->calculated->total_compensation ) ); ?></span>
                </div>
            </div>

            <!-- Parties Section -->
            <section class="smcb-section">
                <h2><?php esc_html_e( 'Parties', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-parties">
                    <div class="smcb-party">
                        <h3><?php esc_html_e( 'Performer', 'skinny-moo-contract-builder' ); ?></h3>
                        <p><strong><?php echo esc_html( SMCB_COMPANY_NAME ); ?></strong></p>
                        <p><?php echo esc_html( SMCB_COMPANY_ADDRESS ); ?></p>
                        <p><?php echo esc_html( SMCB_COMPANY_CITY . ', ' . SMCB_COMPANY_STATE . ' ' . SMCB_COMPANY_ZIP ); ?></p>
                        <p><?php echo esc_html( SMCB_COMPANY_PHONE ); ?></p>
                        <p><a href="mailto:<?php echo esc_attr( SMCB_COMPANY_EMAIL ); ?>"><?php echo esc_html( SMCB_COMPANY_EMAIL ); ?></a></p>
                    </div>
                    <div class="smcb-party">
                        <h3><?php esc_html_e( 'Client', 'skinny-moo-contract-builder' ); ?></h3>
                        <p><strong><?php echo esc_html( $contract->client_company_name ); ?></strong></p>
                        <p><?php echo esc_html( $contract->contact_person_name ); ?></p>
                        <p><?php echo esc_html( $contract->street_address ); ?></p>
                        <p><?php echo esc_html( $contract->city . ', ' . $contract->state . ' ' . $contract->zip_code ); ?></p>
                        <p><?php echo esc_html( $contract->phone ); ?></p>
                    </div>
                </div>
            </section>

            <!-- Performance Details -->
            <section class="smcb-section">
                <h2><?php esc_html_e( 'Performance Details', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-details-grid">
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Event Name', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( $contract->event_name ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Date', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( smcb_format_date( $contract->performance_date ) ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Load-in Time', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( smcb_format_time( $contract->load_in_time ) ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Performance Start', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( smcb_format_time( $contract->first_set_start_time ) ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Number of Sets', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( $contract->number_of_sets ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Set/Break Length', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( $contract->set_length . ' min / ' . $contract->break_length . ' min break' ); ?></span>
                    </div>
                </div>

                <!-- Set Schedule -->
                <div class="smcb-set-schedule">
                    <h3><?php esc_html_e( 'Set Schedule', 'skinny-moo-contract-builder' ); ?></h3>
                    <div class="smcb-sets">
                        <?php foreach ( $contract->calculated->set_times as $set ) : ?>
                            <div class="smcb-set">
                                <span class="smcb-set-number"><?php printf( esc_html__( 'Set %d', 'skinny-moo-contract-builder' ), esc_html( $set['set_number'] ) ); ?></span>
                                <span class="smcb-set-time"><?php echo esc_html( $set['start'] . ' - ' . $set['end'] ); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>

            <!-- Venue Details -->
            <?php if ( ! empty( $contract->venue_name ) || ! empty( $contract->venue_address ) ) : ?>
            <section class="smcb-section">
                <h2><?php esc_html_e( 'Venue', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-details-grid">
                    <?php if ( ! empty( $contract->venue_name ) ) : ?>
                        <div class="smcb-detail smcb-detail-full">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Venue Name', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value"><?php echo esc_html( $contract->venue_name ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_address ) ) : ?>
                        <div class="smcb-detail smcb-detail-full">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Address', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value">
                                <?php echo esc_html( $contract->venue_address ); ?>
                                <?php if ( ! empty( $contract->venue_city ) ) : ?>
                                    <br><?php echo esc_html( $contract->venue_city . ', ' . $contract->venue_state . ' ' . $contract->venue_zip ); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_contact_person ) ) : ?>
                        <div class="smcb-detail">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Venue Contact', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value"><?php echo esc_html( $contract->venue_contact_person ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_phone ) ) : ?>
                        <div class="smcb-detail">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Venue Phone', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value"><?php echo esc_html( $contract->venue_phone ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->loadin_location ) ) : ?>
                        <div class="smcb-detail smcb-detail-full">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Load-in Location', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value"><?php echo esc_html( $contract->loadin_location ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->performance_location ) ) : ?>
                        <div class="smcb-detail smcb-detail-full">
                            <span class="smcb-detail-label"><?php esc_html_e( 'Performance Location', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-detail-value"><?php echo esc_html( $contract->performance_location ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Production -->
            <section class="smcb-section">
                <h2><?php esc_html_e( 'Production', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-details-grid">
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Location', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( ucfirst( $contract->inside_outside ) ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Stage', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( strtoupper( $contract->stage_available ) ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Sound System', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( $production_options[ $contract->sound_system ] ?? $contract->sound_system ); ?></span>
                    </div>
                    <div class="smcb-detail">
                        <span class="smcb-detail-label"><?php esc_html_e( 'Lights', 'skinny-moo-contract-builder' ); ?></span>
                        <span class="smcb-detail-value"><?php echo esc_html( $production_options[ $contract->lights ] ?? $contract->lights ); ?></span>
                    </div>
                </div>

                <?php if ( ! empty( $contract->services_description ) ) : ?>
                    <div class="smcb-services">
                        <h3><?php esc_html_e( 'Services Provided', 'skinny-moo-contract-builder' ); ?></h3>
                        <p><?php echo esc_html( $contract->services_description ); ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Terms and Conditions -->
            <section class="smcb-section smcb-terms-section">
                <h2><?php esc_html_e( 'Terms and Conditions', 'skinny-moo-contract-builder' ); ?></h2>
                <p class="smcb-scroll-instruction"><?php esc_html_e( 'Scroll within this window to see all the terms & conditions listed.', 'skinny-moo-contract-builder' ); ?></p>
                <div class="smcb-terms">
                    <ol>
                        <li><strong><?php esc_html_e( 'Deposit:', 'skinny-moo-contract-builder' ); ?></strong> <?php printf( esc_html__( 'A non-refundable deposit of %d%% (%s) is due upon signing of this agreement. The remaining balance is due on the day of the performance.', 'skinny-moo-contract-builder' ), esc_html( $contract->deposit_percentage ), esc_html( smcb_format_currency( $contract->calculated->deposit_amount ) ) ); ?></li>
                        <li><strong><?php esc_html_e( 'Cancellation:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'If CLIENT cancels this engagement, the deposit is non-refundable. If cancellation occurs within 30 days of the performance date, CLIENT agrees to pay 50% of the total contract amount. If cancellation occurs within 14 days of the performance date, CLIENT agrees to pay 100% of the total contract amount.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Cancellation by Performer:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'If PERFORMER must cancel this engagement due to circumstances beyond their control, PERFORMER will make every reasonable effort to find a suitable replacement. If no replacement can be found, all payments made by CLIENT will be refunded in full.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Force Majeure:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'Neither party shall be liable for failure to perform due to circumstances beyond their reasonable control, including but not limited to: acts of God, natural disasters, government actions, pandemic, or venue closure.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Sound and Lighting:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'As specified in this agreement, sound and lighting equipment will be provided as noted. Any additional equipment required must be arranged and paid for separately.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Meals:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'If the performance extends through normal meal times, CLIENT agrees to provide a meal for the performing members.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Parking:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'CLIENT will provide convenient and safe parking for PERFORMER vehicles and equipment at no charge.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Safety:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'CLIENT will provide a safe performance environment. PERFORMER reserves the right to stop performance if conditions become unsafe.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Recording:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'PERFORMER may record audio and/or video of the performance for promotional purposes. CLIENT may photograph or record the performance for personal, non-commercial use.', 'skinny-moo-contract-builder' ); ?></li>
                        <li><strong><?php esc_html_e( 'Entire Agreement:', 'skinny-moo-contract-builder' ); ?></strong> <?php esc_html_e( 'This agreement constitutes the entire agreement between the parties and supersedes all prior negotiations, understandings, and agreements between the parties.', 'skinny-moo-contract-builder' ); ?></li>
                    </ol>
                </div>

                <?php if ( ! empty( $contract->additional_contract_notes ) ) : ?>
                    <div class="smcb-additional-notes">
                        <h3><?php esc_html_e( 'Additional Notes', 'skinny-moo-contract-builder' ); ?></h3>
                        <p><?php echo esc_html( $contract->additional_contract_notes ); ?></p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Compensation -->
            <section class="smcb-section smcb-section-highlight">
                <h2><?php esc_html_e( 'Compensation', 'skinny-moo-contract-builder' ); ?></h2>
                <table class="smcb-compensation-table smcb-compensation-table-full">
                    <tbody>
                        <tr>
                            <td><?php esc_html_e( 'Base Compensation', 'skinny-moo-contract-builder' ); ?></td>
                            <td class="smcb-amount"><?php echo esc_html( smcb_format_currency( $contract->base_compensation ) ); ?></td>
                        </tr>
                        <?php if ( $contract->mileage_travel_fee > 0 ) : ?>
                            <tr>
                                <td><?php esc_html_e( 'Travel Fee', 'skinny-moo-contract-builder' ); ?></td>
                                <td class="smcb-amount"><?php echo esc_html( smcb_format_currency( $contract->mileage_travel_fee ) ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ( $contract->early_loadin_required && $contract->calculated->early_loadin_fee > 0 ) : ?>
                            <tr>
                                <td><?php printf( esc_html__( 'Early Load-in Fee (%d hours)', 'skinny-moo-contract-builder' ), esc_html( $contract->early_loadin_hours ) ); ?></td>
                                <td class="smcb-amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->early_loadin_fee ) ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="smcb-total-row">
                            <td><strong><?php esc_html_e( 'Total Performance Fees', 'skinny-moo-contract-builder' ); ?></strong></td>
                            <td class="smcb-amount"><strong><?php echo esc_html( smcb_format_currency( $contract->calculated->total_compensation ) ); ?></strong></td>
                        </tr>
                    </tbody>
                </table>

                <div class="smcb-payment-terms">
                    <h3><?php esc_html_e( 'Payment Terms', 'skinny-moo-contract-builder' ); ?></h3>
                    <div class="smcb-payment-breakdown">
                        <div class="smcb-payment-item">
                            <span class="smcb-payment-label"><?php printf( esc_html__( 'Deposit Due (%d%%)', 'skinny-moo-contract-builder' ), esc_html( $contract->deposit_percentage ) ); ?></span>
                            <span class="smcb-payment-amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->deposit_amount ) ); ?></span>
                        </div>
                        <div class="smcb-payment-item">
                            <span class="smcb-payment-label"><?php esc_html_e( 'Balance Due at Event', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="smcb-payment-amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->balance_due ) ); ?></span>
                        </div>
                    </div>
                </div>

                <?php if ( ! empty( $contract->additional_compensation ) ) : ?>
                    <p class="smcb-additional-comp">
                        <strong><?php esc_html_e( 'Additional:', 'skinny-moo-contract-builder' ); ?></strong>
                        <?php echo esc_html( $contract->additional_compensation ); ?>
                    </p>
                <?php endif; ?>
            </section>

            <!-- Signature Section -->
            <?php if ( $can_sign['valid'] && $contract->status !== 'signed' ) : ?>
                <section class="smcb-section smcb-signature-section" id="signature-section">
                    <h2><?php esc_html_e( 'Sign This Agreement', 'skinny-moo-contract-builder' ); ?></h2>

                    <p class="smcb-sign-intro"><?php esc_html_e( 'By signing below, you agree to the terms and conditions set forth in this Performance Agreement.', 'skinny-moo-contract-builder' ); ?></p>

                    <form id="smcb-signature-form" data-token="<?php echo esc_attr( $contract->access_token ); ?>">
                        <div class="smcb-form-row">
                            <label for="signed_name"><?php esc_html_e( 'Your Full Name', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="signed_name" name="signed_name" required placeholder="<?php esc_attr_e( 'Type your full legal name', 'skinny-moo-contract-builder' ); ?>">
                        </div>

                        <div class="smcb-form-row">
                            <label><?php esc_html_e( 'Your Signature', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <div class="smcb-signature-wrapper">
                                <canvas id="signature-pad" class="smcb-signature-pad"></canvas>
                                <input type="hidden" id="signature_data" name="signature_data">
                            </div>
                            <div class="smcb-signature-actions">
                                <button type="button" id="clear-signature" class="smcb-btn smcb-btn-secondary"><?php esc_html_e( 'Clear Signature', 'skinny-moo-contract-builder' ); ?></button>
                            </div>
                            <p class="smcb-signature-help"><?php esc_html_e( 'Draw your signature in the box above using your mouse or finger.', 'skinny-moo-contract-builder' ); ?></p>
                        </div>

                        <div class="smcb-form-row smcb-checkbox-row">
                            <label class="smcb-checkbox-label">
                                <input type="checkbox" id="agree_terms" name="agree_terms" required>
                                <span><?php esc_html_e( 'I have read and agree to the terms and conditions of this Performance Agreement.', 'skinny-moo-contract-builder' ); ?></span>
                            </label>
                        </div>

                        <div class="smcb-form-row">
                            <button type="submit" id="submit-signature" class="smcb-btn smcb-btn-primary smcb-btn-large">
                                <?php esc_html_e( 'Sign Contract', 'skinny-moo-contract-builder' ); ?>
                            </button>
                        </div>

                        <div id="signature-error" class="smcb-error-message" style="display: none;"></div>
                        <div id="signature-success" class="smcb-success-message" style="display: none;"></div>
                    </form>
                </section>
            <?php endif; ?>

            <!-- Signed Signature Display -->
            <?php if ( $contract->status === 'signed' && ! empty( $contract->client_signature ) ) : ?>
                <section class="smcb-section smcb-signed-section">
                    <h2><?php esc_html_e( 'Signature', 'skinny-moo-contract-builder' ); ?></h2>
                    <div class="smcb-signature-display">
                        <div class="smcb-signature-image">
                            <img src="<?php echo esc_attr( $contract->client_signature ); ?>" alt="<?php esc_attr_e( 'Client Signature', 'skinny-moo-contract-builder' ); ?>">
                        </div>
                        <div class="smcb-signature-details">
                            <p><strong><?php echo esc_html( $contract->client_signed_name ); ?></strong></p>
                            <p><?php echo esc_html( date( 'F j, Y \a\t g:i A', strtotime( $contract->client_signed_at ) ) ); ?></p>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

        </div>
    </main>

    <!-- Footer -->
    <footer class="smcb-footer">
        <div class="smcb-container">
            <p><?php echo esc_html( SMCB_COMPANY_NAME ); ?></p>
            <p><?php echo esc_html( SMCB_COMPANY_ADDRESS . ', ' . SMCB_COMPANY_CITY . ', ' . SMCB_COMPANY_STATE . ' ' . SMCB_COMPANY_ZIP ); ?></p>
            <p>
                <a href="tel:<?php echo esc_attr( SMCB_COMPANY_PHONE ); ?>"><?php echo esc_html( SMCB_COMPANY_PHONE ); ?></a> |
                <a href="mailto:<?php echo esc_attr( SMCB_COMPANY_EMAIL ); ?>"><?php echo esc_html( SMCB_COMPANY_EMAIL ); ?></a>
            </p>
        </div>
    </footer>
</div>

<?php wp_footer(); ?>
</body>
</html>
