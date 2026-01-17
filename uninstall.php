<?php
/**
 * Uninstall script for Skinny Moo Contract Builder.
 *
 * This file runs when the plugin is deleted via the WordPress admin.
 * It removes all database tables and options created by the plugin.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Check if we should delete all data
$delete_data = get_option( 'smcb_delete_data_on_uninstall', false );

if ( $delete_data ) {
    global $wpdb;

    // Drop database tables (in correct order due to foreign keys)
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smcb_contract_activity_log" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smcb_invoice_line_items" );
    $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smcb_contracts" );

    // Delete plugin options
    $options = array(
        'smcb_db_version',
        'smcb_token_expiration_days',
        'smcb_default_deposit_percent',
        'smcb_early_loadin_hourly_rate',
        'smcb_default_set_length',
        'smcb_default_break_length',
        'smcb_default_number_of_sets',
        'smcb_email_from_name',
        'smcb_email_from_address',
        'smcb_delete_data_on_uninstall',
    );

    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Delete transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_smcb_%' OR option_name LIKE '_transient_timeout_smcb_%'"
    );

    // Delete uploaded files
    $upload_dir = wp_upload_dir();
    $smcb_dir = $upload_dir['basedir'] . '/smcb-contracts';

    if ( is_dir( $smcb_dir ) ) {
        smcb_recursive_delete( $smcb_dir );
    }
}

/**
 * Recursively delete a directory and its contents.
 *
 * @param string $dir Directory path.
 */
function smcb_recursive_delete( $dir ) {
    if ( is_dir( $dir ) ) {
        $objects = scandir( $dir );
        foreach ( $objects as $object ) {
            if ( $object !== '.' && $object !== '..' ) {
                $path = $dir . '/' . $object;
                if ( is_dir( $path ) ) {
                    smcb_recursive_delete( $path );
                } else {
                    wp_delete_file( $path );
                }
            }
        }
        rmdir( $dir );
    }
}
