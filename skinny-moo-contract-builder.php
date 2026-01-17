<?php
/**
 * Plugin Name: Skinny Moo Contract Builder
 * Plugin URI: https://absolute0.net
 * Description: Create, send, and manage performance agreements and invoices with digital signing capabilities.
 * Version: 1.0.3
 * Author: Jay Goodman
 * Author URI: https://absolute0.net
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: skinny-moo-contract-builder
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version.
 */
define( 'SMCB_VERSION', '1.0.3' );

/**
 * Plugin directory path.
 */
define( 'SMCB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'SMCB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename.
 */
define( 'SMCB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Company information constants.
 */
define( 'SMCB_COMPANY_NAME', 'Skinny Moo Media Services, LLC' );
define( 'SMCB_COMPANY_ADDRESS', '4448 Luckystone Way' );
define( 'SMCB_COMPANY_CITY', 'Medina' );
define( 'SMCB_COMPANY_STATE', 'Ohio' );
define( 'SMCB_COMPANY_ZIP', '44256' );
define( 'SMCB_COMPANY_PHONE', '(330) 421-1960' );
define( 'SMCB_COMPANY_EMAIL', 'booking@skinnymoo.com' );
define( 'SMCB_COMPANY_WEBSITE', 'http://www.skinnymoo.com' );
define( 'SMCB_COMPANY_EIN', '20-4746552' );

/**
 * Token expiration in days.
 */
define( 'SMCB_TOKEN_EXPIRATION_DAYS', 30 );

/**
 * Early load-in hourly rate.
 */
define( 'SMCB_EARLY_LOADIN_RATE', 100 );

/**
 * GitHub repository for updates.
 */
define( 'SMCB_GITHUB_REPO', 'https://github.com/absolute0github/band-contract-plugin' );

/**
 * Initialize Plugin Update Checker.
 */
function smcb_init_update_checker() {
    // Load the update checker library
    $update_checker_path = SMCB_PLUGIN_DIR . 'vendor/plugin-update-checker/plugin-update-checker.php';

    if ( file_exists( $update_checker_path ) ) {
        require_once $update_checker_path;

        try {
            $update_checker = YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
                SMCB_GITHUB_REPO,
                __FILE__,
                'skinny-moo-contract-builder'
            );

            // Set the branch that contains the stable release
            $update_checker->setBranch( 'main' );

            // Optional: If your repository is private, uncomment and add your token
            // $update_checker->setAuthentication( 'your-github-personal-access-token' );

            // Optional: Enable release assets (if you attach zip files to releases)
            $update_checker->getVcsApi()->enableReleaseAssets();
        } catch ( Exception $e ) {
            // Silently fail if update checker has issues
            error_log( 'SMCB Update Checker Error: ' . $e->getMessage() );
        }
    }
}
add_action( 'init', 'smcb_init_update_checker' );

/**
 * Increase timeout for GitHub API requests.
 */
function smcb_http_request_timeout( $timeout, $url ) {
    if ( strpos( $url, 'api.github.com' ) !== false || strpos( $url, 'github.com' ) !== false ) {
        return 15; // 15 seconds timeout for GitHub
    }
    return $timeout;
}
add_filter( 'http_request_timeout', 'smcb_http_request_timeout', 10, 2 );

/**
 * Activation hook.
 */
function smcb_activate() {
    require_once SMCB_PLUGIN_DIR . 'includes/class-smcb-activator.php';
    SMCB_Activator::activate();
}
register_activation_hook( __FILE__, 'smcb_activate' );

/**
 * Deactivation hook.
 */
function smcb_deactivate() {
    // Clean up transients and scheduled events if any
    wp_clear_scheduled_hook( 'smcb_daily_cleanup' );
}
register_deactivation_hook( __FILE__, 'smcb_deactivate' );

/**
 * Load required files.
 */
function smcb_load_dependencies() {
    // Core classes
    require_once SMCB_PLUGIN_DIR . 'includes/class-smcb-contract.php';
    require_once SMCB_PLUGIN_DIR . 'includes/class-smcb-token-manager.php';
    require_once SMCB_PLUGIN_DIR . 'includes/class-smcb-email.php';
    require_once SMCB_PLUGIN_DIR . 'includes/class-smcb-pdf-generator.php';

    // Admin
    if ( is_admin() ) {
        require_once SMCB_PLUGIN_DIR . 'admin/class-smcb-admin.php';
    }

    // Public
    require_once SMCB_PLUGIN_DIR . 'public/class-smcb-public.php';

    // REST API
    require_once SMCB_PLUGIN_DIR . 'api/class-smcb-rest-api.php';
}
add_action( 'plugins_loaded', 'smcb_load_dependencies' );

/**
 * Initialize admin functionality.
 */
function smcb_init_admin() {
    if ( is_admin() && class_exists( 'SMCB_Admin' ) ) {
        $admin = new SMCB_Admin();
        $admin->init();
    }
}
add_action( 'init', 'smcb_init_admin' );

/**
 * Initialize public functionality.
 */
function smcb_init_public() {
    if ( class_exists( 'SMCB_Public' ) ) {
        $public = new SMCB_Public();
        $public->init();
    }
}
add_action( 'init', 'smcb_init_public' );

/**
 * Initialize REST API.
 */
function smcb_init_rest_api() {
    if ( class_exists( 'SMCB_REST_API' ) ) {
        $api = new SMCB_REST_API();
        $api->init();
    }
}
add_action( 'rest_api_init', 'smcb_init_rest_api' );

/**
 * Add settings link to plugins page.
 *
 * @param array $links Plugin action links.
 * @return array Modified plugin action links.
 */
function smcb_plugin_action_links( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=smcb-contracts' ) . '">' . __( 'Contracts', 'skinny-moo-contract-builder' ) . '</a>';
    array_unshift( $links, $settings_link );

    // Add check for updates link
    $update_link = '<a href="' . wp_nonce_url( admin_url( 'plugins.php?puc_check_for_updates=1&puc_slug=skinny-moo-contract-builder' ), 'puc_check_for_updates' ) . '">' . __( 'Check for Updates', 'skinny-moo-contract-builder' ) . '</a>';
    $links[] = $update_link;

    return $links;
}
add_filter( 'plugin_action_links_' . SMCB_PLUGIN_BASENAME, 'smcb_plugin_action_links' );

/**
 * Get contract statuses.
 *
 * @return array Contract statuses.
 */
function smcb_get_contract_statuses() {
    return array(
        'draft'     => __( 'Draft', 'skinny-moo-contract-builder' ),
        'sent'      => __( 'Sent', 'skinny-moo-contract-builder' ),
        'viewed'    => __( 'Viewed', 'skinny-moo-contract-builder' ),
        'signed'    => __( 'Signed', 'skinny-moo-contract-builder' ),
        'cancelled' => __( 'Cancelled', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get number of sets options.
 *
 * @return array Number of sets options.
 */
function smcb_get_number_of_sets_options() {
    return array(
        '1' => '1',
        '2' => '2',
        '3' => '3',
        '4' => '4',
    );
}

/**
 * Get inside/outside options.
 *
 * @return array Inside/outside options.
 */
function smcb_get_inside_outside_options() {
    return array(
        'inside'  => __( 'Inside', 'skinny-moo-contract-builder' ),
        'outside' => __( 'Outside', 'skinny-moo-contract-builder' ),
        'both'    => __( 'Both', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get stage available options.
 *
 * @return array Stage available options.
 */
function smcb_get_stage_options() {
    return array(
        'yes' => __( 'Yes', 'skinny-moo-contract-builder' ),
        'no'  => __( 'No', 'skinny-moo-contract-builder' ),
        'tbd' => __( 'TBD', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get production options.
 *
 * @return array Production options.
 */
function smcb_get_production_options() {
    return array(
        'we_provide'   => __( 'We Provide', 'skinny-moo-contract-builder' ),
        'they_provide' => __( 'They Provide', 'skinny-moo-contract-builder' ),
        'shared'       => __( 'Shared', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get genre options.
 *
 * @return array Genre options.
 */
function smcb_get_genre_options() {
    return array(
        ''           => __( 'Select Genre', 'skinny-moo-contract-builder' ),
        'rock'       => __( 'Rock', 'skinny-moo-contract-builder' ),
        'pop'        => __( 'Pop', 'skinny-moo-contract-builder' ),
        'country'    => __( 'Country', 'skinny-moo-contract-builder' ),
        'jazz'       => __( 'Jazz', 'skinny-moo-contract-builder' ),
        'blues'      => __( 'Blues', 'skinny-moo-contract-builder' ),
        'rnb'        => __( 'R&B', 'skinny-moo-contract-builder' ),
        'dance_edm'  => __( 'Dance/EDM', 'skinny-moo-contract-builder' ),
        'oldies'     => __( 'Oldies', 'skinny-moo-contract-builder' ),
        'mix'        => __( 'Mix/Variety', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get accommodations options.
 *
 * @return array Accommodations options.
 */
function smcb_get_accommodations_options() {
    return array(
        ''    => __( 'Select Option', 'skinny-moo-contract-builder' ),
        'yes' => __( 'Yes', 'skinny-moo-contract-builder' ),
        'no'  => __( 'No', 'skinny-moo-contract-builder' ),
        'na'  => __( 'N/A', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Get audience rating options.
 *
 * @return array Audience rating options.
 */
function smcb_get_audience_rating_options() {
    return array(
        'g'     => __( 'G', 'skinny-moo-contract-builder' ),
        'pg'    => __( 'PG', 'skinny-moo-contract-builder' ),
        'pg-13' => __( 'PG-13', 'skinny-moo-contract-builder' ),
        'r'     => __( 'R', 'skinny-moo-contract-builder' ),
    );
}

/**
 * Format currency for display.
 *
 * @param float $amount Amount to format.
 * @return string Formatted currency.
 */
function smcb_format_currency( $amount ) {
    return '$' . number_format( (float) $amount, 2 );
}

/**
 * Format time for display.
 *
 * @param string $time Time in H:i format.
 * @return string Formatted time.
 */
function smcb_format_time( $time ) {
    if ( empty( $time ) ) {
        return '';
    }
    return date( 'g:i A', strtotime( $time ) );
}

/**
 * Format date for display.
 *
 * @param string $date Date string.
 * @return string Formatted date.
 */
function smcb_format_date( $date ) {
    if ( empty( $date ) ) {
        return '';
    }
    return date( 'F j, Y', strtotime( $date ) );
}

/**
 * Calculate set times based on performance parameters.
 *
 * @param string $first_set_start First set start time (H:i format).
 * @param int    $num_sets Number of sets.
 * @param int    $set_length Set length in minutes.
 * @param int    $break_length Break length in minutes.
 * @return array Array of set times with start and end.
 */
function smcb_calculate_set_times( $first_set_start, $num_sets, $set_length, $break_length ) {
    $set_times = array();
    $current_time = strtotime( $first_set_start );

    for ( $i = 1; $i <= $num_sets; $i++ ) {
        $start_time = $current_time;
        $end_time = $current_time + ( $set_length * 60 );

        $set_times[] = array(
            'set_number' => $i,
            'start'      => date( 'g:i A', $start_time ),
            'end'        => date( 'g:i A', $end_time ),
        );

        // Move to next set start (end time + break)
        $current_time = $end_time + ( $break_length * 60 );
    }

    return $set_times;
}

/**
 * Generate contract number.
 *
 * @return string Contract number in format SM-YYYY-XXXX.
 */
function smcb_generate_contract_number() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smcb_contracts';
    $year = date( 'Y' );

    // Get the highest contract number for this year
    $last_number = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING(contract_number, 9) AS UNSIGNED)) FROM {$table_name} WHERE contract_number LIKE %s",
            'SM-' . $year . '-%'
        )
    );

    $next_number = $last_number ? $last_number + 1 : 1;

    return sprintf( 'SM-%s-%04d', $year, $next_number );
}

/**
 * Generate invoice number.
 *
 * @return string Invoice number in format INV-YYYY-XXXX.
 */
function smcb_generate_invoice_number() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'smcb_contracts';
    $year = date( 'Y' );

    // Get the highest invoice number for this year
    $last_number = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT MAX(CAST(SUBSTRING(invoice_number, 10) AS UNSIGNED)) FROM {$table_name} WHERE invoice_number LIKE %s",
            'INV-' . $year . '-%'
        )
    );

    $next_number = $last_number ? $last_number + 1 : 1;

    return sprintf( 'INV-%s-%04d', $year, $next_number );
}
