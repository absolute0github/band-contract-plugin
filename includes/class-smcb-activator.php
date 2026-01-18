<?php
/**
 * Plugin activation class.
 *
 * Creates database tables and sets up initial options.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Activator
 *
 * Handles plugin activation tasks.
 */
class SMCB_Activator {

    /**
     * Activate the plugin.
     *
     * Creates database tables and sets default options.
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        self::create_upload_directory();
        self::maybe_upgrade();

        // Flush rewrite rules for custom endpoints
        flush_rewrite_rules();
    }

    /**
     * Check and run database upgrades if needed.
     */
    public static function maybe_upgrade() {
        $current_version = get_option( 'smcb_db_version', '1.0.0' );

        // Upgrade to 1.0.9 - Add payment tracking fields
        if ( version_compare( $current_version, '1.0.9', '<' ) ) {
            self::upgrade_to_1_0_9();
        }
    }

    /**
     * Upgrade database to version 1.0.9.
     * Adds payment tracking fields to contracts table.
     */
    private static function upgrade_to_1_0_9() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'smcb_contracts';

        // Check if payment columns exist
        $columns = $wpdb->get_col( "DESCRIBE {$table_name}", 0 );

        if ( ! in_array( 'deposit_paid', $columns, true ) ) {
            $wpdb->query( "ALTER TABLE {$table_name}
                ADD COLUMN deposit_payment_method varchar(20) AFTER signed_contract_pdf_path,
                ADD COLUMN deposit_paid tinyint(1) NOT NULL DEFAULT 0 AFTER deposit_payment_method,
                ADD COLUMN deposit_paid_at datetime AFTER deposit_paid,
                ADD COLUMN deposit_amount_received decimal(10,2) DEFAULT 0.00 AFTER deposit_paid_at,
                ADD COLUMN deposit_payment_notes text AFTER deposit_amount_received,
                ADD COLUMN balance_payment_method varchar(20) AFTER deposit_payment_notes,
                ADD COLUMN balance_paid tinyint(1) NOT NULL DEFAULT 0 AFTER balance_payment_method,
                ADD COLUMN balance_paid_at datetime AFTER balance_paid,
                ADD COLUMN balance_amount_received decimal(10,2) DEFAULT 0.00 AFTER balance_paid_at,
                ADD COLUMN balance_payment_notes text AFTER balance_amount_received"
            );
        }

        update_option( 'smcb_db_version', '1.0.9' );
    }

    /**
     * Create database tables.
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Main contracts table
        $table_contracts = $wpdb->prefix . 'smcb_contracts';
        $sql_contracts = "CREATE TABLE {$table_contracts} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contract_number varchar(20) NOT NULL,
            invoice_number varchar(20) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'draft',

            -- Client Information
            client_company_name varchar(255) NOT NULL,
            contact_person_name varchar(255) NOT NULL,
            street_address varchar(255) NOT NULL,
            city varchar(100) NOT NULL,
            state varchar(50) NOT NULL,
            zip_code varchar(20) NOT NULL,
            phone varchar(30) NOT NULL,
            email varchar(255) NOT NULL,

            -- Performance Details
            performance_date date NOT NULL,
            event_name varchar(255) NOT NULL,
            first_set_start_time time NOT NULL,
            number_of_sets tinyint(1) NOT NULL DEFAULT 3,
            set_length int(3) NOT NULL DEFAULT 60,
            break_length int(3) NOT NULL DEFAULT 30,
            load_in_time time NOT NULL,

            -- Venue Details
            venue_name varchar(255),
            venue_address varchar(255),
            venue_city varchar(100),
            venue_state varchar(50),
            venue_zip varchar(20),
            venue_contact_person varchar(255),
            venue_phone varchar(30),
            venue_email varchar(255),
            inside_outside varchar(20) NOT NULL DEFAULT 'inside',
            stage_available varchar(10) NOT NULL DEFAULT 'tbd',
            power_requirements text,
            loadin_location text,
            performance_location text,

            -- Early Load-in
            early_loadin_required tinyint(1) NOT NULL DEFAULT 0,
            early_loadin_hours int(3) DEFAULT 0,

            -- Production & Equipment
            sound_system varchar(20) NOT NULL DEFAULT 'we_provide',
            lights varchar(20) NOT NULL DEFAULT 'we_provide',
            music_between_sets varchar(20) NOT NULL DEFAULT 'we_provide',
            outside_production tinyint(1) NOT NULL DEFAULT 0,
            outside_production_notes text,

            -- Music Preferences
            preferred_genre varchar(50),

            -- Travel & Accommodations
            accommodations_provided varchar(10),
            accommodation_cost_offset decimal(10,2) DEFAULT 0.00,
            mileage_travel_fee decimal(10,2) DEFAULT 0.00,

            -- Compensation
            base_compensation decimal(10,2) NOT NULL,
            deposit_percentage int(3) NOT NULL DEFAULT 30,
            additional_compensation text,

            -- Services Provided
            services_description text NOT NULL,
            attire varchar(255),
            audience_rating varchar(10) NOT NULL DEFAULT 'pg-13',

            -- Additional Content
            cover_letter_message text,
            additional_contract_notes text,

            -- Token and Security
            access_token varchar(64) NOT NULL,
            token_expires_at datetime NOT NULL,

            -- Signatures
            client_signature longtext,
            client_signed_at datetime,
            client_signed_ip varchar(45),
            client_signed_name varchar(255),
            performer_signature longtext,
            performer_signed_at datetime,

            -- PDF Storage
            cover_letter_pdf_path varchar(500),
            contract_pdf_path varchar(500),
            invoice_pdf_path varchar(500),
            signed_contract_pdf_path varchar(500),

            -- Payment Tracking
            deposit_payment_method varchar(20),
            deposit_paid tinyint(1) NOT NULL DEFAULT 0,
            deposit_paid_at datetime,
            deposit_amount_received decimal(10,2) DEFAULT 0.00,
            deposit_payment_notes text,
            balance_payment_method varchar(20),
            balance_paid tinyint(1) NOT NULL DEFAULT 0,
            balance_paid_at datetime,
            balance_amount_received decimal(10,2) DEFAULT 0.00,
            balance_payment_notes text,

            -- Timestamps
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            sent_at datetime,
            viewed_at datetime,

            PRIMARY KEY (id),
            UNIQUE KEY contract_number (contract_number),
            UNIQUE KEY invoice_number (invoice_number),
            UNIQUE KEY access_token (access_token),
            KEY status (status),
            KEY performance_date (performance_date),
            KEY client_company_name (client_company_name),
            KEY email (email)
        ) {$charset_collate};";

        // Invoice line items table
        $table_line_items = $wpdb->prefix . 'smcb_invoice_line_items';
        $sql_line_items = "CREATE TABLE {$table_line_items} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contract_id bigint(20) unsigned NOT NULL,
            description varchar(500) NOT NULL,
            quantity decimal(10,2) NOT NULL DEFAULT 1.00,
            unit_price decimal(10,2) NOT NULL,
            sort_order int(3) NOT NULL DEFAULT 0,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY (id),
            KEY contract_id (contract_id),
            CONSTRAINT fk_line_items_contract FOREIGN KEY (contract_id) REFERENCES {$table_contracts}(id) ON DELETE CASCADE
        ) {$charset_collate};";

        // Activity log table
        $table_activity = $wpdb->prefix . 'smcb_contract_activity_log';
        $sql_activity = "CREATE TABLE {$table_activity} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contract_id bigint(20) unsigned NOT NULL,
            action varchar(50) NOT NULL,
            description text,
            user_id bigint(20) unsigned,
            ip_address varchar(45),
            user_agent text,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,

            PRIMARY KEY (id),
            KEY contract_id (contract_id),
            KEY action (action),
            KEY created_at (created_at),
            CONSTRAINT fk_activity_contract FOREIGN KEY (contract_id) REFERENCES {$table_contracts}(id) ON DELETE CASCADE
        ) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        dbDelta( $sql_contracts );
        dbDelta( $sql_line_items );
        dbDelta( $sql_activity );

        // Store database version
        update_option( 'smcb_db_version', SMCB_VERSION );
    }

    /**
     * Set default plugin options.
     */
    private static function set_default_options() {
        $default_options = array(
            'token_expiration_days'    => 30,
            'default_deposit_percent'  => 30,
            'early_loadin_hourly_rate' => 100,
            'default_set_length'       => 60,
            'default_break_length'     => 30,
            'default_number_of_sets'   => 3,
            'email_from_name'          => SMCB_COMPANY_NAME,
            'email_from_address'       => SMCB_COMPANY_EMAIL,
        );

        foreach ( $default_options as $key => $value ) {
            if ( get_option( 'smcb_' . $key ) === false ) {
                add_option( 'smcb_' . $key, $value );
            }
        }
    }

    /**
     * Create upload directory for PDFs.
     */
    private static function create_upload_directory() {
        $upload_dir = wp_upload_dir();
        $smcb_dir = $upload_dir['basedir'] . '/smcb-contracts';

        if ( ! file_exists( $smcb_dir ) ) {
            wp_mkdir_p( $smcb_dir );

            // Create .htaccess to protect the directory
            $htaccess_content = "Order Deny,Allow\nDeny from all\n<Files ~ \"\\.(pdf)$\">\n    Allow from all\n</Files>";
            file_put_contents( $smcb_dir . '/.htaccess', $htaccess_content );

            // Create index.php for security
            file_put_contents( $smcb_dir . '/index.php', '<?php // Silence is golden' );
        }

        // Create yearly subdirectories
        $year_dir = $smcb_dir . '/' . date( 'Y' );
        if ( ! file_exists( $year_dir ) ) {
            wp_mkdir_p( $year_dir );
            file_put_contents( $year_dir . '/index.php', '<?php // Silence is golden' );
        }
    }
}
