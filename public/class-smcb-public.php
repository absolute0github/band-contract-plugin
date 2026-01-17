<?php
/**
 * Public-facing functionality class.
 *
 * Handles the public contract viewing and signing interface.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Public
 *
 * Manages public contract views and signing.
 */
class SMCB_Public {

    /**
     * Initialize public functionality.
     */
    public function init() {
        // Handle contract view requests
        add_action( 'template_redirect', array( $this, 'handle_contract_view' ) );

        // Enqueue scripts
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Enqueue public scripts and styles.
     */
    public function enqueue_scripts() {
        // Only load on contract view pages
        if ( ! isset( $_GET['smcb_contract'] ) ) {
            return;
        }

        // Styles
        wp_enqueue_style(
            'smcb-public',
            SMCB_PLUGIN_URL . 'public/css/smcb-public.css',
            array(),
            SMCB_VERSION
        );

        // Signature Pad library
        wp_enqueue_script(
            'signature-pad',
            SMCB_PLUGIN_URL . 'public/js/signature_pad.min.js',
            array(),
            '4.1.7',
            true
        );

        // Public JavaScript
        wp_enqueue_script(
            'smcb-public',
            SMCB_PLUGIN_URL . 'public/js/smcb-public.js',
            array( 'jquery', 'signature-pad' ),
            SMCB_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'smcb-public', 'smcb_public', array(
            'ajax_url'          => admin_url( 'admin-ajax.php' ),
            'rest_url'          => rest_url( 'smcb/v1/' ),
            'nonce'             => wp_create_nonce( 'wp_rest' ),
            'signing'           => __( 'Signing...', 'skinny-moo-contract-builder' ),
            'sign_contract'     => __( 'Sign Contract', 'skinny-moo-contract-builder' ),
            'signature_required' => __( 'Please provide your signature.', 'skinny-moo-contract-builder' ),
            'name_required'     => __( 'Please enter your name.', 'skinny-moo-contract-builder' ),
            'agree_required'    => __( 'Please agree to the terms.', 'skinny-moo-contract-builder' ),
        ) );
    }

    /**
     * Handle contract view requests.
     */
    public function handle_contract_view() {
        // Check if this is a contract view request
        if ( ! isset( $_GET['smcb_contract'] ) || $_GET['smcb_contract'] !== 'view' ) {
            return;
        }

        // Get and validate token
        $token = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : '';

        if ( empty( $token ) ) {
            $this->render_error( __( 'Invalid contract link.', 'skinny-moo-contract-builder' ) );
            exit;
        }

        // Validate token format
        $token_manager = new SMCB_Token_Manager();
        if ( ! $token_manager->validate_token_format( $token ) ) {
            $this->render_error( __( 'Invalid contract link.', 'skinny-moo-contract-builder' ) );
            exit;
        }

        // Get contract
        $contract_model = new SMCB_Contract();
        $contract = $contract_model->get_by_token( $token );

        if ( ! $contract ) {
            $this->render_error( __( 'Contract not found.', 'skinny-moo-contract-builder' ) );
            exit;
        }

        // Check if contract can be signed
        $can_sign = $token_manager->can_sign( $contract );

        // Mark as viewed if applicable
        if ( $contract->status === 'sent' ) {
            $contract_model->mark_viewed( $contract->id, $this->get_client_ip() );
            $contract->status = 'viewed';
        }

        // Render the contract view
        $this->render_contract( $contract, $can_sign );
        exit;
    }

    /**
     * Render contract view page.
     *
     * @param object $contract Contract object.
     * @param array  $can_sign Sign validation result.
     */
    private function render_contract( $contract, $can_sign ) {
        // Set page title
        add_filter( 'pre_get_document_title', function() use ( $contract ) {
            return sprintf(
                __( 'Performance Agreement - %s', 'skinny-moo-contract-builder' ),
                $contract->event_name
            );
        } );

        $production_options = smcb_get_production_options();

        // Output the page
        include SMCB_PLUGIN_DIR . 'public/partials/contract-view.php';
    }

    /**
     * Render error page.
     *
     * @param string $message Error message.
     */
    private function render_error( $message ) {
        include SMCB_PLUGIN_DIR . 'public/partials/error.php';
    }

    /**
     * Get client IP address.
     *
     * @return string IP address.
     */
    private function get_client_ip() {
        $ip_keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' );

        foreach ( $ip_keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) );
                if ( strpos( $ip, ',' ) !== false ) {
                    $ip = trim( explode( ',', $ip )[0] );
                }
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }
}
