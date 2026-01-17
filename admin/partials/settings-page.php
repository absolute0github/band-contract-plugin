<?php
/**
 * Admin settings page template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap smcb-admin smcb-settings-page">
    <h1><?php esc_html_e( 'Contract Builder Settings', 'skinny-moo-contract-builder' ); ?></h1>

    <?php if ( $test_mode ) : ?>
        <div class="notice notice-warning">
            <p><strong><?php esc_html_e( 'Test Mode is ENABLED', 'skinny-moo-contract-builder' ); ?></strong> - <?php esc_html_e( 'All emails will be redirected to the test email address.', 'skinny-moo-contract-builder' ); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <?php wp_nonce_field( 'smcb_save_settings', 'smcb_settings_nonce' ); ?>

        <table class="form-table" role="presentation">
            <tbody>
                <!-- Test Mode Section -->
                <tr>
                    <th scope="row" colspan="2">
                        <h2 class="title"><?php esc_html_e( 'Test Mode', 'skinny-moo-contract-builder' ); ?></h2>
                        <p class="description"><?php esc_html_e( 'Use test mode to preview contracts and send test emails without affecting production.', 'skinny-moo-contract-builder' ); ?></p>
                    </th>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Enable Test Mode', 'skinny-moo-contract-builder' ); ?></th>
                    <td>
                        <label for="smcb_test_mode">
                            <input type="checkbox" id="smcb_test_mode" name="smcb_test_mode" value="1" <?php checked( $test_mode, 1 ); ?>>
                            <?php esc_html_e( 'Enable test mode', 'skinny-moo-contract-builder' ); ?>
                        </label>
                        <p class="description"><?php esc_html_e( 'When enabled, all emails will be redirected to the test email address and "Fill Test Data" button will appear on the contract form.', 'skinny-moo-contract-builder' ); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="smcb_test_email"><?php esc_html_e( 'Test Email Address', 'skinny-moo-contract-builder' ); ?></label>
                    </th>
                    <td>
                        <input type="email" id="smcb_test_email" name="smcb_test_email" value="<?php echo esc_attr( $test_email ); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e( 'All contract emails will be sent to this address when test mode is enabled.', 'skinny-moo-contract-builder' ); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="submit">
            <button type="submit" name="smcb_save_settings" class="button button-primary"><?php esc_html_e( 'Save Settings', 'skinny-moo-contract-builder' ); ?></button>
        </p>
    </form>
</div>
