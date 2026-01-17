<?php
/**
 * REST API class.
 *
 * Handles REST API endpoints for the plugin.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_REST_API
 *
 * REST API endpoint handlers.
 */
class SMCB_REST_API {

    /**
     * API namespace.
     *
     * @var string
     */
    private $namespace = 'smcb/v1';

    /**
     * Initialize REST API.
     */
    public function init() {
        $this->register_routes();
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        // Sign contract endpoint
        register_rest_route(
            $this->namespace,
            '/sign',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'sign_contract' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'signed_name' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                    'signature' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => array( $this, 'sanitize_signature' ),
                    ),
                ),
            )
        );

        // Get contract endpoint (for preview)
        register_rest_route(
            $this->namespace,
            '/contract/(?P<token>[a-fA-F0-9]{64})',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'get_contract' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'token' => array(
                        'required'          => true,
                        'type'              => 'string',
                        'sanitize_callback' => 'sanitize_text_field',
                    ),
                ),
            )
        );
    }

    /**
     * Sign contract endpoint handler.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function sign_contract( $request ) {
        $token = $request->get_param( 'token' );
        $signed_name = $request->get_param( 'signed_name' );
        $signature = $request->get_param( 'signature' );

        // Validate token format
        $token_manager = new SMCB_Token_Manager();
        if ( ! $token_manager->validate_token_format( $token ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Invalid contract link.', 'skinny-moo-contract-builder' ),
                ),
                400
            );
        }

        // Get contract
        $contract_model = new SMCB_Contract();
        $contract = $contract_model->get_by_token( $token );

        if ( ! $contract ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Contract not found.', 'skinny-moo-contract-builder' ),
                ),
                404
            );
        }

        // Check if contract can be signed
        $can_sign = $token_manager->can_sign( $contract );
        if ( ! $can_sign['valid'] ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => $can_sign['message'],
                ),
                403
            );
        }

        // Rate limiting
        $ip_address = $this->get_client_ip();
        if ( ! $token_manager->check_rate_limit( $ip_address ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Too many signature attempts. Please try again later.', 'skinny-moo-contract-builder' ),
                ),
                429
            );
        }
        $token_manager->record_signature_attempt( $ip_address );

        // Validate signature data
        if ( empty( $signature ) || strpos( $signature, 'data:image/png;base64,' ) !== 0 ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Invalid signature data.', 'skinny-moo-contract-builder' ),
                ),
                400
            );
        }

        // Validate signed name
        if ( strlen( $signed_name ) < 2 || strlen( $signed_name ) > 255 ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Please enter a valid name.', 'skinny-moo-contract-builder' ),
                ),
                400
            );
        }

        // Record signature
        $result = $contract_model->record_signature(
            $contract->id,
            $signature,
            $signed_name,
            $ip_address
        );

        if ( ! $result ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Failed to save signature. Please try again.', 'skinny-moo-contract-builder' ),
                ),
                500
            );
        }

        // Refresh contract data
        $contract = $contract_model->get( $contract->id );

        // Generate signed PDFs
        try {
            $pdf_generator = new SMCB_PDF_Generator( $contract );
            $paths = $pdf_generator->generate_all( true );
        } catch ( Exception $e ) {
            error_log( 'SMCB PDF Generation Error: ' . $e->getMessage() );
        }

        // Refresh contract to get PDF paths
        $contract = $contract_model->get( $contract->id );

        // Send confirmation emails
        $email = new SMCB_Email( $contract );

        // Collect PDFs for attachment
        $attachments = array();
        if ( ! empty( $contract->signed_contract_pdf_path ) && file_exists( $contract->signed_contract_pdf_path ) ) {
            $attachments[] = $contract->signed_contract_pdf_path;
        }
        if ( ! empty( $contract->invoice_pdf_path ) && file_exists( $contract->invoice_pdf_path ) ) {
            $attachments[] = $contract->invoice_pdf_path;
        }

        // Send to client
        $email->send_signature_confirmation( $attachments );

        // Notify admin
        $email->send_admin_notification();

        return new WP_REST_Response(
            array(
                'success' => true,
                'message' => __( 'Contract signed successfully! A confirmation email has been sent to your email address.', 'skinny-moo-contract-builder' ),
            ),
            200
        );
    }

    /**
     * Get contract endpoint handler.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response Response object.
     */
    public function get_contract( $request ) {
        $token = $request->get_param( 'token' );

        // Validate token format
        $token_manager = new SMCB_Token_Manager();
        if ( ! $token_manager->validate_token_format( $token ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Invalid contract link.', 'skinny-moo-contract-builder' ),
                ),
                400
            );
        }

        // Get contract
        $contract_model = new SMCB_Contract();
        $contract = $contract_model->get_by_token( $token );

        if ( ! $contract ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Contract not found.', 'skinny-moo-contract-builder' ),
                ),
                404
            );
        }

        // Check if viewable
        $can_sign = $token_manager->can_sign( $contract );

        // Return limited public data
        return new WP_REST_Response(
            array(
                'success'  => true,
                'contract' => array(
                    'contract_number'    => $contract->contract_number,
                    'event_name'         => $contract->event_name,
                    'performance_date'   => $contract->performance_date,
                    'client_company'     => $contract->client_company_name,
                    'status'             => $contract->status,
                    'total_compensation' => $contract->calculated->total_compensation,
                    'deposit_amount'     => $contract->calculated->deposit_amount,
                    'can_sign'           => $can_sign['valid'],
                    'sign_message'       => $can_sign['message'],
                ),
            ),
            200
        );
    }

    /**
     * Sanitize signature data.
     *
     * @param string $signature Base64 signature data.
     * @return string Sanitized signature.
     */
    public function sanitize_signature( $signature ) {
        // Only allow data URLs for PNG images
        if ( strpos( $signature, 'data:image/png;base64,' ) !== 0 ) {
            return '';
        }

        // Validate base64 data
        $base64_data = substr( $signature, strlen( 'data:image/png;base64,' ) );
        if ( ! preg_match( '/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $base64_data ) ) {
            return '';
        }

        // Check decoded size (limit to 500KB)
        $decoded = base64_decode( $base64_data, true );
        if ( $decoded === false || strlen( $decoded ) > 512000 ) {
            return '';
        }

        return $signature;
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
