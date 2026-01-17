<?php
/**
 * Email handling class.
 *
 * Manages all email notifications for contracts.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Email
 *
 * Handles contract email notifications.
 */
class SMCB_Email {

    /**
     * Contract object.
     *
     * @var object
     */
    private $contract;

    /**
     * Constructor.
     *
     * @param object $contract Optional. Contract object.
     */
    public function __construct( $contract = null ) {
        $this->contract = $contract;
    }

    /**
     * Set contract for email.
     *
     * @param object $contract Contract object.
     */
    public function set_contract( $contract ) {
        $this->contract = $contract;
    }

    /**
     * Send contract to client.
     *
     * @return bool True on success, false on failure.
     */
    public function send_contract() {
        if ( ! $this->contract ) {
            return false;
        }

        $to = $this->contract->email;
        $subject = $this->get_contract_subject();
        $message = $this->get_contract_email_body();
        $headers = $this->get_email_headers();

        // Add reply-to header
        $headers[] = 'Reply-To: ' . SMCB_COMPANY_NAME . ' <' . SMCB_COMPANY_EMAIL . '>';

        $sent = wp_mail( $to, $subject, $message, $headers );

        if ( $sent ) {
            // Update contract status to sent
            $contract_model = new SMCB_Contract();
            $contract_model->update_status( $this->contract->id, 'sent' );
        }

        return $sent;
    }

    /**
     * Send signature confirmation to client.
     *
     * @param array $attachments Array of PDF file paths.
     * @return bool True on success, false on failure.
     */
    public function send_signature_confirmation( $attachments = array() ) {
        if ( ! $this->contract ) {
            return false;
        }

        $to = $this->contract->email;
        $subject = $this->get_confirmation_subject();
        $message = $this->get_confirmation_email_body();
        $headers = $this->get_email_headers();

        // Add reply-to header
        $headers[] = 'Reply-To: ' . SMCB_COMPANY_NAME . ' <' . SMCB_COMPANY_EMAIL . '>';

        return wp_mail( $to, $subject, $message, $headers, $attachments );
    }

    /**
     * Send admin notification when contract is signed.
     *
     * @return bool True on success, false on failure.
     */
    public function send_admin_notification() {
        if ( ! $this->contract ) {
            return false;
        }

        $to = SMCB_COMPANY_EMAIL;
        $subject = $this->get_admin_notification_subject();
        $message = $this->get_admin_notification_body();
        $headers = $this->get_email_headers();

        return wp_mail( $to, $subject, $message, $headers );
    }

    /**
     * Get email headers.
     *
     * @return array Email headers.
     */
    private function get_email_headers() {
        $from_name = get_option( 'smcb_email_from_name', SMCB_COMPANY_NAME );
        $from_email = get_option( 'smcb_email_from_address', SMCB_COMPANY_EMAIL );

        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        );
    }

    /**
     * Get contract email subject.
     *
     * @return string Email subject.
     */
    private function get_contract_subject() {
        return sprintf(
            __( 'Performance Contract for %s - %s', 'skinny-moo-contract-builder' ),
            $this->contract->event_name,
            smcb_format_date( $this->contract->performance_date )
        );
    }

    /**
     * Get confirmation email subject.
     *
     * @return string Email subject.
     */
    private function get_confirmation_subject() {
        return sprintf(
            __( 'Signed Contract Confirmation - %s', 'skinny-moo-contract-builder' ),
            $this->contract->event_name
        );
    }

    /**
     * Get admin notification subject.
     *
     * @return string Email subject.
     */
    private function get_admin_notification_subject() {
        return sprintf(
            __( 'Contract Signed: %s - %s', 'skinny-moo-contract-builder' ),
            $this->contract->client_company_name,
            $this->contract->event_name
        );
    }

    /**
     * Get contract email body.
     *
     * @return string HTML email body.
     */
    private function get_contract_email_body() {
        $token_manager = new SMCB_Token_Manager();
        $contract_url = $token_manager->get_contract_url( $this->contract->access_token );

        ob_start();
        include SMCB_PLUGIN_DIR . 'templates/email/contract-sent.php';
        return ob_get_clean();
    }

    /**
     * Get confirmation email body.
     *
     * @return string HTML email body.
     */
    private function get_confirmation_email_body() {
        ob_start();
        include SMCB_PLUGIN_DIR . 'templates/email/signature-confirmation.php';
        return ob_get_clean();
    }

    /**
     * Get admin notification body.
     *
     * @return string HTML email body.
     */
    private function get_admin_notification_body() {
        ob_start();
        include SMCB_PLUGIN_DIR . 'templates/email/admin-notification.php';
        return ob_get_clean();
    }

    /**
     * Get common email styles.
     *
     * @return string CSS styles.
     */
    public static function get_email_styles() {
        return '
            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
                font-size: 16px;
                line-height: 1.6;
                color: #333333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .email-wrapper {
                max-width: 600px;
                margin: 0 auto;
                background-color: #ffffff;
                padding: 0;
            }
            .email-header {
                background-color: #1a1a1a;
                padding: 30px;
                text-align: center;
            }
            .email-header img {
                max-width: 200px;
                height: auto;
            }
            .email-body {
                padding: 40px 30px;
            }
            .email-footer {
                background-color: #f9f9f9;
                padding: 20px 30px;
                text-align: center;
                font-size: 14px;
                color: #666666;
                border-top: 1px solid #eeeeee;
            }
            h1 {
                color: #1a1a1a;
                font-size: 24px;
                margin: 0 0 20px 0;
            }
            h2 {
                color: #1a1a1a;
                font-size: 20px;
                margin: 30px 0 15px 0;
            }
            p {
                margin: 0 0 15px 0;
            }
            .button {
                display: inline-block;
                background-color: #c41230;
                color: #ffffff !important;
                text-decoration: none;
                padding: 15px 30px;
                border-radius: 5px;
                font-weight: bold;
                margin: 20px 0;
            }
            .button:hover {
                background-color: #a30f28;
            }
            .details-box {
                background-color: #f9f9f9;
                border-left: 4px solid #c41230;
                padding: 20px;
                margin: 20px 0;
            }
            .details-box strong {
                display: block;
                margin-bottom: 5px;
            }
            .highlight {
                color: #c41230;
                font-weight: bold;
            }
            ul {
                padding-left: 20px;
            }
            li {
                margin-bottom: 8px;
            }
        ';
    }

    /**
     * Wrap content in email template.
     *
     * @param string $content Email content.
     * @param string $title   Email title.
     * @return string Complete HTML email.
     */
    public static function wrap_in_template( $content, $title = '' ) {
        $logo_url = SMCB_PLUGIN_URL . 'assets/images/logo.png';
        $styles = self::get_email_styles();

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . esc_html( $title ) . '</title>
    <style>' . $styles . '</style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-header">
            <img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( SMCB_COMPANY_NAME ) . '">
        </div>
        <div class="email-body">
            ' . $content . '
        </div>
        <div class="email-footer">
            <p>' . esc_html( SMCB_COMPANY_NAME ) . '</p>
            <p>' . esc_html( SMCB_COMPANY_ADDRESS ) . ', ' . esc_html( SMCB_COMPANY_CITY ) . ', ' . esc_html( SMCB_COMPANY_STATE ) . ' ' . esc_html( SMCB_COMPANY_ZIP ) . '</p>
            <p>' . esc_html( SMCB_COMPANY_PHONE ) . ' | <a href="mailto:' . esc_attr( SMCB_COMPANY_EMAIL ) . '">' . esc_html( SMCB_COMPANY_EMAIL ) . '</a></p>
            <p><a href="' . esc_url( SMCB_COMPANY_WEBSITE ) . '">' . esc_html( SMCB_COMPANY_WEBSITE ) . '</a></p>
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
