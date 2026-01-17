<?php
/**
 * Admin contract form template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$is_edit = ! empty( $contract );
$page_title = $is_edit
    ? sprintf( __( 'Edit Contract: %s', 'skinny-moo-contract-builder' ), $contract->contract_number )
    : __( 'Add New Contract', 'skinny-moo-contract-builder' );
?>

<div class="wrap smcb-admin smcb-contract-form-page">
    <h1 class="wp-heading-inline"><?php echo esc_html( $page_title ); ?></h1>

    <?php if ( $is_edit ) : ?>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=smcb-add-contract' ) ); ?>" class="page-title-action">
            <?php esc_html_e( 'Add New', 'skinny-moo-contract-builder' ); ?>
        </a>
    <?php endif; ?>

    <hr class="wp-header-end">

    <form method="post" action="" class="smcb-contract-form" id="smcb-contract-form">
        <?php wp_nonce_field( 'smcb_save_contract', 'smcb_contract_nonce' ); ?>
        <input type="hidden" name="contract_id" value="<?php echo esc_attr( $is_edit ? $contract->id : 0 ); ?>">
        <input type="hidden" name="smcb_action" id="smcb_action" value="save">

        <div class="smcb-form-container">
            <!-- Main Column -->
            <div class="smcb-form-main">

                <!-- Client Information -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-businessman"></span>
                        <?php esc_html_e( 'Client Information', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-6">
                            <label for="client_company_name"><?php esc_html_e( 'Client/Company Name', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="client_company_name" name="client_company_name" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->client_company_name : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="contact_person_name"><?php esc_html_e( 'Contact Person Name', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="contact_person_name" name="contact_person_name" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->contact_person_name : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-12">
                            <label for="street_address"><?php esc_html_e( 'Street Address', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="street_address" name="street_address" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->street_address : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="city"><?php esc_html_e( 'City', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="city" name="city" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->city : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="state"><?php esc_html_e( 'State', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="state" name="state" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->state : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="zip_code"><?php esc_html_e( 'ZIP Code', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="zip_code" name="zip_code" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->zip_code : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="phone"><?php esc_html_e( 'Phone', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->phone : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="email"><?php esc_html_e( 'Email', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->email : '' ); ?>">
                        </div>
                    </div>
                </div>

                <!-- Performance Details -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e( 'Performance Details', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-6">
                            <label for="event_name"><?php esc_html_e( 'Event Name', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="text" id="event_name" name="event_name" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->event_name : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="performance_date"><?php esc_html_e( 'Performance Date', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="date" id="performance_date" name="performance_date" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->performance_date : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="load_in_time"><?php esc_html_e( 'Load-in Time', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="time" id="load_in_time" name="load_in_time" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->load_in_time : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="first_set_start_time"><?php esc_html_e( 'First Set Start Time', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="time" id="first_set_start_time" name="first_set_start_time" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->first_set_start_time : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="number_of_sets"><?php esc_html_e( 'Number of Sets', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="number_of_sets" name="number_of_sets" required>
                                <?php foreach ( $sets_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->number_of_sets : '3', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="set_length"><?php esc_html_e( 'Set Length (minutes)', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="number" id="set_length" name="set_length" min="15" max="180" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->set_length : '60' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="break_length"><?php esc_html_e( 'Break Length (minutes)', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="number" id="break_length" name="break_length" min="0" max="60" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->break_length : '30' ); ?>">
                        </div>
                    </div>

                    <!-- Calculated Set Times -->
                    <div class="smcb-set-times-preview" id="set-times-preview" style="display: none;">
                        <h4><?php esc_html_e( 'Set Schedule Preview', 'skinny-moo-contract-builder' ); ?></h4>
                        <div id="set-times-list"></div>
                    </div>
                </div>

                <!-- Venue Details -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-building"></span>
                        <?php esc_html_e( 'Venue Details', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-6">
                            <label for="venue_name"><?php esc_html_e( 'Venue Name', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_name" name="venue_name"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_name : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="venue_contact_person"><?php esc_html_e( 'Venue Contact Person', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_contact_person" name="venue_contact_person"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_contact_person : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-12">
                            <label for="venue_address"><?php esc_html_e( 'Venue Address', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_address" name="venue_address"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_address : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="venue_city"><?php esc_html_e( 'City', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_city" name="venue_city"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_city : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="venue_state"><?php esc_html_e( 'State', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_state" name="venue_state"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_state : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="venue_zip"><?php esc_html_e( 'ZIP Code', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="venue_zip" name="venue_zip"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_zip : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="venue_phone"><?php esc_html_e( 'Venue Phone', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="tel" id="venue_phone" name="venue_phone"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_phone : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="venue_email"><?php esc_html_e( 'Venue Email', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="email" id="venue_email" name="venue_email"
                                   value="<?php echo esc_attr( $is_edit ? $contract->venue_email : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="inside_outside"><?php esc_html_e( 'Inside/Outside', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="inside_outside" name="inside_outside" required>
                                <?php foreach ( $inside_outside_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->inside_outside : 'inside', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="stage_available"><?php esc_html_e( 'Stage Available', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="stage_available" name="stage_available" required>
                                <?php foreach ( $stage_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->stage_available : 'tbd', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="power_requirements"><?php esc_html_e( 'Power Requirements', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="power_requirements" name="power_requirements" placeholder="e.g., 20A single circuit"
                                   value="<?php echo esc_attr( $is_edit ? $contract->power_requirements : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="loadin_location"><?php esc_html_e( 'Load-in Location', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="loadin_location" name="loadin_location" rows="2" placeholder="<?php esc_attr_e( 'Where will we load in?', 'skinny-moo-contract-builder' ); ?>"><?php echo esc_textarea( $is_edit ? $contract->loadin_location : '' ); ?></textarea>
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="performance_location"><?php esc_html_e( 'Performance Location', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="performance_location" name="performance_location" rows="2" placeholder="<?php esc_attr_e( 'Where are we playing within the venue?', 'skinny-moo-contract-builder' ); ?>"><?php echo esc_textarea( $is_edit ? $contract->performance_location : '' ); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Early Load-in -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-clock"></span>
                        <?php esc_html_e( 'Early Load-in', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-6">
                            <label class="smcb-checkbox-label">
                                <input type="checkbox" id="early_loadin_required" name="early_loadin_required" value="1"
                                    <?php checked( $is_edit && $contract->early_loadin_required ); ?>>
                                <?php esc_html_e( 'Early Load-in Required (more than 2 hours before)', 'skinny-moo-contract-builder' ); ?>
                            </label>
                        </div>
                        <div class="smcb-form-row smcb-col-6 smcb-early-loadin-hours" style="<?php echo ( $is_edit && $contract->early_loadin_required ) ? '' : 'display:none;'; ?>">
                            <label for="early_loadin_hours"><?php esc_html_e( 'Hours Early', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="number" id="early_loadin_hours" name="early_loadin_hours" min="1" max="12"
                                   value="<?php echo esc_attr( $is_edit ? $contract->early_loadin_hours : '' ); ?>">
                            <p class="description"><?php printf( esc_html__( 'Charged at $%d/hour', 'skinny-moo-contract-builder' ), SMCB_EARLY_LOADIN_RATE ); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Production & Equipment -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-controls-volumeon"></span>
                        <?php esc_html_e( 'Production & Equipment', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-4">
                            <label for="sound_system"><?php esc_html_e( 'Sound System', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="sound_system" name="sound_system" required>
                                <?php foreach ( $production_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->sound_system : 'we_provide', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="lights"><?php esc_html_e( 'Lights', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="lights" name="lights" required>
                                <?php foreach ( $production_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->lights : 'we_provide', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="music_between_sets"><?php esc_html_e( 'Music Between Sets', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="music_between_sets" name="music_between_sets" required>
                                <?php foreach ( $production_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->music_between_sets : 'we_provide', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-12">
                            <label class="smcb-checkbox-label">
                                <input type="checkbox" id="outside_production" name="outside_production" value="1"
                                    <?php checked( $is_edit && $contract->outside_production ); ?>>
                                <?php esc_html_e( 'Outside Production Company Involved', 'skinny-moo-contract-builder' ); ?>
                            </label>
                        </div>
                        <div class="smcb-form-row smcb-col-12 smcb-outside-production-notes" style="<?php echo ( $is_edit && $contract->outside_production ) ? '' : 'display:none;'; ?>">
                            <label for="outside_production_notes"><?php esc_html_e( 'Outside Production Notes', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="outside_production_notes" name="outside_production_notes" rows="3"><?php echo esc_textarea( $is_edit ? $contract->outside_production_notes : '' ); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Music Preferences -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-playlist-audio"></span>
                        <?php esc_html_e( 'Music Preferences', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-6">
                            <label for="preferred_genre"><?php esc_html_e( 'Preferred Genre', 'skinny-moo-contract-builder' ); ?></label>
                            <select id="preferred_genre" name="preferred_genre">
                                <?php foreach ( $genre_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->preferred_genre : '', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Travel & Accommodations -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-car"></span>
                        <?php esc_html_e( 'Travel & Accommodations', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-4">
                            <label for="accommodations_provided"><?php esc_html_e( 'Accommodations Provided', 'skinny-moo-contract-builder' ); ?></label>
                            <select id="accommodations_provided" name="accommodations_provided">
                                <?php foreach ( $accommodations_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->accommodations_provided : '', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="accommodation_cost_offset"><?php esc_html_e( 'Accommodation Cost Offset ($)', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="number" id="accommodation_cost_offset" name="accommodation_cost_offset" min="0" step="0.01"
                                   value="<?php echo esc_attr( $is_edit ? $contract->accommodation_cost_offset : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="mileage_travel_fee"><?php esc_html_e( 'Mileage/Travel Fee ($)', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="number" id="mileage_travel_fee" name="mileage_travel_fee" min="0" step="0.01"
                                   value="<?php echo esc_attr( $is_edit ? $contract->mileage_travel_fee : '' ); ?>">
                        </div>
                    </div>
                </div>

                <!-- Compensation -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e( 'Compensation', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-4">
                            <label for="base_compensation"><?php esc_html_e( 'Base Compensation ($)', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="number" id="base_compensation" name="base_compensation" min="0" step="0.01" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->base_compensation : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-4">
                            <label for="deposit_percentage"><?php esc_html_e( 'Deposit Percentage (%)', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <input type="number" id="deposit_percentage" name="deposit_percentage" min="0" max="100" required
                                   value="<?php echo esc_attr( $is_edit ? $contract->deposit_percentage : '30' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-12">
                            <label for="additional_compensation"><?php esc_html_e( 'Additional Compensation', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="additional_compensation" name="additional_compensation" rows="2" placeholder="e.g., Food/drink tab, other perks"><?php echo esc_textarea( $is_edit ? $contract->additional_compensation : '' ); ?></textarea>
                        </div>
                    </div>

                    <!-- Calculated Totals Preview -->
                    <div class="smcb-totals-preview" id="totals-preview">
                        <div class="smcb-total-row">
                            <span class="label"><?php esc_html_e( 'Total Compensation:', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="value" id="total-compensation">$0.00</span>
                        </div>
                        <div class="smcb-total-row">
                            <span class="label"><?php esc_html_e( 'Deposit Amount:', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="value" id="deposit-amount">$0.00</span>
                        </div>
                        <div class="smcb-total-row">
                            <span class="label"><?php esc_html_e( 'Balance Due:', 'skinny-moo-contract-builder' ); ?></span>
                            <span class="value" id="balance-due">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Services Provided -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php esc_html_e( 'Services Provided', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-12">
                            <label for="services_description"><?php esc_html_e( 'Services Description', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <textarea id="services_description" name="services_description" rows="4" required><?php echo esc_textarea( $is_edit ? $contract->services_description : 'Live musical entertainment consisting of rock, pop, country, and dance music performed by the Skinny Moo band.' ); ?></textarea>
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="attire"><?php esc_html_e( 'Attire', 'skinny-moo-contract-builder' ); ?></label>
                            <input type="text" id="attire" name="attire" placeholder="e.g., Jeans/Shorts - T-shirts"
                                   value="<?php echo esc_attr( $is_edit ? $contract->attire : '' ); ?>">
                        </div>
                        <div class="smcb-form-row smcb-col-6">
                            <label for="audience_rating"><?php esc_html_e( 'Audience Rating', 'skinny-moo-contract-builder' ); ?> <span class="required">*</span></label>
                            <select id="audience_rating" name="audience_rating" required>
                                <?php foreach ( $rating_options as $value => $label ) : ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->audience_rating : 'pg-13', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Invoice Line Items -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-list-view"></span>
                        <?php esc_html_e( 'Invoice Line Items', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <p class="description"><?php esc_html_e( 'Optional: Add custom line items to the invoice. If left empty, the base compensation will be used.', 'skinny-moo-contract-builder' ); ?></p>

                    <table class="smcb-line-items-table" id="line-items-table">
                        <thead>
                            <tr>
                                <th class="col-description"><?php esc_html_e( 'Description', 'skinny-moo-contract-builder' ); ?></th>
                                <th class="col-quantity"><?php esc_html_e( 'Qty', 'skinny-moo-contract-builder' ); ?></th>
                                <th class="col-price"><?php esc_html_e( 'Unit Price', 'skinny-moo-contract-builder' ); ?></th>
                                <th class="col-total"><?php esc_html_e( 'Total', 'skinny-moo-contract-builder' ); ?></th>
                                <th class="col-actions"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ( $is_edit && ! empty( $contract->line_items ) ) :
                                foreach ( $contract->line_items as $index => $item ) :
                            ?>
                                <tr class="smcb-line-item">
                                    <td><input type="text" name="line_items[<?php echo esc_attr( $index ); ?>][description]" value="<?php echo esc_attr( $item->description ); ?>"></td>
                                    <td><input type="number" name="line_items[<?php echo esc_attr( $index ); ?>][quantity]" value="<?php echo esc_attr( $item->quantity ); ?>" min="1" step="1" class="line-item-quantity"></td>
                                    <td><input type="number" name="line_items[<?php echo esc_attr( $index ); ?>][unit_price]" value="<?php echo esc_attr( $item->unit_price ); ?>" min="0" step="0.01" class="line-item-price"></td>
                                    <td class="line-item-total">$<?php echo esc_html( number_format( $item->quantity * $item->unit_price, 2 ) ); ?></td>
                                    <td><button type="button" class="button smcb-remove-line-item"><span class="dashicons dashicons-trash"></span></button></td>
                                </tr>
                            <?php
                                endforeach;
                            endif;
                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5">
                                    <button type="button" class="button" id="add-line-item">
                                        <span class="dashicons dashicons-plus-alt"></span>
                                        <?php esc_html_e( 'Add Line Item', 'skinny-moo-contract-builder' ); ?>
                                    </button>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Additional Content -->
                <div class="smcb-form-section">
                    <h2 class="smcb-section-title">
                        <span class="dashicons dashicons-edit-page"></span>
                        <?php esc_html_e( 'Additional Content', 'skinny-moo-contract-builder' ); ?>
                    </h2>
                    <div class="smcb-form-grid">
                        <div class="smcb-form-row smcb-col-12">
                            <label for="cover_letter_message"><?php esc_html_e( 'Cover Letter Message', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="cover_letter_message" name="cover_letter_message" rows="4" placeholder="<?php esc_attr_e( 'Leave blank for default message', 'skinny-moo-contract-builder' ); ?>"><?php echo esc_textarea( $is_edit ? $contract->cover_letter_message : '' ); ?></textarea>
                        </div>
                        <div class="smcb-form-row smcb-col-12">
                            <label for="additional_contract_notes"><?php esc_html_e( 'Additional Contract Notes', 'skinny-moo-contract-builder' ); ?></label>
                            <textarea id="additional_contract_notes" name="additional_contract_notes" rows="4" placeholder="<?php esc_attr_e( 'Custom terms or special arrangements', 'skinny-moo-contract-builder' ); ?>"><?php echo esc_textarea( $is_edit ? $contract->additional_contract_notes : '' ); ?></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="smcb-form-sidebar">
                <!-- Publish Box -->
                <div class="smcb-sidebar-box smcb-publish-box">
                    <h3><?php esc_html_e( 'Publish', 'skinny-moo-contract-builder' ); ?></h3>
                    <div class="smcb-sidebar-box-content">
                        <?php if ( $is_edit ) : ?>
                            <p class="smcb-contract-info">
                                <strong><?php esc_html_e( 'Contract #:', 'skinny-moo-contract-builder' ); ?></strong>
                                <?php echo esc_html( $contract->contract_number ); ?>
                            </p>
                            <p class="smcb-contract-info">
                                <strong><?php esc_html_e( 'Invoice #:', 'skinny-moo-contract-builder' ); ?></strong>
                                <?php echo esc_html( $contract->invoice_number ); ?>
                            </p>
                        <?php endif; ?>

                        <div class="smcb-form-row">
                            <label for="status"><?php esc_html_e( 'Status', 'skinny-moo-contract-builder' ); ?></label>
                            <select id="status" name="status">
                                <?php foreach ( $status_options as $value => $label ) : ?>
                                    <?php if ( ! $is_edit && in_array( $value, array( 'sent', 'viewed', 'signed' ), true ) ) continue; ?>
                                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $is_edit ? $contract->status : 'draft', $value ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="smcb-publish-actions">
                            <button type="submit" name="smcb_save_contract" class="button button-large" id="save-draft">
                                <?php esc_html_e( 'Save Draft', 'skinny-moo-contract-builder' ); ?>
                            </button>
                            <button type="submit" name="smcb_save_contract" class="button button-primary button-large" id="save-send">
                                <?php esc_html_e( 'Save & Send', 'skinny-moo-contract-builder' ); ?>
                            </button>
                        </div>

                        <?php if ( $is_edit ) : ?>
                            <div class="smcb-additional-actions">
                                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-contracts', 'action' => 'view', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>" class="button">
                                    <?php esc_html_e( 'View Contract', 'skinny-moo-contract-builder' ); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ( $is_edit ) : ?>
                    <!-- Contract Info Box -->
                    <div class="smcb-sidebar-box">
                        <h3><?php esc_html_e( 'Contract Info', 'skinny-moo-contract-builder' ); ?></h3>
                        <div class="smcb-sidebar-box-content">
                            <p>
                                <strong><?php esc_html_e( 'Created:', 'skinny-moo-contract-builder' ); ?></strong><br>
                                <?php echo esc_html( date( 'M j, Y g:i A', strtotime( $contract->created_at ) ) ); ?>
                            </p>
                            <?php if ( $contract->sent_at ) : ?>
                                <p>
                                    <strong><?php esc_html_e( 'Sent:', 'skinny-moo-contract-builder' ); ?></strong><br>
                                    <?php echo esc_html( date( 'M j, Y g:i A', strtotime( $contract->sent_at ) ) ); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ( $contract->viewed_at ) : ?>
                                <p>
                                    <strong><?php esc_html_e( 'Viewed:', 'skinny-moo-contract-builder' ); ?></strong><br>
                                    <?php echo esc_html( date( 'M j, Y g:i A', strtotime( $contract->viewed_at ) ) ); ?>
                                </p>
                            <?php endif; ?>
                            <?php if ( $contract->client_signed_at ) : ?>
                                <p>
                                    <strong><?php esc_html_e( 'Signed:', 'skinny-moo-contract-builder' ); ?></strong><br>
                                    <?php echo esc_html( date( 'M j, Y g:i A', strtotime( $contract->client_signed_at ) ) ); ?>
                                    <br><small><?php echo esc_html( $contract->client_signed_name ); ?></small>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<script type="text/template" id="line-item-template">
    <tr class="smcb-line-item">
        <td><input type="text" name="line_items[{{index}}][description]" value=""></td>
        <td><input type="number" name="line_items[{{index}}][quantity]" value="1" min="1" step="1" class="line-item-quantity"></td>
        <td><input type="number" name="line_items[{{index}}][unit_price]" value="0" min="0" step="0.01" class="line-item-price"></td>
        <td class="line-item-total">$0.00</td>
        <td><button type="button" class="button smcb-remove-line-item"><span class="dashicons dashicons-trash"></span></button></td>
    </tr>
</script>
