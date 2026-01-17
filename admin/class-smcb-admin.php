<?php
/**
 * Admin functionality class.
 *
 * Handles all WordPress admin functionality for the plugin.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Admin
 *
 * Manages admin interface and functionality.
 */
class SMCB_Admin {

    /**
     * Contract model instance.
     *
     * @var SMCB_Contract
     */
    private $contract_model;

    /**
     * Initialize the admin class.
     */
    public function init() {
        $this->contract_model = new SMCB_Contract();

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Enqueue admin scripts and styles
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Handle form submissions
        add_action( 'admin_init', array( $this, 'handle_form_submissions' ) );

        // Add admin notices
        add_action( 'admin_notices', array( $this, 'display_admin_notices' ) );

        // AJAX handlers
        add_action( 'wp_ajax_smcb_send_contract', array( $this, 'ajax_send_contract' ) );
        add_action( 'wp_ajax_smcb_delete_contract', array( $this, 'ajax_delete_contract' ) );
        add_action( 'wp_ajax_smcb_regenerate_token', array( $this, 'ajax_regenerate_token' ) );
        add_action( 'wp_ajax_smcb_generate_pdfs', array( $this, 'ajax_generate_pdfs' ) );
    }

    /**
     * Add admin menu items.
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'Contracts', 'skinny-moo-contract-builder' ),
            __( 'Contracts', 'skinny-moo-contract-builder' ),
            'manage_options',
            'smcb-contracts',
            array( $this, 'render_contracts_page' ),
            'dashicons-media-document',
            30
        );

        // Submenu - All Contracts
        add_submenu_page(
            'smcb-contracts',
            __( 'All Contracts', 'skinny-moo-contract-builder' ),
            __( 'All Contracts', 'skinny-moo-contract-builder' ),
            'manage_options',
            'smcb-contracts',
            array( $this, 'render_contracts_page' )
        );

        // Submenu - Add New
        add_submenu_page(
            'smcb-contracts',
            __( 'Add New Contract', 'skinny-moo-contract-builder' ),
            __( 'Add New', 'skinny-moo-contract-builder' ),
            'manage_options',
            'smcb-add-contract',
            array( $this, 'render_add_contract_page' )
        );

        // Submenu - Settings (hidden for now)
        // add_submenu_page(
        //     'smcb-contracts',
        //     __( 'Settings', 'skinny-moo-contract-builder' ),
        //     __( 'Settings', 'skinny-moo-contract-builder' ),
        //     'manage_options',
        //     'smcb-settings',
        //     array( $this, 'render_settings_page' )
        // );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * @param string $hook Current admin page hook.
     */
    public function enqueue_scripts( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'smcb' ) === false ) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'smcb-admin',
            SMCB_PLUGIN_URL . 'admin/css/smcb-admin.css',
            array(),
            SMCB_VERSION
        );

        // Scripts
        wp_enqueue_script(
            'smcb-admin',
            SMCB_PLUGIN_URL . 'admin/js/smcb-admin.js',
            array( 'jquery' ),
            SMCB_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'smcb-admin', 'smcb_admin', array(
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'smcb_admin_nonce' ),
            'confirm_delete' => __( 'Are you sure you want to delete this contract? This action cannot be undone.', 'skinny-moo-contract-builder' ),
            'confirm_send'   => __( 'Send this contract to the client?', 'skinny-moo-contract-builder' ),
            'sending'        => __( 'Sending...', 'skinny-moo-contract-builder' ),
            'send'           => __( 'Send', 'skinny-moo-contract-builder' ),
        ) );
    }

    /**
     * Handle form submissions.
     */
    public function handle_form_submissions() {
        // Save contract
        if ( isset( $_POST['smcb_save_contract'] ) && isset( $_POST['smcb_contract_nonce'] ) ) {
            if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['smcb_contract_nonce'] ) ), 'smcb_save_contract' ) ) {
                wp_die( esc_html__( 'Security check failed.', 'skinny-moo-contract-builder' ) );
            }

            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( esc_html__( 'You do not have permission to perform this action.', 'skinny-moo-contract-builder' ) );
            }

            $this->save_contract();
        }
    }

    /**
     * Save contract from form submission.
     */
    private function save_contract() {
        $contract_id = isset( $_POST['contract_id'] ) ? intval( $_POST['contract_id'] ) : 0;

        $data = $this->get_contract_data_from_post();

        // Determine action
        $action = isset( $_POST['smcb_action'] ) ? sanitize_text_field( wp_unslash( $_POST['smcb_action'] ) ) : 'save';

        if ( $contract_id > 0 ) {
            // Update existing contract
            $result = $this->contract_model->update( $contract_id, $data );
            $message = 'updated';
        } else {
            // Create new contract
            $contract_id = $this->contract_model->create( $data );
            $result = $contract_id !== false;
            $message = 'created';
        }

        if ( $result && $action === 'send' ) {
            // Generate PDFs and send email
            $contract = $this->contract_model->get( $contract_id );
            $pdf_generator = new SMCB_PDF_Generator( $contract );
            $pdf_generator->generate_all();

            $email = new SMCB_Email( $contract );
            if ( $email->send_contract() ) {
                $message = 'sent';
            } else {
                $message = 'send_failed';
            }
        }

        // Redirect with message
        $redirect_url = add_query_arg(
            array(
                'page'     => 'smcb-add-contract',
                'id'       => $contract_id,
                'message'  => $message,
            ),
            admin_url( 'admin.php' )
        );

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Get contract data from POST.
     *
     * @return array Contract data.
     */
    private function get_contract_data_from_post() {
        $data = array();

        // Client Information
        $data['client_company_name'] = isset( $_POST['client_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['client_company_name'] ) ) : '';
        $data['contact_person_name'] = isset( $_POST['contact_person_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_person_name'] ) ) : '';
        $data['street_address'] = isset( $_POST['street_address'] ) ? sanitize_text_field( wp_unslash( $_POST['street_address'] ) ) : '';
        $data['city'] = isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '';
        $data['state'] = isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '';
        $data['zip_code'] = isset( $_POST['zip_code'] ) ? sanitize_text_field( wp_unslash( $_POST['zip_code'] ) ) : '';
        $data['phone'] = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
        $data['email'] = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

        // Performance Details
        $data['performance_date'] = isset( $_POST['performance_date'] ) ? sanitize_text_field( wp_unslash( $_POST['performance_date'] ) ) : '';
        $data['event_name'] = isset( $_POST['event_name'] ) ? sanitize_text_field( wp_unslash( $_POST['event_name'] ) ) : '';
        $data['first_set_start_time'] = isset( $_POST['first_set_start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['first_set_start_time'] ) ) : '';
        $data['number_of_sets'] = isset( $_POST['number_of_sets'] ) ? intval( $_POST['number_of_sets'] ) : 3;
        $data['set_length'] = isset( $_POST['set_length'] ) ? intval( $_POST['set_length'] ) : 60;
        $data['break_length'] = isset( $_POST['break_length'] ) ? intval( $_POST['break_length'] ) : 30;
        $data['load_in_time'] = isset( $_POST['load_in_time'] ) ? sanitize_text_field( wp_unslash( $_POST['load_in_time'] ) ) : '';

        // Venue Details
        $data['venue_name'] = isset( $_POST['venue_name'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_name'] ) ) : '';
        $data['venue_address'] = isset( $_POST['venue_address'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_address'] ) ) : '';
        $data['venue_city'] = isset( $_POST['venue_city'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_city'] ) ) : '';
        $data['venue_state'] = isset( $_POST['venue_state'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_state'] ) ) : '';
        $data['venue_zip'] = isset( $_POST['venue_zip'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_zip'] ) ) : '';
        $data['venue_contact_person'] = isset( $_POST['venue_contact_person'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_contact_person'] ) ) : '';
        $data['venue_phone'] = isset( $_POST['venue_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['venue_phone'] ) ) : '';
        $data['venue_email'] = isset( $_POST['venue_email'] ) ? sanitize_email( wp_unslash( $_POST['venue_email'] ) ) : '';
        $data['inside_outside'] = isset( $_POST['inside_outside'] ) ? sanitize_text_field( wp_unslash( $_POST['inside_outside'] ) ) : 'inside';
        $data['stage_available'] = isset( $_POST['stage_available'] ) ? sanitize_text_field( wp_unslash( $_POST['stage_available'] ) ) : 'tbd';
        $data['power_requirements'] = isset( $_POST['power_requirements'] ) ? sanitize_text_field( wp_unslash( $_POST['power_requirements'] ) ) : '';
        $data['loadin_location'] = isset( $_POST['loadin_location'] ) ? sanitize_textarea_field( wp_unslash( $_POST['loadin_location'] ) ) : '';
        $data['performance_location'] = isset( $_POST['performance_location'] ) ? sanitize_textarea_field( wp_unslash( $_POST['performance_location'] ) ) : '';

        // Early Load-in
        $data['early_loadin_required'] = isset( $_POST['early_loadin_required'] ) ? 1 : 0;
        $data['early_loadin_hours'] = isset( $_POST['early_loadin_hours'] ) ? intval( $_POST['early_loadin_hours'] ) : 0;

        // Production & Equipment
        $data['sound_system'] = isset( $_POST['sound_system'] ) ? sanitize_text_field( wp_unslash( $_POST['sound_system'] ) ) : 'we_provide';
        $data['lights'] = isset( $_POST['lights'] ) ? sanitize_text_field( wp_unslash( $_POST['lights'] ) ) : 'we_provide';
        $data['music_between_sets'] = isset( $_POST['music_between_sets'] ) ? sanitize_text_field( wp_unslash( $_POST['music_between_sets'] ) ) : 'we_provide';
        $data['outside_production'] = isset( $_POST['outside_production'] ) ? 1 : 0;
        $data['outside_production_notes'] = isset( $_POST['outside_production_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['outside_production_notes'] ) ) : '';

        // Music Preferences
        $data['preferred_genre'] = isset( $_POST['preferred_genre'] ) ? sanitize_text_field( wp_unslash( $_POST['preferred_genre'] ) ) : '';

        // Travel & Accommodations
        $data['accommodations_provided'] = isset( $_POST['accommodations_provided'] ) ? sanitize_text_field( wp_unslash( $_POST['accommodations_provided'] ) ) : '';
        $data['accommodation_cost_offset'] = isset( $_POST['accommodation_cost_offset'] ) ? floatval( $_POST['accommodation_cost_offset'] ) : 0;
        $data['mileage_travel_fee'] = isset( $_POST['mileage_travel_fee'] ) ? floatval( $_POST['mileage_travel_fee'] ) : 0;

        // Compensation
        $data['base_compensation'] = isset( $_POST['base_compensation'] ) ? floatval( $_POST['base_compensation'] ) : 0;
        $data['deposit_percentage'] = isset( $_POST['deposit_percentage'] ) ? intval( $_POST['deposit_percentage'] ) : 30;
        $data['additional_compensation'] = isset( $_POST['additional_compensation'] ) ? sanitize_textarea_field( wp_unslash( $_POST['additional_compensation'] ) ) : '';

        // Services Provided
        $data['services_description'] = isset( $_POST['services_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['services_description'] ) ) : '';
        $data['attire'] = isset( $_POST['attire'] ) ? sanitize_text_field( wp_unslash( $_POST['attire'] ) ) : '';
        $data['audience_rating'] = isset( $_POST['audience_rating'] ) ? sanitize_text_field( wp_unslash( $_POST['audience_rating'] ) ) : 'pg-13';

        // Additional Content
        $data['cover_letter_message'] = isset( $_POST['cover_letter_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['cover_letter_message'] ) ) : '';
        $data['additional_contract_notes'] = isset( $_POST['additional_contract_notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['additional_contract_notes'] ) ) : '';

        // Status
        $data['status'] = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : 'draft';

        // Line items
        $data['line_items'] = array();
        if ( isset( $_POST['line_items'] ) && is_array( $_POST['line_items'] ) ) {
            foreach ( $_POST['line_items'] as $item ) {
                if ( ! empty( $item['description'] ) ) {
                    $data['line_items'][] = array(
                        'description' => sanitize_text_field( wp_unslash( $item['description'] ) ),
                        'quantity'    => floatval( $item['quantity'] ?? 1 ),
                        'unit_price'  => floatval( $item['unit_price'] ?? 0 ),
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Display admin notices.
     */
    public function display_admin_notices() {
        if ( ! isset( $_GET['page'] ) || strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'smcb' ) === false ) {
            return;
        }

        if ( ! isset( $_GET['message'] ) ) {
            return;
        }

        $message = sanitize_text_field( wp_unslash( $_GET['message'] ) );
        $type = 'success';

        switch ( $message ) {
            case 'created':
                $text = __( 'Contract created successfully.', 'skinny-moo-contract-builder' );
                break;
            case 'updated':
                $text = __( 'Contract updated successfully.', 'skinny-moo-contract-builder' );
                break;
            case 'sent':
                $text = __( 'Contract sent successfully.', 'skinny-moo-contract-builder' );
                break;
            case 'send_failed':
                $text = __( 'Contract saved but email could not be sent. Please try resending.', 'skinny-moo-contract-builder' );
                $type = 'error';
                break;
            case 'deleted':
                $text = __( 'Contract deleted successfully.', 'skinny-moo-contract-builder' );
                break;
            default:
                return;
        }

        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr( $type ),
            esc_html( $text )
        );
    }

    /**
     * Render contracts list page.
     */
    public function render_contracts_page() {
        // Check for view action
        if ( isset( $_GET['action'] ) && sanitize_text_field( wp_unslash( $_GET['action'] ) ) === 'view' && isset( $_GET['id'] ) ) {
            $this->render_view_contract_page();
            return;
        }

        // Get filters
        $args = array(
            'per_page' => 20,
            'page'     => isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1,
            'status'   => isset( $_GET['status'] ) ? sanitize_text_field( wp_unslash( $_GET['status'] ) ) : '',
            'search'   => isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '',
            'orderby'  => isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : 'created_at',
            'order'    => isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'DESC',
        );

        $result = $this->contract_model->get_list( $args );
        $contracts = $result['items'];
        $total = $result['total'];
        $total_pages = ceil( $total / $args['per_page'] );

        // Get statistics
        $stats = $this->contract_model->get_statistics();

        include SMCB_PLUGIN_DIR . 'admin/partials/contracts-list.php';
    }

    /**
     * Render add/edit contract page.
     */
    public function render_add_contract_page() {
        $contract = null;
        $contract_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;

        if ( $contract_id > 0 ) {
            $contract = $this->contract_model->get( $contract_id );
            if ( ! $contract ) {
                wp_die( esc_html__( 'Contract not found.', 'skinny-moo-contract-builder' ) );
            }
        }

        // Get dropdown options
        $status_options = smcb_get_contract_statuses();
        $sets_options = smcb_get_number_of_sets_options();
        $inside_outside_options = smcb_get_inside_outside_options();
        $stage_options = smcb_get_stage_options();
        $production_options = smcb_get_production_options();
        $genre_options = smcb_get_genre_options();
        $accommodations_options = smcb_get_accommodations_options();
        $rating_options = smcb_get_audience_rating_options();

        include SMCB_PLUGIN_DIR . 'admin/partials/contract-form.php';
    }

    /**
     * Render view contract page.
     */
    public function render_view_contract_page() {
        $contract_id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
        $contract = $this->contract_model->get( $contract_id );

        if ( ! $contract ) {
            wp_die( esc_html__( 'Contract not found.', 'skinny-moo-contract-builder' ) );
        }

        $activity_log = $this->contract_model->get_activity_log( $contract_id );
        $token_manager = new SMCB_Token_Manager();
        $contract_url = $token_manager->get_contract_url( $contract->access_token );

        include SMCB_PLUGIN_DIR . 'admin/partials/contract-view.php';
    }

    /**
     * AJAX: Send contract email.
     */
    public function ajax_send_contract() {
        check_ajax_referer( 'smcb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skinny-moo-contract-builder' ) ) );
        }

        $contract_id = isset( $_POST['contract_id'] ) ? intval( $_POST['contract_id'] ) : 0;
        $contract = $this->contract_model->get( $contract_id );

        if ( ! $contract ) {
            wp_send_json_error( array( 'message' => __( 'Contract not found.', 'skinny-moo-contract-builder' ) ) );
        }

        // Generate PDFs if not exists
        if ( empty( $contract->contract_pdf_path ) || ! file_exists( $contract->contract_pdf_path ) ) {
            $pdf_generator = new SMCB_PDF_Generator( $contract );
            $pdf_generator->generate_all();
            // Refresh contract data
            $contract = $this->contract_model->get( $contract_id );
        }

        // Send email
        $email = new SMCB_Email( $contract );
        if ( $email->send_contract() ) {
            wp_send_json_success( array(
                'message' => __( 'Contract sent successfully.', 'skinny-moo-contract-builder' ),
                'status'  => 'sent',
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to send email.', 'skinny-moo-contract-builder' ) ) );
        }
    }

    /**
     * AJAX: Delete contract.
     */
    public function ajax_delete_contract() {
        check_ajax_referer( 'smcb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skinny-moo-contract-builder' ) ) );
        }

        $contract_id = isset( $_POST['contract_id'] ) ? intval( $_POST['contract_id'] ) : 0;

        if ( $this->contract_model->delete( $contract_id ) ) {
            wp_send_json_success( array( 'message' => __( 'Contract deleted.', 'skinny-moo-contract-builder' ) ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to delete contract.', 'skinny-moo-contract-builder' ) ) );
        }
    }

    /**
     * AJAX: Regenerate access token.
     */
    public function ajax_regenerate_token() {
        check_ajax_referer( 'smcb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skinny-moo-contract-builder' ) ) );
        }

        $contract_id = isset( $_POST['contract_id'] ) ? intval( $_POST['contract_id'] ) : 0;
        $new_token = $this->contract_model->regenerate_token( $contract_id );

        if ( $new_token ) {
            $token_manager = new SMCB_Token_Manager();
            wp_send_json_success( array(
                'message' => __( 'Token regenerated.', 'skinny-moo-contract-builder' ),
                'token'   => $new_token,
                'url'     => $token_manager->get_contract_url( $new_token ),
            ) );
        } else {
            wp_send_json_error( array( 'message' => __( 'Failed to regenerate token.', 'skinny-moo-contract-builder' ) ) );
        }
    }

    /**
     * AJAX: Generate PDFs.
     */
    public function ajax_generate_pdfs() {
        check_ajax_referer( 'smcb_admin_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'Permission denied.', 'skinny-moo-contract-builder' ) ) );
        }

        $contract_id = isset( $_POST['contract_id'] ) ? intval( $_POST['contract_id'] ) : 0;
        $contract = $this->contract_model->get( $contract_id );

        if ( ! $contract ) {
            wp_send_json_error( array( 'message' => __( 'Contract not found.', 'skinny-moo-contract-builder' ) ) );
        }

        try {
            $pdf_generator = new SMCB_PDF_Generator( $contract );
            $paths = $pdf_generator->generate_all();

            $urls = array();
            foreach ( $paths as $type => $path ) {
                if ( $path ) {
                    $urls[ $type ] = SMCB_PDF_Generator::get_pdf_url( $path );
                }
            }

            wp_send_json_success( array(
                'message' => __( 'PDFs generated successfully.', 'skinny-moo-contract-builder' ),
                'urls'    => $urls,
            ) );
        } catch ( Exception $e ) {
            wp_send_json_error( array( 'message' => $e->getMessage() ) );
        }
    }
}
