<?php
/**
 * Admin contract view template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$statuses = smcb_get_contract_statuses();
$production_options = smcb_get_production_options();
?>

<div class="wrap smcb-admin smcb-contract-view-page">
    <h1 class="wp-heading-inline">
        <?php printf( esc_html__( 'Contract: %s', 'skinny-moo-contract-builder' ), esc_html( $contract->contract_number ) ); ?>
    </h1>
    <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-add-contract', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Edit', 'skinny-moo-contract-builder' ); ?>
    </a>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=smcb-contracts' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Back to List', 'skinny-moo-contract-builder' ); ?>
    </a>
    <hr class="wp-header-end">

    <div class="smcb-view-container">
        <!-- Main Content -->
        <div class="smcb-view-main">
            <!-- Status Banner -->
            <div class="smcb-status-banner smcb-status-<?php echo esc_attr( $contract->status ); ?>">
                <span class="smcb-status-label"><?php echo esc_html( $statuses[ $contract->status ] ?? $contract->status ); ?></span>
                <?php if ( $contract->status === 'signed' ) : ?>
                    <span class="smcb-signed-info">
                        <?php
                        printf(
                            esc_html__( 'Signed by %s on %s', 'skinny-moo-contract-builder' ),
                            esc_html( $contract->client_signed_name ),
                            esc_html( date( 'F j, Y \a\t g:i A', strtotime( $contract->client_signed_at ) ) )
                        );
                        ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Client Information -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-businessman"></span> <?php esc_html_e( 'Client Information', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-view-grid">
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Company/Client', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $contract->client_company_name ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Contact Person', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $contract->contact_person_name ); ?></span>
                    </div>
                    <div class="smcb-view-field smcb-view-field-full">
                        <label><?php esc_html_e( 'Address', 'skinny-moo-contract-builder' ); ?></label>
                        <span>
                            <?php echo esc_html( $contract->street_address ); ?><br>
                            <?php echo esc_html( $contract->city . ', ' . $contract->state . ' ' . $contract->zip_code ); ?>
                        </span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Phone', 'skinny-moo-contract-builder' ); ?></label>
                        <span><a href="tel:<?php echo esc_attr( $contract->phone ); ?>"><?php echo esc_html( $contract->phone ); ?></a></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Email', 'skinny-moo-contract-builder' ); ?></label>
                        <span><a href="mailto:<?php echo esc_attr( $contract->email ); ?>"><?php echo esc_html( $contract->email ); ?></a></span>
                    </div>
                </div>
            </div>

            <!-- Performance Details -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-calendar-alt"></span> <?php esc_html_e( 'Performance Details', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-view-grid">
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Event Name', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $contract->event_name ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Date', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( smcb_format_date( $contract->performance_date ) ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Load-in Time', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( smcb_format_time( $contract->load_in_time ) ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'First Set Start', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( smcb_format_time( $contract->first_set_start_time ) ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Number of Sets', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $contract->number_of_sets ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Set/Break Length', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $contract->set_length . ' min / ' . $contract->break_length . ' min' ); ?></span>
                    </div>

                    <!-- Set Schedule -->
                    <div class="smcb-view-field smcb-view-field-full">
                        <label><?php esc_html_e( 'Set Schedule', 'skinny-moo-contract-builder' ); ?></label>
                        <div class="smcb-set-schedule">
                            <?php foreach ( $contract->calculated->set_times as $set ) : ?>
                                <span class="smcb-set-time">
                                    <strong><?php printf( esc_html__( 'Set %d:', 'skinny-moo-contract-builder' ), esc_html( $set['set_number'] ) ); ?></strong>
                                    <?php echo esc_html( $set['start'] . ' - ' . $set['end'] ); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Venue Details -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-building"></span> <?php esc_html_e( 'Venue Details', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-view-grid">
                    <?php if ( ! empty( $contract->venue_name ) ) : ?>
                        <div class="smcb-view-field">
                            <label><?php esc_html_e( 'Venue Name', 'skinny-moo-contract-builder' ); ?></label>
                            <span><?php echo esc_html( $contract->venue_name ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_contact_person ) ) : ?>
                        <div class="smcb-view-field">
                            <label><?php esc_html_e( 'Venue Contact', 'skinny-moo-contract-builder' ); ?></label>
                            <span><?php echo esc_html( $contract->venue_contact_person ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_address ) ) : ?>
                        <div class="smcb-view-field smcb-view-field-full">
                            <label><?php esc_html_e( 'Venue Address', 'skinny-moo-contract-builder' ); ?></label>
                            <span>
                                <?php echo esc_html( $contract->venue_address ); ?><br>
                                <?php
                                $venue_city_state_zip = array_filter( array( $contract->venue_city, $contract->venue_state, $contract->venue_zip ) );
                                if ( ! empty( $venue_city_state_zip ) ) {
                                    echo esc_html( $contract->venue_city . ', ' . $contract->venue_state . ' ' . $contract->venue_zip );
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_phone ) ) : ?>
                        <div class="smcb-view-field">
                            <label><?php esc_html_e( 'Venue Phone', 'skinny-moo-contract-builder' ); ?></label>
                            <span><a href="tel:<?php echo esc_attr( $contract->venue_phone ); ?>"><?php echo esc_html( $contract->venue_phone ); ?></a></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->venue_email ) ) : ?>
                        <div class="smcb-view-field">
                            <label><?php esc_html_e( 'Venue Email', 'skinny-moo-contract-builder' ); ?></label>
                            <span><a href="mailto:<?php echo esc_attr( $contract->venue_email ); ?>"><?php echo esc_html( $contract->venue_email ); ?></a></span>
                        </div>
                    <?php endif; ?>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Inside/Outside', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( ucfirst( $contract->inside_outside ) ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Stage Available', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( strtoupper( $contract->stage_available ) ); ?></span>
                    </div>
                    <?php if ( ! empty( $contract->power_requirements ) ) : ?>
                        <div class="smcb-view-field">
                            <label><?php esc_html_e( 'Power Requirements', 'skinny-moo-contract-builder' ); ?></label>
                            <span><?php echo esc_html( $contract->power_requirements ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->loadin_location ) ) : ?>
                        <div class="smcb-view-field smcb-view-field-full">
                            <label><?php esc_html_e( 'Load-in Location', 'skinny-moo-contract-builder' ); ?></label>
                            <span><?php echo esc_html( $contract->loadin_location ); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contract->performance_location ) ) : ?>
                        <div class="smcb-view-field smcb-view-field-full">
                            <label><?php esc_html_e( 'Performance Location', 'skinny-moo-contract-builder' ); ?></label>
                            <span><?php echo esc_html( $contract->performance_location ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Production -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-controls-volumeon"></span> <?php esc_html_e( 'Production', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-view-grid">
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Sound System', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $production_options[ $contract->sound_system ] ?? $contract->sound_system ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Lights', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $production_options[ $contract->lights ] ?? $contract->lights ); ?></span>
                    </div>
                    <div class="smcb-view-field">
                        <label><?php esc_html_e( 'Music Between Sets', 'skinny-moo-contract-builder' ); ?></label>
                        <span><?php echo esc_html( $production_options[ $contract->music_between_sets ] ?? $contract->music_between_sets ); ?></span>
                    </div>
                </div>
            </div>

            <!-- Compensation -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Compensation', 'skinny-moo-contract-builder' ); ?></h2>
                <div class="smcb-compensation-summary">
                    <table class="smcb-compensation-table">
                        <tr>
                            <td><?php esc_html_e( 'Base Compensation', 'skinny-moo-contract-builder' ); ?></td>
                            <td class="amount"><?php echo esc_html( smcb_format_currency( $contract->base_compensation ) ); ?></td>
                        </tr>
                        <?php if ( $contract->mileage_travel_fee > 0 ) : ?>
                            <tr>
                                <td><?php esc_html_e( 'Travel Fee', 'skinny-moo-contract-builder' ); ?></td>
                                <td class="amount"><?php echo esc_html( smcb_format_currency( $contract->mileage_travel_fee ) ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if ( $contract->early_loadin_required && $contract->calculated->early_loadin_fee > 0 ) : ?>
                            <tr>
                                <td><?php printf( esc_html__( 'Early Load-in Fee (%d hours)', 'skinny-moo-contract-builder' ), esc_html( $contract->early_loadin_hours ) ); ?></td>
                                <td class="amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->early_loadin_fee ) ); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="total-row">
                            <td><strong><?php esc_html_e( 'Total', 'skinny-moo-contract-builder' ); ?></strong></td>
                            <td class="amount"><strong><?php echo esc_html( smcb_format_currency( $contract->calculated->total_compensation ) ); ?></strong></td>
                        </tr>
                        <tr>
                            <td><?php printf( esc_html__( 'Deposit (%d%%)', 'skinny-moo-contract-builder' ), esc_html( $contract->deposit_percentage ) ); ?></td>
                            <td class="amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->deposit_amount ) ); ?></td>
                        </tr>
                        <tr>
                            <td><?php esc_html_e( 'Balance Due at Event', 'skinny-moo-contract-builder' ); ?></td>
                            <td class="amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->balance_due ) ); ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Payment Tracking -->
            <?php if ( $contract->status === 'signed' ) : ?>
            <div class="smcb-view-section smcb-payment-tracking">
                <h2><span class="dashicons dashicons-money-alt"></span> <?php esc_html_e( 'Payment Tracking', 'skinny-moo-contract-builder' ); ?></h2>

                <div class="smcb-payment-grid">
                    <!-- Deposit Payment -->
                    <div class="smcb-payment-box <?php echo $contract->deposit_paid ? 'smcb-payment-paid' : 'smcb-payment-pending'; ?>">
                        <h3>
                            <?php if ( $contract->deposit_paid ) : ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-clock"></span>
                            <?php endif; ?>
                            <?php esc_html_e( 'Deposit', 'skinny-moo-contract-builder' ); ?>
                        </h3>
                        <div class="smcb-payment-amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->deposit_amount ) ); ?></div>

                        <?php if ( $contract->deposit_paid ) : ?>
                            <div class="smcb-payment-details">
                                <p><strong><?php esc_html_e( 'Received:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( smcb_format_currency( $contract->deposit_amount_received ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Method:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( ucfirst( $contract->deposit_payment_method ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Date:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( date( 'M j, Y', strtotime( $contract->deposit_paid_at ) ) ); ?></p>
                                <?php if ( ! empty( $contract->deposit_payment_notes ) ) : ?>
                                    <p><strong><?php esc_html_e( 'Notes:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( $contract->deposit_payment_notes ); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <form class="smcb-payment-form" data-payment-type="deposit" data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Payment Method', 'skinny-moo-contract-builder' ); ?></label>
                                    <select name="payment_method" required>
                                        <option value=""><?php esc_html_e( 'Select...', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="check"><?php esc_html_e( 'Check', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="cash"><?php esc_html_e( 'Cash', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="card"><?php esc_html_e( 'Credit Card', 'skinny-moo-contract-builder' ); ?></option>
                                    </select>
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Amount Received', 'skinny-moo-contract-builder' ); ?></label>
                                    <input type="number" name="amount" step="0.01" value="<?php echo esc_attr( $contract->calculated->deposit_amount ); ?>" required>
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Notes (optional)', 'skinny-moo-contract-builder' ); ?></label>
                                    <input type="text" name="notes" placeholder="<?php esc_attr_e( 'Check #, transaction ID, etc.', 'skinny-moo-contract-builder' ); ?>">
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label>
                                        <input type="checkbox" name="send_receipt" value="1" checked>
                                        <?php esc_html_e( 'Send receipt to client', 'skinny-moo-contract-builder' ); ?>
                                    </label>
                                </div>
                                <button type="submit" class="button button-primary"><?php esc_html_e( 'Record Payment', 'skinny-moo-contract-builder' ); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Balance Payment -->
                    <div class="smcb-payment-box <?php echo $contract->balance_paid ? 'smcb-payment-paid' : 'smcb-payment-pending'; ?>">
                        <h3>
                            <?php if ( $contract->balance_paid ) : ?>
                                <span class="dashicons dashicons-yes-alt"></span>
                            <?php else : ?>
                                <span class="dashicons dashicons-clock"></span>
                            <?php endif; ?>
                            <?php esc_html_e( 'Balance', 'skinny-moo-contract-builder' ); ?>
                        </h3>
                        <div class="smcb-payment-amount"><?php echo esc_html( smcb_format_currency( $contract->calculated->balance_due ) ); ?></div>

                        <?php if ( $contract->balance_paid ) : ?>
                            <div class="smcb-payment-details">
                                <p><strong><?php esc_html_e( 'Received:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( smcb_format_currency( $contract->balance_amount_received ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Method:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( ucfirst( $contract->balance_payment_method ) ); ?></p>
                                <p><strong><?php esc_html_e( 'Date:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( date( 'M j, Y', strtotime( $contract->balance_paid_at ) ) ); ?></p>
                                <?php if ( ! empty( $contract->balance_payment_notes ) ) : ?>
                                    <p><strong><?php esc_html_e( 'Notes:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( $contract->balance_payment_notes ); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else : ?>
                            <form class="smcb-payment-form" data-payment-type="balance" data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Payment Method', 'skinny-moo-contract-builder' ); ?></label>
                                    <select name="payment_method" required>
                                        <option value=""><?php esc_html_e( 'Select...', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="check"><?php esc_html_e( 'Check', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="cash"><?php esc_html_e( 'Cash', 'skinny-moo-contract-builder' ); ?></option>
                                        <option value="card"><?php esc_html_e( 'Credit Card', 'skinny-moo-contract-builder' ); ?></option>
                                    </select>
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Amount Received', 'skinny-moo-contract-builder' ); ?></label>
                                    <input type="number" name="amount" step="0.01" value="<?php echo esc_attr( $contract->calculated->balance_due ); ?>" required>
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label><?php esc_html_e( 'Notes (optional)', 'skinny-moo-contract-builder' ); ?></label>
                                    <input type="text" name="notes" placeholder="<?php esc_attr_e( 'Check #, transaction ID, etc.', 'skinny-moo-contract-builder' ); ?>">
                                </div>
                                <div class="smcb-payment-form-row">
                                    <label>
                                        <input type="checkbox" name="send_receipt" value="1" checked>
                                        <?php esc_html_e( 'Send receipt to client', 'skinny-moo-contract-builder' ); ?>
                                    </label>
                                </div>
                                <button type="submit" class="button button-primary"><?php esc_html_e( 'Record Payment', 'skinny-moo-contract-builder' ); ?></button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment Summary -->
                <div class="smcb-payment-summary">
                    <?php
                    $total_received = floatval( $contract->deposit_amount_received ?? 0 ) + floatval( $contract->balance_amount_received ?? 0 );
                    $total_due = $contract->calculated->total_compensation;
                    $remaining = $total_due - $total_received;
                    ?>
                    <div class="smcb-payment-summary-item">
                        <span><?php esc_html_e( 'Total Due:', 'skinny-moo-contract-builder' ); ?></span>
                        <strong><?php echo esc_html( smcb_format_currency( $total_due ) ); ?></strong>
                    </div>
                    <div class="smcb-payment-summary-item">
                        <span><?php esc_html_e( 'Total Received:', 'skinny-moo-contract-builder' ); ?></span>
                        <strong class="smcb-text-success"><?php echo esc_html( smcb_format_currency( $total_received ) ); ?></strong>
                    </div>
                    <?php if ( $remaining > 0 ) : ?>
                    <div class="smcb-payment-summary-item">
                        <span><?php esc_html_e( 'Remaining:', 'skinny-moo-contract-builder' ); ?></span>
                        <strong class="smcb-text-warning"><?php echo esc_html( smcb_format_currency( $remaining ) ); ?></strong>
                    </div>
                    <?php elseif ( $contract->deposit_paid && $contract->balance_paid ) : ?>
                    <div class="smcb-payment-summary-item smcb-paid-full">
                        <span class="dashicons dashicons-yes-alt"></span>
                        <strong><?php esc_html_e( 'PAID IN FULL', 'skinny-moo-contract-builder' ); ?></strong>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Signature (if signed) -->
            <?php if ( $contract->status === 'signed' && ! empty( $contract->client_signature ) ) : ?>
                <div class="smcb-view-section">
                    <h2><span class="dashicons dashicons-edit"></span> <?php esc_html_e( 'Client Signature', 'skinny-moo-contract-builder' ); ?></h2>
                    <div class="smcb-signature-display">
                        <img src="<?php echo esc_attr( $contract->client_signature ); ?>" alt="<?php esc_attr_e( 'Client Signature', 'skinny-moo-contract-builder' ); ?>">
                        <p class="smcb-signature-info">
                            <?php
                            printf(
                                esc_html__( 'Signed by %s on %s from IP %s', 'skinny-moo-contract-builder' ),
                                '<strong>' . esc_html( $contract->client_signed_name ) . '</strong>',
                                esc_html( date( 'F j, Y \a\t g:i A', strtotime( $contract->client_signed_at ) ) ),
                                esc_html( $contract->client_signed_ip )
                            );
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Activity Log -->
            <div class="smcb-view-section">
                <h2><span class="dashicons dashicons-backup"></span> <?php esc_html_e( 'Activity Log', 'skinny-moo-contract-builder' ); ?></h2>
                <table class="smcb-activity-log">
                    <thead>
                        <tr>
                            <th><?php esc_html_e( 'Date/Time', 'skinny-moo-contract-builder' ); ?></th>
                            <th><?php esc_html_e( 'Action', 'skinny-moo-contract-builder' ); ?></th>
                            <th><?php esc_html_e( 'Details', 'skinny-moo-contract-builder' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( empty( $activity_log ) ) : ?>
                            <tr>
                                <td colspan="3"><?php esc_html_e( 'No activity recorded.', 'skinny-moo-contract-builder' ); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ( $activity_log as $activity ) : ?>
                                <tr>
                                    <td><?php echo esc_html( date( 'M j, Y g:i A', strtotime( $activity->created_at ) ) ); ?></td>
                                    <td><span class="smcb-activity-action smcb-activity-<?php echo esc_attr( $activity->action ); ?>"><?php echo esc_html( ucwords( str_replace( '_', ' ', $activity->action ) ) ); ?></span></td>
                                    <td><?php echo esc_html( $activity->description ); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="smcb-view-sidebar">
            <!-- Actions Box -->
            <div class="smcb-sidebar-box">
                <h3><?php esc_html_e( 'Actions', 'skinny-moo-contract-builder' ); ?></h3>
                <div class="smcb-sidebar-box-content">
                    <div class="smcb-action-buttons">
                        <?php if ( in_array( $contract->status, array( 'draft', 'sent', 'viewed' ), true ) ) : ?>
                            <button type="button" class="button button-primary smcb-action-send" data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                                <span class="dashicons dashicons-email-alt"></span>
                                <?php echo $contract->status === 'draft' ? esc_html__( 'Send Contract', 'skinny-moo-contract-builder' ) : esc_html__( 'Resend Contract', 'skinny-moo-contract-builder' ); ?>
                            </button>
                        <?php endif; ?>

                        <button type="button" class="button smcb-generate-pdfs" data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                            <span class="dashicons dashicons-pdf"></span>
                            <?php esc_html_e( 'Generate PDFs', 'skinny-moo-contract-builder' ); ?>
                        </button>

                        <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-add-contract', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>" class="button">
                            <span class="dashicons dashicons-edit"></span>
                            <?php esc_html_e( 'Edit Contract', 'skinny-moo-contract-builder' ); ?>
                        </a>

                        <button type="button" class="button smcb-regenerate-token" data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                            <span class="dashicons dashicons-update"></span>
                            <?php esc_html_e( 'Regenerate Link', 'skinny-moo-contract-builder' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contract Link Box -->
            <div class="smcb-sidebar-box">
                <h3><?php esc_html_e( 'Contract Link', 'skinny-moo-contract-builder' ); ?></h3>
                <div class="smcb-sidebar-box-content">
                    <p class="description"><?php esc_html_e( 'Share this link with the client:', 'skinny-moo-contract-builder' ); ?></p>
                    <div class="smcb-contract-link-wrapper">
                        <input type="text" readonly class="smcb-contract-link" id="contract-url" value="<?php echo esc_attr( $contract_url ); ?>">
                        <button type="button" class="button smcb-copy-link" data-clipboard-target="#contract-url">
                            <span class="dashicons dashicons-clipboard"></span>
                        </button>
                    </div>
                    <p class="smcb-token-expires">
                        <?php
                        $is_expired = strtotime( $contract->token_expires_at ) < time();
                        if ( $is_expired ) {
                            echo '<span class="smcb-expired">' . esc_html__( 'Link expired', 'skinny-moo-contract-builder' ) . '</span>';
                        } else {
                            printf(
                                esc_html__( 'Expires: %s', 'skinny-moo-contract-builder' ),
                                esc_html( date( 'M j, Y', strtotime( $contract->token_expires_at ) ) )
                            );
                        }
                        ?>
                    </p>
                </div>
            </div>

            <!-- PDFs Box -->
            <div class="smcb-sidebar-box">
                <h3><?php esc_html_e( 'Documents', 'skinny-moo-contract-builder' ); ?></h3>
                <div class="smcb-sidebar-box-content smcb-pdf-links">
                    <?php if ( ! empty( $contract->cover_letter_pdf_path ) && file_exists( $contract->cover_letter_pdf_path ) ) : ?>
                        <a href="<?php echo esc_url( SMCB_PDF_Generator::get_pdf_url( $contract->cover_letter_pdf_path ) ); ?>" target="_blank" class="smcb-pdf-link">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php esc_html_e( 'Cover Letter', 'skinny-moo-contract-builder' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $contract->contract_pdf_path ) && file_exists( $contract->contract_pdf_path ) ) : ?>
                        <a href="<?php echo esc_url( SMCB_PDF_Generator::get_pdf_url( $contract->contract_pdf_path ) ); ?>" target="_blank" class="smcb-pdf-link">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php esc_html_e( 'Contract', 'skinny-moo-contract-builder' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $contract->invoice_pdf_path ) && file_exists( $contract->invoice_pdf_path ) ) : ?>
                        <a href="<?php echo esc_url( SMCB_PDF_Generator::get_pdf_url( $contract->invoice_pdf_path ) ); ?>" target="_blank" class="smcb-pdf-link">
                            <span class="dashicons dashicons-media-document"></span>
                            <?php esc_html_e( 'Invoice', 'skinny-moo-contract-builder' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( ! empty( $contract->signed_contract_pdf_path ) && file_exists( $contract->signed_contract_pdf_path ) ) : ?>
                        <a href="<?php echo esc_url( SMCB_PDF_Generator::get_pdf_url( $contract->signed_contract_pdf_path ) ); ?>" target="_blank" class="smcb-pdf-link smcb-pdf-signed">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php esc_html_e( 'Signed Contract', 'skinny-moo-contract-builder' ); ?>
                        </a>
                    <?php endif; ?>

                    <?php if ( empty( $contract->contract_pdf_path ) ) : ?>
                        <p class="smcb-no-pdfs"><?php esc_html_e( 'PDFs not yet generated.', 'skinny-moo-contract-builder' ); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Contract Info Box -->
            <div class="smcb-sidebar-box">
                <h3><?php esc_html_e( 'Contract Info', 'skinny-moo-contract-builder' ); ?></h3>
                <div class="smcb-sidebar-box-content">
                    <p><strong><?php esc_html_e( 'Contract #:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( $contract->contract_number ); ?></p>
                    <p><strong><?php esc_html_e( 'Invoice #:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( $contract->invoice_number ); ?></p>
                    <p><strong><?php esc_html_e( 'Created:', 'skinny-moo-contract-builder' ); ?></strong> <?php echo esc_html( date( 'M j, Y', strtotime( $contract->created_at ) ) ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
