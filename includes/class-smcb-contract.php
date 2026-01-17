<?php
/**
 * Contract model class.
 *
 * Handles all contract-related database operations.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_Contract
 *
 * Contract model for database operations.
 */
class SMCB_Contract {

    /**
     * Database table name for contracts.
     *
     * @var string
     */
    private $table_name;

    /**
     * Database table name for line items.
     *
     * @var string
     */
    private $line_items_table;

    /**
     * Database table name for activity log.
     *
     * @var string
     */
    private $activity_table;

    /**
     * WordPress database instance.
     *
     * @var wpdb
     */
    private $wpdb;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . 'smcb_contracts';
        $this->line_items_table = $wpdb->prefix . 'smcb_invoice_line_items';
        $this->activity_table = $wpdb->prefix . 'smcb_contract_activity_log';
    }

    /**
     * Create a new contract.
     *
     * @param array $data Contract data.
     * @return int|false Contract ID on success, false on failure.
     */
    public function create( $data ) {
        // Generate contract and invoice numbers
        $data['contract_number'] = smcb_generate_contract_number();
        $data['invoice_number'] = smcb_generate_invoice_number();

        // Generate access token
        $token_manager = new SMCB_Token_Manager();
        $data['access_token'] = $token_manager->generate_token();
        $data['token_expires_at'] = $token_manager->get_expiration_date();

        // Set timestamps
        $data['created_at'] = current_time( 'mysql' );
        $data['updated_at'] = current_time( 'mysql' );

        // Extract line items before inserting contract
        $line_items = isset( $data['line_items'] ) ? $data['line_items'] : array();
        unset( $data['line_items'] );

        // Sanitize and prepare data
        $contract_data = $this->prepare_contract_data( $data );

        $result = $this->wpdb->insert( $this->table_name, $contract_data );

        if ( $result === false ) {
            return false;
        }

        $contract_id = $this->wpdb->insert_id;

        // Insert line items
        if ( ! empty( $line_items ) ) {
            $this->save_line_items( $contract_id, $line_items );
        }

        // Log activity
        $this->log_activity( $contract_id, 'created', 'Contract created' );

        return $contract_id;
    }

    /**
     * Update an existing contract.
     *
     * @param int   $id   Contract ID.
     * @param array $data Contract data.
     * @return bool True on success, false on failure.
     */
    public function update( $id, $data ) {
        // Extract line items before updating contract
        $line_items = isset( $data['line_items'] ) ? $data['line_items'] : null;
        unset( $data['line_items'] );

        // Set updated timestamp
        $data['updated_at'] = current_time( 'mysql' );

        // Sanitize and prepare data
        $contract_data = $this->prepare_contract_data( $data );

        $result = $this->wpdb->update(
            $this->table_name,
            $contract_data,
            array( 'id' => $id )
        );

        // Update line items if provided
        if ( $line_items !== null ) {
            $this->save_line_items( $id, $line_items );
        }

        // Log activity
        $this->log_activity( $id, 'updated', 'Contract updated' );

        return $result !== false;
    }

    /**
     * Get a contract by ID.
     *
     * @param int $id Contract ID.
     * @return object|null Contract object or null.
     */
    public function get( $id ) {
        $contract = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
                $id
            )
        );

        if ( $contract ) {
            $contract->line_items = $this->get_line_items( $id );
            $contract->calculated = $this->calculate_totals( $contract );
        }

        return $contract;
    }

    /**
     * Get a contract by access token.
     *
     * @param string $token Access token.
     * @return object|null Contract object or null.
     */
    public function get_by_token( $token ) {
        $contract = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE access_token = %s",
                $token
            )
        );

        if ( $contract ) {
            $contract->line_items = $this->get_line_items( $contract->id );
            $contract->calculated = $this->calculate_totals( $contract );
        }

        return $contract;
    }

    /**
     * Get a contract by contract number.
     *
     * @param string $contract_number Contract number.
     * @return object|null Contract object or null.
     */
    public function get_by_contract_number( $contract_number ) {
        $contract = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE contract_number = %s",
                $contract_number
            )
        );

        if ( $contract ) {
            $contract->line_items = $this->get_line_items( $contract->id );
            $contract->calculated = $this->calculate_totals( $contract );
        }

        return $contract;
    }

    /**
     * Delete a contract.
     *
     * @param int $id Contract ID.
     * @return bool True on success, false on failure.
     */
    public function delete( $id ) {
        // Get contract to delete associated files
        $contract = $this->get( $id );
        if ( $contract ) {
            $this->delete_contract_files( $contract );
        }

        // Line items and activity log will be deleted via CASCADE
        $result = $this->wpdb->delete(
            $this->table_name,
            array( 'id' => $id ),
            array( '%d' )
        );

        return $result !== false;
    }

    /**
     * Get contracts list with pagination and filtering.
     *
     * @param array $args Query arguments.
     * @return array Array with 'items' and 'total'.
     */
    public function get_list( $args = array() ) {
        $defaults = array(
            'per_page' => 20,
            'page'     => 1,
            'status'   => '',
            'search'   => '',
            'orderby'  => 'created_at',
            'order'    => 'DESC',
            'date_from' => '',
            'date_to'  => '',
        );

        $args = wp_parse_args( $args, $defaults );

        $where = array( '1=1' );
        $where_values = array();

        // Status filter
        if ( ! empty( $args['status'] ) ) {
            $where[] = 'status = %s';
            $where_values[] = $args['status'];
        }

        // Search filter
        if ( ! empty( $args['search'] ) ) {
            $where[] = '(client_company_name LIKE %s OR contact_person_name LIKE %s OR event_name LIKE %s OR contract_number LIKE %s OR email LIKE %s)';
            $search_term = '%' . $this->wpdb->esc_like( $args['search'] ) . '%';
            $where_values = array_merge( $where_values, array( $search_term, $search_term, $search_term, $search_term, $search_term ) );
        }

        // Date range filter
        if ( ! empty( $args['date_from'] ) ) {
            $where[] = 'performance_date >= %s';
            $where_values[] = $args['date_from'];
        }

        if ( ! empty( $args['date_to'] ) ) {
            $where[] = 'performance_date <= %s';
            $where_values[] = $args['date_to'];
        }

        $where_clause = implode( ' AND ', $where );

        // Sanitize orderby
        $allowed_orderby = array( 'created_at', 'performance_date', 'client_company_name', 'status', 'contract_number' );
        $orderby = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
        $order = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

        // Get total count
        $count_query = "SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}";
        if ( ! empty( $where_values ) ) {
            $count_query = $this->wpdb->prepare( $count_query, $where_values );
        }
        $total = (int) $this->wpdb->get_var( $count_query );

        // Get items
        $offset = ( $args['page'] - 1 ) * $args['per_page'];
        $items_query = "SELECT * FROM {$this->table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

        $query_values = array_merge( $where_values, array( $args['per_page'], $offset ) );
        $items = $this->wpdb->get_results( $this->wpdb->prepare( $items_query, $query_values ) );

        // Add calculated totals to each item
        foreach ( $items as $item ) {
            $item->line_items = $this->get_line_items( $item->id );
            $item->calculated = $this->calculate_totals( $item );
        }

        return array(
            'items' => $items,
            'total' => $total,
        );
    }

    /**
     * Update contract status.
     *
     * @param int    $id     Contract ID.
     * @param string $status New status.
     * @return bool True on success, false on failure.
     */
    public function update_status( $id, $status ) {
        $valid_statuses = array_keys( smcb_get_contract_statuses() );

        if ( ! in_array( $status, $valid_statuses, true ) ) {
            return false;
        }

        $update_data = array(
            'status'     => $status,
            'updated_at' => current_time( 'mysql' ),
        );

        // Set additional timestamps based on status
        switch ( $status ) {
            case 'sent':
                $update_data['sent_at'] = current_time( 'mysql' );
                break;
            case 'viewed':
                if ( ! $this->get( $id )->viewed_at ) {
                    $update_data['viewed_at'] = current_time( 'mysql' );
                }
                break;
        }

        $result = $this->wpdb->update(
            $this->table_name,
            $update_data,
            array( 'id' => $id )
        );

        $this->log_activity( $id, 'status_changed', "Status changed to {$status}" );

        return $result !== false;
    }

    /**
     * Record client signature.
     *
     * @param int    $id            Contract ID.
     * @param string $signature     Base64 signature image.
     * @param string $signed_name   Typed name of signer.
     * @param string $ip_address    Client IP address.
     * @return bool True on success, false on failure.
     */
    public function record_signature( $id, $signature, $signed_name, $ip_address ) {
        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'client_signature'   => $signature,
                'client_signed_at'   => current_time( 'mysql' ),
                'client_signed_ip'   => $ip_address,
                'client_signed_name' => $signed_name,
                'status'             => 'signed',
                'updated_at'         => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        $this->log_activity( $id, 'signed', "Contract signed by {$signed_name} from IP {$ip_address}" );

        return $result !== false;
    }

    /**
     * Mark contract as viewed.
     *
     * @param int    $id         Contract ID.
     * @param string $ip_address Client IP address.
     * @return bool True on success, false on failure.
     */
    public function mark_viewed( $id, $ip_address = '' ) {
        $contract = $this->get( $id );

        // Only update if not already viewed
        if ( $contract && empty( $contract->viewed_at ) && $contract->status === 'sent' ) {
            $this->wpdb->update(
                $this->table_name,
                array(
                    'viewed_at'  => current_time( 'mysql' ),
                    'status'     => 'viewed',
                    'updated_at' => current_time( 'mysql' ),
                ),
                array( 'id' => $id )
            );

            $this->log_activity( $id, 'viewed', "Contract viewed from IP {$ip_address}" );
        }

        return true;
    }

    /**
     * Save PDF path to contract.
     *
     * @param int    $id       Contract ID.
     * @param string $pdf_type Type of PDF (cover_letter, contract, invoice, signed_contract).
     * @param string $path     File path.
     * @return bool True on success, false on failure.
     */
    public function save_pdf_path( $id, $pdf_type, $path ) {
        $column_map = array(
            'cover_letter'    => 'cover_letter_pdf_path',
            'contract'        => 'contract_pdf_path',
            'invoice'         => 'invoice_pdf_path',
            'signed_contract' => 'signed_contract_pdf_path',
        );

        if ( ! isset( $column_map[ $pdf_type ] ) ) {
            return false;
        }

        return $this->wpdb->update(
            $this->table_name,
            array( $column_map[ $pdf_type ] => $path ),
            array( 'id' => $id )
        ) !== false;
    }

    /**
     * Regenerate access token.
     *
     * @param int $id Contract ID.
     * @return string|false New token or false on failure.
     */
    public function regenerate_token( $id ) {
        $token_manager = new SMCB_Token_Manager();
        $new_token = $token_manager->generate_token();
        $expires_at = $token_manager->get_expiration_date();

        $result = $this->wpdb->update(
            $this->table_name,
            array(
                'access_token'     => $new_token,
                'token_expires_at' => $expires_at,
                'updated_at'       => current_time( 'mysql' ),
            ),
            array( 'id' => $id )
        );

        if ( $result !== false ) {
            $this->log_activity( $id, 'token_regenerated', 'Access token regenerated' );
            return $new_token;
        }

        return false;
    }

    /**
     * Get statistics for dashboard.
     *
     * @return array Statistics array.
     */
    public function get_statistics() {
        $stats = array(
            'total'              => 0,
            'draft'              => 0,
            'sent'               => 0,
            'viewed'             => 0,
            'signed'             => 0,
            'cancelled'          => 0,
            'signed_this_month'  => 0,
            'upcoming_events'    => 0,
            'total_revenue'      => 0,
            'pending_revenue'    => 0,
        );

        // Count by status
        $status_counts = $this->wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$this->table_name} GROUP BY status"
        );

        foreach ( $status_counts as $row ) {
            $stats[ $row->status ] = (int) $row->count;
            $stats['total'] += (int) $row->count;
        }

        // Signed this month
        $stats['signed_this_month'] = (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE status = 'signed' AND client_signed_at >= %s",
                date( 'Y-m-01' )
            )
        );

        // Upcoming events (next 30 days)
        $stats['upcoming_events'] = (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE performance_date BETWEEN %s AND %s AND status IN ('sent', 'viewed', 'signed')",
                date( 'Y-m-d' ),
                date( 'Y-m-d', strtotime( '+30 days' ) )
            )
        );

        // Total revenue (signed contracts)
        $stats['total_revenue'] = (float) $this->wpdb->get_var(
            "SELECT COALESCE(SUM(base_compensation + mileage_travel_fee + (early_loadin_hours * " . SMCB_EARLY_LOADIN_RATE . ")), 0) FROM {$this->table_name} WHERE status = 'signed'"
        );

        // Pending revenue (sent/viewed contracts)
        $stats['pending_revenue'] = (float) $this->wpdb->get_var(
            "SELECT COALESCE(SUM(base_compensation + mileage_travel_fee + (early_loadin_hours * " . SMCB_EARLY_LOADIN_RATE . ")), 0) FROM {$this->table_name} WHERE status IN ('sent', 'viewed')"
        );

        return $stats;
    }

    /**
     * Get activity log for a contract.
     *
     * @param int $contract_id Contract ID.
     * @param int $limit       Number of records to return.
     * @return array Activity log entries.
     */
    public function get_activity_log( $contract_id, $limit = 50 ) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->activity_table} WHERE contract_id = %d ORDER BY created_at DESC LIMIT %d",
                $contract_id,
                $limit
            )
        );
    }

    /**
     * Save invoice line items.
     *
     * @param int   $contract_id Contract ID.
     * @param array $line_items  Array of line items.
     */
    private function save_line_items( $contract_id, $line_items ) {
        // Delete existing line items
        $this->wpdb->delete(
            $this->line_items_table,
            array( 'contract_id' => $contract_id ),
            array( '%d' )
        );

        // Insert new line items
        $sort_order = 0;
        foreach ( $line_items as $item ) {
            if ( empty( $item['description'] ) || ! isset( $item['unit_price'] ) ) {
                continue;
            }

            $this->wpdb->insert(
                $this->line_items_table,
                array(
                    'contract_id' => $contract_id,
                    'description' => sanitize_text_field( $item['description'] ),
                    'quantity'    => floatval( $item['quantity'] ?? 1 ),
                    'unit_price'  => floatval( $item['unit_price'] ),
                    'sort_order'  => $sort_order++,
                )
            );
        }
    }

    /**
     * Get line items for a contract.
     *
     * @param int $contract_id Contract ID.
     * @return array Line items.
     */
    private function get_line_items( $contract_id ) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->line_items_table} WHERE contract_id = %d ORDER BY sort_order ASC",
                $contract_id
            )
        );
    }

    /**
     * Calculate totals for a contract.
     *
     * @param object $contract Contract object.
     * @return object Calculated values.
     */
    private function calculate_totals( $contract ) {
        $calculated = new stdClass();

        // Early load-in fee
        $calculated->early_loadin_fee = $contract->early_loadin_required
            ? floatval( $contract->early_loadin_hours ) * SMCB_EARLY_LOADIN_RATE
            : 0;

        // Invoice line items total
        $calculated->line_items_total = 0;
        if ( ! empty( $contract->line_items ) ) {
            foreach ( $contract->line_items as $item ) {
                $calculated->line_items_total += floatval( $item->quantity ) * floatval( $item->unit_price );
            }
        }

        // Total compensation
        $calculated->total_compensation = floatval( $contract->base_compensation )
            + floatval( $contract->mileage_travel_fee )
            + $calculated->early_loadin_fee;

        // Deposit amount
        $calculated->deposit_amount = $calculated->total_compensation * ( floatval( $contract->deposit_percentage ) / 100 );

        // Balance due
        $calculated->balance_due = $calculated->total_compensation - $calculated->deposit_amount;

        // Set times
        $calculated->set_times = smcb_calculate_set_times(
            $contract->first_set_start_time,
            $contract->number_of_sets,
            $contract->set_length,
            $contract->break_length
        );

        // Performance end time
        if ( ! empty( $calculated->set_times ) ) {
            $last_set = end( $calculated->set_times );
            $calculated->performance_end_time = $last_set['end'];
        }

        return $calculated;
    }

    /**
     * Log activity for a contract.
     *
     * @param int    $contract_id Contract ID.
     * @param string $action      Action type.
     * @param string $description Action description.
     */
    private function log_activity( $contract_id, $action, $description = '' ) {
        $this->wpdb->insert(
            $this->activity_table,
            array(
                'contract_id' => $contract_id,
                'action'      => $action,
                'description' => $description,
                'user_id'     => get_current_user_id(),
                'ip_address'  => $this->get_client_ip(),
                'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
                'created_at'  => current_time( 'mysql' ),
            )
        );
    }

    /**
     * Prepare contract data for database insertion.
     *
     * @param array $data Raw contract data.
     * @return array Sanitized contract data.
     */
    private function prepare_contract_data( $data ) {
        $prepared = array();

        // Text fields
        $text_fields = array(
            'contract_number', 'invoice_number', 'status', 'client_company_name',
            'contact_person_name', 'street_address', 'city', 'state', 'zip_code',
            'phone', 'email', 'event_name', 'venue_name', 'venue_address',
            'venue_city', 'venue_state', 'venue_zip', 'venue_contact_person',
            'venue_phone', 'venue_email', 'inside_outside', 'stage_available',
            'power_requirements', 'loadin_location', 'performance_location',
            'sound_system', 'lights', 'music_between_sets',
            'outside_production_notes', 'preferred_genre', 'accommodations_provided',
            'additional_compensation', 'services_description', 'attire', 'audience_rating',
            'cover_letter_message', 'additional_contract_notes', 'access_token',
            'client_signature', 'client_signed_ip', 'client_signed_name',
            'performer_signature', 'cover_letter_pdf_path', 'contract_pdf_path',
            'invoice_pdf_path', 'signed_contract_pdf_path',
        );

        foreach ( $text_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        // Textarea fields (allow more content)
        $textarea_fields = array(
            'outside_production_notes', 'additional_compensation', 'services_description',
            'cover_letter_message', 'additional_contract_notes', 'client_signature',
            'performer_signature', 'loadin_location', 'performance_location',
        );

        foreach ( $textarea_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = sanitize_textarea_field( $data[ $field ] );
            }
        }

        // Email fields
        if ( isset( $data['email'] ) ) {
            $prepared['email'] = sanitize_email( $data['email'] );
        }
        if ( isset( $data['venue_email'] ) ) {
            $prepared['venue_email'] = sanitize_email( $data['venue_email'] );
        }

        // Date fields
        $date_fields = array( 'performance_date', 'token_expires_at', 'created_at', 'updated_at', 'sent_at', 'viewed_at', 'client_signed_at', 'performer_signed_at' );
        foreach ( $date_fields as $field ) {
            if ( isset( $data[ $field ] ) && ! empty( $data[ $field ] ) ) {
                $prepared[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        // Time fields
        $time_fields = array( 'first_set_start_time', 'load_in_time' );
        foreach ( $time_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = sanitize_text_field( $data[ $field ] );
            }
        }

        // Integer fields
        $int_fields = array( 'number_of_sets', 'set_length', 'break_length', 'early_loadin_hours', 'deposit_percentage' );
        foreach ( $int_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = intval( $data[ $field ] );
            }
        }

        // Boolean fields
        $bool_fields = array( 'early_loadin_required', 'outside_production' );
        foreach ( $bool_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = $data[ $field ] ? 1 : 0;
            }
        }

        // Decimal fields
        $decimal_fields = array( 'accommodation_cost_offset', 'mileage_travel_fee', 'base_compensation' );
        foreach ( $decimal_fields as $field ) {
            if ( isset( $data[ $field ] ) ) {
                $prepared[ $field ] = floatval( $data[ $field ] );
            }
        }

        return $prepared;
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
                // Handle comma-separated IPs (X-Forwarded-For)
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

    /**
     * Delete contract PDF files.
     *
     * @param object $contract Contract object.
     */
    private function delete_contract_files( $contract ) {
        $pdf_fields = array( 'cover_letter_pdf_path', 'contract_pdf_path', 'invoice_pdf_path', 'signed_contract_pdf_path' );

        foreach ( $pdf_fields as $field ) {
            if ( ! empty( $contract->$field ) && file_exists( $contract->$field ) ) {
                wp_delete_file( $contract->$field );
            }
        }
    }
}
