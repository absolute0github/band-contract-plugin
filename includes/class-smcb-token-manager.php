<?php
/**
 * Token Manager class.
 *
 * Handles secure token generation and validation for contract access.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Token_Manager
 *
 * Manages access tokens for contract security.
 */
class SMCB_Token_Manager {

    /**
     * Token length in characters.
     *
     * @var int
     */
    const TOKEN_LENGTH = 64;

    /**
     * Rate limit window in seconds.
     *
     * @var int
     */
    const RATE_LIMIT_WINDOW = 300; // 5 minutes

    /**
     * Maximum signature attempts per window.
     *
     * @var int
     */
    const MAX_SIGNATURE_ATTEMPTS = 5;

    /**
     * Generate a cryptographically secure token.
     *
     * @return string 64-character hexadecimal token.
     */
    public function generate_token() {
        return bin2hex( random_bytes( self::TOKEN_LENGTH / 2 ) );
    }

    /**
     * Get token expiration date.
     *
     * @param int $days Optional. Number of days until expiration.
     * @return string MySQL datetime format.
     */
    public function get_expiration_date( $days = null ) {
        if ( $days === null ) {
            $days = defined( 'SMCB_TOKEN_EXPIRATION_DAYS' ) ? SMCB_TOKEN_EXPIRATION_DAYS : 30;
        }

        return date( 'Y-m-d H:i:s', strtotime( '+' . intval( $days ) . ' days' ) );
    }

    /**
     * Validate a token.
     *
     * @param string $token Token to validate.
     * @return bool True if token format is valid.
     */
    public function validate_token_format( $token ) {
        // Check if token is exactly 64 characters and hexadecimal
        if ( strlen( $token ) !== self::TOKEN_LENGTH ) {
            return false;
        }

        return ctype_xdigit( $token );
    }

    /**
     * Check if a token has expired.
     *
     * @param string $expires_at Expiration datetime.
     * @return bool True if expired.
     */
    public function is_expired( $expires_at ) {
        return strtotime( $expires_at ) < current_time( 'timestamp' );
    }

    /**
     * Check if contract can be signed (not expired, valid status).
     *
     * @param object $contract Contract object.
     * @return array Array with 'valid' boolean and 'message' string.
     */
    public function can_sign( $contract ) {
        // Check if contract exists
        if ( ! $contract ) {
            return array(
                'valid'   => false,
                'message' => __( 'Contract not found.', 'skinny-moo-contract-builder' ),
            );
        }

        // Check token expiration
        if ( $this->is_expired( $contract->token_expires_at ) ) {
            return array(
                'valid'   => false,
                'message' => __( 'This contract link has expired. Please contact us for a new link.', 'skinny-moo-contract-builder' ),
            );
        }

        // Check contract status
        if ( $contract->status === 'signed' ) {
            return array(
                'valid'   => false,
                'message' => __( 'This contract has already been signed.', 'skinny-moo-contract-builder' ),
            );
        }

        if ( $contract->status === 'cancelled' ) {
            return array(
                'valid'   => false,
                'message' => __( 'This contract has been cancelled.', 'skinny-moo-contract-builder' ),
            );
        }

        if ( $contract->status === 'draft' ) {
            return array(
                'valid'   => false,
                'message' => __( 'This contract is not available yet.', 'skinny-moo-contract-builder' ),
            );
        }

        return array(
            'valid'   => true,
            'message' => '',
        );
    }

    /**
     * Check rate limiting for signature attempts.
     *
     * @param string $ip_address Client IP address.
     * @return bool True if rate limit not exceeded.
     */
    public function check_rate_limit( $ip_address ) {
        $transient_key = 'smcb_sig_attempts_' . md5( $ip_address );
        $attempts = get_transient( $transient_key );

        if ( $attempts === false ) {
            return true;
        }

        return intval( $attempts ) < self::MAX_SIGNATURE_ATTEMPTS;
    }

    /**
     * Record a signature attempt for rate limiting.
     *
     * @param string $ip_address Client IP address.
     */
    public function record_signature_attempt( $ip_address ) {
        $transient_key = 'smcb_sig_attempts_' . md5( $ip_address );
        $attempts = get_transient( $transient_key );

        if ( $attempts === false ) {
            set_transient( $transient_key, 1, self::RATE_LIMIT_WINDOW );
        } else {
            set_transient( $transient_key, intval( $attempts ) + 1, self::RATE_LIMIT_WINDOW );
        }
    }

    /**
     * Generate a secure contract view URL.
     *
     * @param string $token Access token.
     * @return string Full URL to view contract.
     */
    public function get_contract_url( $token ) {
        return add_query_arg(
            array(
                'smcb_contract' => 'view',
                'token'         => $token,
            ),
            home_url()
        );
    }

    /**
     * Clear expired transients (for cleanup).
     */
    public static function cleanup_expired_transients() {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
                '_transient_timeout_smcb_sig_attempts_%',
                time()
            )
        );

        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_smcb_sig_attempts_%'
             AND option_name NOT IN (
                SELECT REPLACE(option_name, '_transient_timeout_', '_transient_')
                FROM {$wpdb->options}
                WHERE option_name LIKE '_transient_timeout_smcb_sig_attempts_%'
             )"
        );
    }
}
