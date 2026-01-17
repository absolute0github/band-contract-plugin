<?php
/**
 * PDF Generator class.
 *
 * Generates contract, invoice, and cover letter PDFs using TCPDF.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Class SMCB_PDF_Generator
 *
 * Handles PDF generation for contracts.
 */
class SMCB_PDF_Generator {

    /**
     * Contract object.
     *
     * @var object
     */
    private $contract;

    /**
     * TCPDF instance.
     *
     * @var TCPDF
     */
    private $pdf;

    /**
     * Brand colors.
     *
     * @var array
     */
    private $colors = array(
        'primary'   => array( 196, 18, 48 ),    // #c41230 - Red
        'secondary' => array( 26, 26, 26 ),      // #1a1a1a - Dark
        'text'      => array( 51, 51, 51 ),      // #333333
        'light'     => array( 245, 245, 245 ),   // #f5f5f5
        'border'    => array( 200, 200, 200 ),   // #c8c8c8
    );

    /**
     * Constructor.
     *
     * @param object $contract Contract object.
     */
    public function __construct( $contract ) {
        $this->contract = $contract;

        // Ensure TCPDF is loaded
        if ( ! class_exists( 'TCPDF' ) ) {
            $this->load_tcpdf();
        }
    }

    /**
     * Load TCPDF library.
     */
    private function load_tcpdf() {
        // Check for TCPDF in common locations
        $tcpdf_paths = array(
            SMCB_PLUGIN_DIR . 'vendor/tecnickcom/tcpdf/tcpdf.php',
            SMCB_PLUGIN_DIR . 'lib/tcpdf/tcpdf.php',
            ABSPATH . 'wp-content/plugins/tcpdf/tcpdf.php',
            WP_CONTENT_DIR . '/tcpdf/tcpdf.php',
        );

        foreach ( $tcpdf_paths as $path ) {
            if ( file_exists( $path ) ) {
                require_once $path;
                return;
            }
        }

        // Try to use composer autoload if available
        $autoload = SMCB_PLUGIN_DIR . 'vendor/autoload.php';
        if ( file_exists( $autoload ) ) {
            require_once $autoload;
        }
    }

    /**
     * Initialize a new PDF document.
     *
     * @param string $orientation Page orientation (P or L).
     */
    private function init_pdf( $orientation = 'P' ) {
        $this->pdf = new TCPDF( $orientation, 'mm', 'LETTER', true, 'UTF-8', false );

        // Set document information
        $this->pdf->SetCreator( SMCB_COMPANY_NAME );
        $this->pdf->SetAuthor( SMCB_COMPANY_NAME );
        $this->pdf->SetTitle( 'Contract - ' . $this->contract->contract_number );
        $this->pdf->SetSubject( 'Performance Agreement' );

        // Remove default header/footer
        $this->pdf->setPrintHeader( false );
        $this->pdf->setPrintFooter( false );

        // Set margins
        $this->pdf->SetMargins( 20, 20, 20 );
        $this->pdf->SetAutoPageBreak( true, 25 );

        // Set font
        $this->pdf->SetFont( 'helvetica', '', 11 );
    }

    /**
     * Generate all PDFs for a contract.
     *
     * @param bool $signed Whether to generate signed versions.
     * @return array Array of generated file paths.
     */
    public function generate_all( $signed = false ) {
        $paths = array();

        // Generate cover letter
        $paths['cover_letter'] = $this->generate_cover_letter();

        // Generate contract
        $paths['contract'] = $signed
            ? $this->generate_signed_contract()
            : $this->generate_contract();

        // Generate invoice
        $paths['invoice'] = $this->generate_invoice();

        // Save paths to contract
        $contract_model = new SMCB_Contract();
        foreach ( $paths as $type => $path ) {
            if ( $path ) {
                $contract_model->save_pdf_path( $this->contract->id, $type, $path );
            }
        }

        return $paths;
    }

    /**
     * Generate cover letter PDF.
     *
     * @return string|false File path or false on failure.
     */
    public function generate_cover_letter() {
        $this->init_pdf();
        $this->pdf->AddPage();

        // Add logo
        $this->add_logo();

        // Add date
        $this->pdf->Ln( 10 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, date( 'F j, Y' ), 0, 1 );
        $this->pdf->Ln( 5 );

        // Recipient info
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 0, 6, $this->contract->contact_person_name, 0, 1 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, $this->contract->client_company_name, 0, 1 );
        $this->pdf->Cell( 0, 6, $this->contract->street_address, 0, 1 );
        $this->pdf->Cell( 0, 6, $this->contract->city . ', ' . $this->contract->state . ' ' . $this->contract->zip_code, 0, 1 );
        $this->pdf->Ln( 10 );

        // Greeting
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, 'Dear ' . $this->contract->contact_person_name . ',', 0, 1 );
        $this->pdf->Ln( 5 );

        // Custom message or default
        $message = ! empty( $this->contract->cover_letter_message )
            ? $this->contract->cover_letter_message
            : $this->get_default_cover_letter_message();

        $this->pdf->MultiCell( 0, 6, $message, 0, 'L' );
        $this->pdf->Ln( 10 );

        // Event details summary
        $this->add_event_summary_box();

        // Closing
        $this->pdf->Ln( 10 );
        $this->pdf->MultiCell( 0, 6, 'Please review the attached Performance Agreement and Invoice. If everything looks correct, please sign the agreement and return it to us.', 0, 'L' );
        $this->pdf->Ln( 5 );
        $this->pdf->MultiCell( 0, 6, 'We look forward to performing for you and making your event a success!', 0, 'L' );
        $this->pdf->Ln( 10 );

        // Signature
        $this->pdf->Cell( 0, 6, 'Warm regards,', 0, 1 );
        $this->pdf->Ln( 15 );
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 0, 6, 'Skinny Moo', 0, 1 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, SMCB_COMPANY_NAME, 0, 1 );
        $this->pdf->Cell( 0, 6, SMCB_COMPANY_PHONE, 0, 1 );
        $this->pdf->Cell( 0, 6, SMCB_COMPANY_EMAIL, 0, 1 );

        return $this->save_pdf( 'cover-letter' );
    }

    /**
     * Generate contract PDF.
     *
     * @return string|false File path or false on failure.
     */
    public function generate_contract() {
        $this->init_pdf();

        // Page 1 - Header and parties
        $this->pdf->AddPage();
        $this->add_contract_header();
        $this->add_parties_section();
        $this->add_performance_details_section();
        $this->add_compensation_section();

        // Page 2 - Terms and signatures
        $this->pdf->AddPage();
        $this->add_terms_section();
        $this->add_signature_section();

        return $this->save_pdf( 'contract' );
    }

    /**
     * Generate signed contract PDF.
     *
     * @return string|false File path or false on failure.
     */
    public function generate_signed_contract() {
        $this->init_pdf();

        // Page 1 - Header and parties
        $this->pdf->AddPage();
        $this->add_contract_header();
        $this->add_parties_section();
        $this->add_performance_details_section();
        $this->add_compensation_section();

        // Page 2 - Terms and signatures with actual signature
        $this->pdf->AddPage();
        $this->add_terms_section();
        $this->add_signature_section( true );

        return $this->save_pdf( 'signed-contract' );
    }

    /**
     * Generate invoice PDF.
     *
     * @return string|false File path or false on failure.
     */
    public function generate_invoice() {
        $this->init_pdf();
        $this->pdf->AddPage();

        // Header - use compact logo
        $this->add_logo( false );

        // Invoice title and number - side by side layout
        $this->pdf->SetFont( 'helvetica', 'B', 20 );
        $this->pdf->SetTextColor( $this->colors['primary'][0], $this->colors['primary'][1], $this->colors['primary'][2] );
        $this->pdf->Cell( 90, 8, 'INVOICE', 0, 0 );
        $this->pdf->SetTextColor( $this->colors['text'][0], $this->colors['text'][1], $this->colors['text'][2] );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( 0, 8, 'Invoice #: ' . $this->contract->invoice_number . '  |  Contract #: ' . $this->contract->contract_number . '  |  Date: ' . smcb_format_date( $this->contract->created_at ), 0, 1, 'R' );
        $this->pdf->Ln( 5 );

        // Bill To / From - more compact
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 90, 5, 'BILL TO:', 0, 0 );
        $this->pdf->Cell( 0, 5, 'FROM:', 0, 1 );

        $this->pdf->SetFont( 'helvetica', '', 9 );

        // Left column - Client
        $y_start = $this->pdf->GetY();
        $this->pdf->MultiCell( 90, 5, $this->contract->client_company_name . "\n" . $this->contract->contact_person_name . "\n" . $this->contract->street_address . "\n" . $this->contract->city . ', ' . $this->contract->state . ' ' . $this->contract->zip_code . "\n" . $this->contract->email, 0, 'L' );

        // Right column - Company
        $this->pdf->SetXY( 110, $y_start );
        $this->pdf->MultiCell( 0, 5, SMCB_COMPANY_NAME . "\n" . SMCB_COMPANY_ADDRESS . "\n" . SMCB_COMPANY_CITY . ', ' . SMCB_COMPANY_STATE . ' ' . SMCB_COMPANY_ZIP . "\n" . SMCB_COMPANY_EMAIL . "\nEIN: " . SMCB_COMPANY_EIN, 0, 'L' );

        $this->pdf->Ln( 5 );

        // Event Details - inline format
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 25, 6, 'Event:', 0, 0, 'L', true );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( 65, 6, $this->contract->event_name, 0, 0, 'L', true );
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 15, 6, 'Date:', 0, 0, 'L', true );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( 35, 6, smcb_format_date( $this->contract->performance_date ), 0, 0, 'L', true );
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 15, 6, 'Time:', 0, 0, 'L', true );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( 0, 6, smcb_format_time( $this->contract->first_set_start_time ), 0, 1, 'L', true );
        $this->pdf->Ln( 5 );

        // Line items table
        $this->add_invoice_table();

        // Payment terms and methods - side by side
        $this->pdf->Ln( 5 );
        $y_start = $this->pdf->GetY();

        // Left column - Payment terms
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 90, 5, 'PAYMENT TERMS', 0, 1 );
        $this->pdf->SetFont( 'helvetica', '', 9 );
        $this->pdf->MultiCell( 90, 4, 'A deposit of ' . smcb_format_currency( $this->contract->calculated->deposit_amount ) . ' (' . $this->contract->deposit_percentage . '%) is due upon signing. Balance of ' . smcb_format_currency( $this->contract->calculated->balance_due ) . ' due day of performance.', 0, 'L' );

        // Right column - Payment methods
        $this->pdf->SetXY( 110, $y_start );
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->Cell( 0, 5, 'PAYMENT METHODS', 0, 1 );
        $this->pdf->SetX( 110 );
        $this->pdf->SetFont( 'helvetica', '', 9 );
        $this->pdf->MultiCell( 0, 4, "Checks payable to: " . SMCB_COMPANY_NAME . "\nVenmo: @skinnymoo  |  PayPal: " . SMCB_COMPANY_EMAIL, 0, 'L' );

        return $this->save_pdf( 'invoice' );
    }

    /**
     * Add logo to PDF (full width, centered with address below).
     *
     * @param bool $with_address Whether to include address below logo.
     */
    private function add_logo( $with_address = true ) {
        $logo_path = SMCB_PLUGIN_DIR . 'assets/images/logo.png';
        $page_width = $this->pdf->getPageWidth();
        $margins = $this->pdf->getMargins();
        $content_width = $page_width - $margins['left'] - $margins['right'];

        if ( file_exists( $logo_path ) ) {
            // Center the logo at full content width
            $this->pdf->Image( $logo_path, $margins['left'], 15, $content_width );
            $this->pdf->Ln( 35 );
        } else {
            // Text fallback
            $this->pdf->SetFont( 'helvetica', 'B', 24 );
            $this->pdf->SetTextColor( $this->colors['primary'][0], $this->colors['primary'][1], $this->colors['primary'][2] );
            $this->pdf->Cell( 0, 15, 'SKINNY MOO', 0, 1, 'C' );
            $this->pdf->SetTextColor( $this->colors['text'][0], $this->colors['text'][1], $this->colors['text'][2] );
        }

        // Add centered address below logo
        if ( $with_address ) {
            $this->pdf->SetFont( 'helvetica', '', 10 );
            $this->pdf->Cell( 0, 5, SMCB_COMPANY_ADDRESS . ', ' . SMCB_COMPANY_CITY . ', ' . SMCB_COMPANY_STATE . ' ' . SMCB_COMPANY_ZIP, 0, 1, 'C' );
            $this->pdf->Cell( 0, 5, SMCB_COMPANY_PHONE . ' | ' . SMCB_COMPANY_EMAIL . ' | ' . SMCB_COMPANY_WEBSITE, 0, 1, 'C' );
            $this->pdf->Ln( 5 );
        }
    }

    /**
     * Add contract header.
     */
    private function add_contract_header() {
        $this->add_logo( true );

        $this->pdf->SetFont( 'helvetica', 'B', 20 );
        $this->pdf->SetTextColor( $this->colors['secondary'][0], $this->colors['secondary'][1], $this->colors['secondary'][2] );
        $this->pdf->Cell( 0, 10, 'PERFORMANCE AGREEMENT', 0, 1, 'C' );
        $this->pdf->SetTextColor( $this->colors['text'][0], $this->colors['text'][1], $this->colors['text'][2] );

        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, 'Contract Number: ' . $this->contract->contract_number, 0, 1, 'C' );
        $this->pdf->Ln( 5 );
    }

    /**
     * Add parties section.
     */
    private function add_parties_section() {
        $this->pdf->SetFont( 'helvetica', 'B', 12 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 0, 8, 'PARTIES', 0, 1, 'L', true );
        $this->pdf->Ln( 3 );

        $this->pdf->SetFont( 'helvetica', '', 11 );

        // Performer (simplified since address is in header)
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 25, 6, 'PERFORMER:', 0, 0 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, SMCB_COMPANY_NAME . ' ("Skinny Moo")', 0, 1 );
        $this->pdf->Ln( 3 );

        // Client
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 25, 6, 'CLIENT:', 0, 0 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, $this->contract->client_company_name, 0, 1 );
        $this->pdf->Cell( 25, 6, '', 0, 0 );
        $this->pdf->Cell( 0, 6, $this->contract->contact_person_name, 0, 1 );
        $this->pdf->Cell( 25, 6, '', 0, 0 );
        $this->pdf->Cell( 0, 6, $this->contract->street_address . ', ' . $this->contract->city . ', ' . $this->contract->state . ' ' . $this->contract->zip_code, 0, 1 );
        $this->pdf->Cell( 25, 6, '', 0, 0 );
        $this->pdf->Cell( 0, 6, $this->contract->phone . ' | ' . $this->contract->email, 0, 1 );
        $this->pdf->Ln( 8 );
    }

    /**
     * Add performance details section.
     */
    private function add_performance_details_section() {
        $this->pdf->SetFont( 'helvetica', 'B', 12 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 0, 8, 'PERFORMANCE DETAILS', 0, 1, 'L', true );
        $this->pdf->Ln( 3 );

        $this->pdf->SetFont( 'helvetica', '', 11 );

        // Event details
        $this->add_detail_row( 'Event', $this->contract->event_name );
        $this->add_detail_row( 'Date', smcb_format_date( $this->contract->performance_date ) );
        $this->add_detail_row( 'Load-in Time', smcb_format_time( $this->contract->load_in_time ) );
        $this->add_detail_row( 'Performance Start', smcb_format_time( $this->contract->first_set_start_time ) );
        $this->add_detail_row( 'Number of Sets', $this->contract->number_of_sets );
        $this->add_detail_row( 'Set Length', $this->contract->set_length . ' minutes' );
        $this->add_detail_row( 'Break Length', $this->contract->break_length . ' minutes' );

        // Set schedule in equal boxes
        $this->pdf->Ln( 5 );
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 0, 6, 'Set Schedule:', 0, 1 );
        $this->pdf->Ln( 2 );

        // Calculate box width based on number of sets
        $num_sets = count( $this->contract->calculated->set_times );
        $page_width = $this->pdf->getPageWidth();
        $margins = $this->pdf->getMargins();
        $content_width = $page_width - $margins['left'] - $margins['right'];
        $box_width = ( $content_width - ( ( $num_sets - 1 ) * 3 ) ) / $num_sets; // 3mm gap between boxes
        $box_height = 18;

        $this->pdf->SetDrawColor( $this->colors['primary'][0], $this->colors['primary'][1], $this->colors['primary'][2] );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );

        $x_start = $margins['left'];
        $y_start = $this->pdf->GetY();

        foreach ( $this->contract->calculated->set_times as $index => $set ) {
            $x_pos = $x_start + ( $index * ( $box_width + 3 ) );

            // Draw box with border
            $this->pdf->Rect( $x_pos, $y_start, $box_width, $box_height, 'DF' );

            // Set title
            $this->pdf->SetXY( $x_pos, $y_start + 2 );
            $this->pdf->SetFont( 'helvetica', 'B', 10 );
            $this->pdf->Cell( $box_width, 5, 'Set ' . $set['set_number'], 0, 0, 'C' );

            // Set times
            $this->pdf->SetXY( $x_pos, $y_start + 8 );
            $this->pdf->SetFont( 'helvetica', '', 9 );
            $this->pdf->Cell( $box_width, 5, $set['start'] . ' - ' . $set['end'], 0, 0, 'C' );
        }

        $this->pdf->SetY( $y_start + $box_height + 5 );
        $this->pdf->SetDrawColor( $this->colors['border'][0], $this->colors['border'][1], $this->colors['border'][2] );

        // Venue details - check for page break before this section
        $this->check_page_break( 60 ); // Estimate 60mm for venue section
        $this->pdf->Ln( 3 );
        if ( ! empty( $this->contract->venue_name ) ) {
            $this->add_detail_row( 'Venue', $this->contract->venue_name );
        }
        if ( ! empty( $this->contract->venue_address ) ) {
            $venue_address = $this->contract->venue_address;
            if ( ! empty( $this->contract->venue_city ) ) {
                $venue_address .= ', ' . $this->contract->venue_city . ', ' . $this->contract->venue_state . ' ' . $this->contract->venue_zip;
            }
            $this->add_detail_row( 'Venue Address', $venue_address );
        }
        if ( ! empty( $this->contract->venue_contact_person ) ) {
            $this->add_detail_row( 'Venue Contact', $this->contract->venue_contact_person );
        }
        if ( ! empty( $this->contract->venue_phone ) ) {
            $this->add_detail_row( 'Venue Phone', $this->contract->venue_phone );
        }
        $this->add_detail_row( 'Location', ucfirst( $this->contract->inside_outside ) );
        $this->add_detail_row( 'Stage Available', ucfirst( $this->contract->stage_available ) );
        if ( ! empty( $this->contract->power_requirements ) ) {
            $this->add_detail_row( 'Power Requirements', $this->contract->power_requirements );
        }

        // Check page break before load-in location to keep it together
        if ( ! empty( $this->contract->loadin_location ) || ! empty( $this->contract->performance_location ) ) {
            $loadin_height = 0;
            if ( ! empty( $this->contract->loadin_location ) ) {
                $loadin_height += 15 + ( substr_count( $this->contract->loadin_location, "\n" ) * 6 );
            }
            if ( ! empty( $this->contract->performance_location ) ) {
                $loadin_height += 15 + ( substr_count( $this->contract->performance_location, "\n" ) * 6 );
            }
            $this->check_page_break( $loadin_height );
        }

        if ( ! empty( $this->contract->loadin_location ) ) {
            $this->pdf->Ln( 2 );
            $this->pdf->SetFont( 'helvetica', 'B', 11 );
            $this->pdf->Cell( 0, 6, 'Load-in Location:', 0, 1 );
            $this->pdf->SetFont( 'helvetica', '', 11 );
            $this->pdf->MultiCell( 0, 6, $this->contract->loadin_location, 0, 'L' );
        }
        if ( ! empty( $this->contract->performance_location ) ) {
            $this->pdf->Ln( 2 );
            $this->pdf->SetFont( 'helvetica', 'B', 11 );
            $this->pdf->Cell( 0, 6, 'Performance Location:', 0, 1 );
            $this->pdf->SetFont( 'helvetica', '', 11 );
            $this->pdf->MultiCell( 0, 6, $this->contract->performance_location, 0, 'L' );
        }

        // Production
        $this->pdf->Ln( 3 );
        $production_options = smcb_get_production_options();
        $this->add_detail_row( 'Sound System', $production_options[ $this->contract->sound_system ] ?? $this->contract->sound_system );
        $this->add_detail_row( 'Lights', $production_options[ $this->contract->lights ] ?? $this->contract->lights );
        $this->add_detail_row( 'Music Between Sets', $production_options[ $this->contract->music_between_sets ] ?? $this->contract->music_between_sets );

        // Services provided
        if ( ! empty( $this->contract->services_description ) ) {
            $this->pdf->Ln( 3 );
            $this->pdf->SetFont( 'helvetica', 'B', 11 );
            $this->pdf->Cell( 40, 6, 'Services:', 0, 0 );
            $this->pdf->SetFont( 'helvetica', '', 11 );
            $this->pdf->MultiCell( 0, 6, $this->contract->services_description, 0, 'L' );
        }

        // Attire and rating
        if ( ! empty( $this->contract->attire ) ) {
            $this->add_detail_row( 'Attire', $this->contract->attire );
        }
        $this->add_detail_row( 'Audience Rating', strtoupper( $this->contract->audience_rating ) );

        $this->pdf->Ln( 10 );
    }

    /**
     * Add compensation section.
     */
    private function add_compensation_section() {
        $this->pdf->SetFont( 'helvetica', 'B', 12 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 0, 8, 'COMPENSATION', 0, 1, 'L', true );
        $this->pdf->Ln( 3 );

        $this->pdf->SetFont( 'helvetica', '', 11 );

        $this->add_detail_row( 'Base Compensation', smcb_format_currency( $this->contract->base_compensation ) );

        if ( $this->contract->mileage_travel_fee > 0 ) {
            $this->add_detail_row( 'Travel Fee', smcb_format_currency( $this->contract->mileage_travel_fee ) );
        }

        if ( $this->contract->early_loadin_required && $this->contract->early_loadin_hours > 0 ) {
            $this->add_detail_row( 'Early Load-in Fee', smcb_format_currency( $this->contract->calculated->early_loadin_fee ) . ' (' . $this->contract->early_loadin_hours . ' hours @ $' . SMCB_EARLY_LOADIN_RATE . '/hr)' );
        }

        $this->pdf->Ln( 3 );
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->add_detail_row( 'TOTAL', smcb_format_currency( $this->contract->calculated->total_compensation ) );
        $this->pdf->SetFont( 'helvetica', '', 11 );

        $this->pdf->Ln( 3 );
        $this->add_detail_row( 'Deposit Required (' . $this->contract->deposit_percentage . '%)', smcb_format_currency( $this->contract->calculated->deposit_amount ) );
        $this->add_detail_row( 'Balance Due at Event', smcb_format_currency( $this->contract->calculated->balance_due ) );

        if ( ! empty( $this->contract->additional_compensation ) ) {
            $this->pdf->Ln( 3 );
            $this->pdf->SetFont( 'helvetica', 'B', 11 );
            $this->pdf->Cell( 40, 6, 'Additional:', 0, 0 );
            $this->pdf->SetFont( 'helvetica', '', 11 );
            $this->pdf->MultiCell( 0, 6, $this->contract->additional_compensation, 0, 'L' );
        }
    }

    /**
     * Add terms and conditions section.
     */
    private function add_terms_section() {
        $this->pdf->SetFont( 'helvetica', 'B', 12 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 0, 8, 'TERMS AND CONDITIONS', 0, 1, 'L', true );
        $this->pdf->Ln( 3 );

        $this->pdf->SetFont( 'helvetica', '', 10 );

        $terms = $this->get_contract_terms();
        foreach ( $terms as $index => $term ) {
            $this->pdf->SetFont( 'helvetica', 'B', 10 );
            $this->pdf->Cell( 8, 5, ( $index + 1 ) . '.', 0, 0 );
            $this->pdf->SetFont( 'helvetica', '', 10 );
            $this->pdf->MultiCell( 0, 5, $term, 0, 'L' );
            $this->pdf->Ln( 1 );
        }

        // Additional notes
        if ( ! empty( $this->contract->additional_contract_notes ) ) {
            $this->pdf->Ln( 5 );
            $this->pdf->SetFont( 'helvetica', 'B', 11 );
            $this->pdf->Cell( 0, 6, 'ADDITIONAL NOTES', 0, 1 );
            $this->pdf->SetFont( 'helvetica', '', 10 );
            $this->pdf->MultiCell( 0, 5, $this->contract->additional_contract_notes, 0, 'L' );
        }

        $this->pdf->Ln( 10 );
    }

    /**
     * Add signature section.
     *
     * @param bool $signed Whether to include actual signatures.
     */
    private function add_signature_section( $signed = false ) {
        $this->pdf->SetFont( 'helvetica', 'B', 12 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 0, 8, 'SIGNATURES', 0, 1, 'L', true );
        $this->pdf->Ln( 5 );

        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->MultiCell( 0, 5, 'By signing below, both parties agree to the terms and conditions set forth in this Performance Agreement.', 0, 'L' );
        $this->pdf->Ln( 10 );

        // Two-column layout for signatures in boxes
        $page_width = $this->pdf->getPageWidth();
        $margins = $this->pdf->getMargins();
        $content_width = $page_width - $margins['left'] - $margins['right'];
        $box_width = ( $content_width - 10 ) / 2; // 10mm gap between boxes
        $box_height = 70;

        $y_start = $this->pdf->GetY();
        $left_x = $margins['left'];
        $right_x = $margins['left'] + $box_width + 10;

        $this->pdf->SetDrawColor( $this->colors['border'][0], $this->colors['border'][1], $this->colors['border'][2] );

        // ===== CLIENT SIGNATURE BOX (LEFT) =====
        $this->pdf->Rect( $left_x, $y_start, $box_width, $box_height, 'D' );

        // Client header
        $this->pdf->SetXY( $left_x + 3, $y_start + 3 );
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( $box_width - 6, 6, 'CLIENT', 0, 1 );
        $this->pdf->SetX( $left_x + 3 );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( $box_width - 6, 5, $this->contract->client_company_name, 0, 1 );

        // Client signature area
        if ( $signed && ! empty( $this->contract->client_signature ) ) {
            // Draw client signature image
            $sig_data = $this->contract->client_signature;
            if ( strpos( $sig_data, 'data:image' ) === 0 ) {
                $sig_data = substr( $sig_data, strpos( $sig_data, ',' ) + 1 );
            }
            $sig_image = base64_decode( $sig_data );
            $this->pdf->Image( '@' . $sig_image, $left_x + 5, $y_start + 18, 50, 18 );
        }

        // Signature line
        $sig_line_y = $y_start + 40;
        $this->pdf->Line( $left_x + 5, $sig_line_y, $left_x + $box_width - 5, $sig_line_y );
        $this->pdf->SetXY( $left_x + 3, $sig_line_y + 1 );
        $this->pdf->SetFont( 'helvetica', '', 8 );
        $this->pdf->Cell( $box_width - 6, 4, 'Signature', 0, 1 );

        if ( $signed && ! empty( $this->contract->client_signed_name ) ) {
            $this->pdf->SetXY( $left_x + 3, $sig_line_y + 6 );
            $this->pdf->SetFont( 'helvetica', '', 9 );
            $this->pdf->Cell( $box_width - 6, 5, 'Signed by: ' . $this->contract->client_signed_name, 0, 1 );
            $this->pdf->SetX( $left_x + 3 );
            $this->pdf->Cell( $box_width - 6, 5, 'Date: ' . smcb_format_date( $this->contract->client_signed_at ), 0, 1 );
        } else {
            // Print name line
            $name_line_y = $sig_line_y + 12;
            $this->pdf->Line( $left_x + 5, $name_line_y, $left_x + $box_width - 5, $name_line_y );
            $this->pdf->SetXY( $left_x + 3, $name_line_y + 1 );
            $this->pdf->Cell( $box_width - 6, 4, 'Print Name', 0, 1 );

            // Date line
            $date_line_y = $name_line_y + 12;
            $this->pdf->Line( $left_x + 5, $date_line_y, $left_x + $box_width - 5, $date_line_y );
            $this->pdf->SetXY( $left_x + 3, $date_line_y + 1 );
            $this->pdf->Cell( $box_width - 6, 4, 'Date', 0, 1 );
        }

        // ===== PERFORMER SIGNATURE BOX (RIGHT) =====
        $this->pdf->Rect( $right_x, $y_start, $box_width, $box_height, 'D' );

        // Performer header
        $this->pdf->SetXY( $right_x + 3, $y_start + 3 );
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( $box_width - 6, 6, 'PERFORMER', 0, 1 );
        $this->pdf->SetX( $right_x + 3 );
        $this->pdf->SetFont( 'helvetica', '', 10 );
        $this->pdf->Cell( $box_width - 6, 5, SMCB_COMPANY_NAME, 0, 1 );

        // Performer signature image (always shown)
        $performer_sig_path = SMCB_PLUGIN_DIR . 'assets/images/performer-signature.png';
        if ( file_exists( $performer_sig_path ) ) {
            $this->pdf->Image( $performer_sig_path, $right_x + 5, $y_start + 18, 50, 18 );
        }

        // Signature line
        $this->pdf->Line( $right_x + 5, $sig_line_y, $right_x + $box_width - 5, $sig_line_y );
        $this->pdf->SetXY( $right_x + 3, $sig_line_y + 1 );
        $this->pdf->SetFont( 'helvetica', '', 8 );
        $this->pdf->Cell( $box_width - 6, 4, 'Signature', 0, 1 );

        // Performer signed by info (always filled)
        $this->pdf->SetXY( $right_x + 3, $sig_line_y + 6 );
        $this->pdf->SetFont( 'helvetica', '', 9 );
        $this->pdf->Cell( $box_width - 6, 5, 'Signed by: Jay Goodman obo Skinny Moo', 0, 1 );
        $this->pdf->SetX( $right_x + 3 );

        // Use sent_at date for performer signature date, or current date if not sent
        $performer_date = ! empty( $this->contract->sent_at ) ? smcb_format_date( $this->contract->sent_at ) : date( 'F j, Y' );
        $this->pdf->Cell( $box_width - 6, 5, 'Date: ' . $performer_date, 0, 1 );

        // Move cursor below both boxes
        $this->pdf->SetY( $y_start + $box_height + 5 );
    }

    /**
     * Add invoice line items table.
     */
    private function add_invoice_table() {
        $this->pdf->SetFont( 'helvetica', 'B', 9 );
        $this->pdf->SetFillColor( $this->colors['secondary'][0], $this->colors['secondary'][1], $this->colors['secondary'][2] );
        $this->pdf->SetTextColor( 255, 255, 255 );

        // Table header
        $this->pdf->Cell( 100, 6, 'Description', 1, 0, 'L', true );
        $this->pdf->Cell( 25, 6, 'Qty', 1, 0, 'C', true );
        $this->pdf->Cell( 25, 6, 'Unit Price', 1, 0, 'R', true );
        $this->pdf->Cell( 25, 6, 'Amount', 1, 1, 'R', true );

        $this->pdf->SetTextColor( $this->colors['text'][0], $this->colors['text'][1], $this->colors['text'][2] );
        $this->pdf->SetFont( 'helvetica', '', 9 );

        $total = 0;

        // Line items
        if ( ! empty( $this->contract->line_items ) ) {
            foreach ( $this->contract->line_items as $item ) {
                $amount = floatval( $item->quantity ) * floatval( $item->unit_price );
                $total += $amount;

                $this->pdf->Cell( 100, 5, $item->description, 1, 0, 'L' );
                $this->pdf->Cell( 25, 5, number_format( $item->quantity, 0 ), 1, 0, 'C' );
                $this->pdf->Cell( 25, 5, smcb_format_currency( $item->unit_price ), 1, 0, 'R' );
                $this->pdf->Cell( 25, 5, smcb_format_currency( $amount ), 1, 1, 'R' );
            }
        }

        // Add base compensation if no line items
        if ( empty( $this->contract->line_items ) ) {
            $this->pdf->Cell( 100, 5, 'Performance Services - ' . $this->contract->event_name, 1, 0, 'L' );
            $this->pdf->Cell( 25, 5, '1', 1, 0, 'C' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->base_compensation ), 1, 0, 'R' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->base_compensation ), 1, 1, 'R' );
            $total = $this->contract->base_compensation;
        }

        // Travel fee
        if ( $this->contract->mileage_travel_fee > 0 ) {
            $total += $this->contract->mileage_travel_fee;
            $this->pdf->Cell( 100, 5, 'Travel / Mileage Fee', 1, 0, 'L' );
            $this->pdf->Cell( 25, 5, '1', 1, 0, 'C' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->mileage_travel_fee ), 1, 0, 'R' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->mileage_travel_fee ), 1, 1, 'R' );
        }

        // Early load-in fee
        if ( $this->contract->early_loadin_required && $this->contract->early_loadin_hours > 0 ) {
            $early_fee = $this->contract->calculated->early_loadin_fee;
            $total += $early_fee;
            $this->pdf->Cell( 100, 5, 'Early Load-in (' . $this->contract->early_loadin_hours . 'hrs @ $' . SMCB_EARLY_LOADIN_RATE . '/hr)', 1, 0, 'L' );
            $this->pdf->Cell( 25, 5, '1', 1, 0, 'C' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $early_fee ), 1, 0, 'R' );
            $this->pdf->Cell( 25, 5, smcb_format_currency( $early_fee ), 1, 1, 'R' );
        }

        // Total row
        $this->pdf->SetFont( 'helvetica', 'B', 10 );
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->Cell( 150, 6, 'TOTAL', 1, 0, 'R', true );
        $this->pdf->Cell( 25, 6, smcb_format_currency( $this->contract->calculated->total_compensation ), 1, 1, 'R', true );

        // Deposit and balance
        $this->pdf->SetFont( 'helvetica', '', 9 );
        $this->pdf->Cell( 150, 5, 'Deposit Due (' . $this->contract->deposit_percentage . '%)', 1, 0, 'R' );
        $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->calculated->deposit_amount ), 1, 1, 'R' );

        $this->pdf->SetFont( 'helvetica', 'B', 9 );
        $this->pdf->Cell( 150, 5, 'BALANCE DUE AT EVENT', 1, 0, 'R' );
        $this->pdf->Cell( 25, 5, smcb_format_currency( $this->contract->calculated->balance_due ), 1, 1, 'R' );
    }

    /**
     * Add event summary box for cover letter.
     */
    private function add_event_summary_box() {
        $this->pdf->SetFillColor( $this->colors['light'][0], $this->colors['light'][1], $this->colors['light'][2] );
        $this->pdf->SetDrawColor( $this->colors['primary'][0], $this->colors['primary'][1], $this->colors['primary'][2] );

        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 0, 8, 'Event Summary', 'L', 1, 'L', true );

        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, 'Event: ' . $this->contract->event_name, 'L', 1, 'L', true );
        $this->pdf->Cell( 0, 6, 'Date: ' . smcb_format_date( $this->contract->performance_date ), 'L', 1, 'L', true );
        $this->pdf->Cell( 0, 6, 'Time: ' . smcb_format_time( $this->contract->first_set_start_time ), 'L', 1, 'L', true );
        $this->pdf->Cell( 0, 6, 'Total: ' . smcb_format_currency( $this->contract->calculated->total_compensation ), 'L', 1, 'L', true );
        $this->pdf->Cell( 0, 6, 'Deposit Required: ' . smcb_format_currency( $this->contract->calculated->deposit_amount ), 'LB', 1, 'L', true );
    }

    /**
     * Add a detail row.
     *
     * @param string $label Label.
     * @param string $value Value.
     */
    private function add_detail_row( $label, $value ) {
        $this->pdf->SetFont( 'helvetica', 'B', 11 );
        $this->pdf->Cell( 50, 6, $label . ':', 0, 0 );
        $this->pdf->SetFont( 'helvetica', '', 11 );
        $this->pdf->Cell( 0, 6, $value, 0, 1 );
    }

    /**
     * Check if content will fit on current page, add new page if not.
     *
     * @param float $height_needed Height in mm needed for the content.
     * @return bool True if a new page was added.
     */
    private function check_page_break( $height_needed ) {
        $page_height = $this->pdf->getPageHeight();
        $margins = $this->pdf->getMargins();
        $current_y = $this->pdf->GetY();
        $available_height = $page_height - $margins['bottom'] - $current_y;

        if ( $available_height < $height_needed ) {
            $this->pdf->AddPage();
            return true;
        }
        return false;
    }

    /**
     * Get default cover letter message.
     *
     * @return string Default message.
     */
    private function get_default_cover_letter_message() {
        return "Thank you for considering Skinny Moo for your upcoming event! We are excited about the opportunity to provide entertainment for " . $this->contract->event_name . " on " . smcb_format_date( $this->contract->performance_date ) . ".\n\nEnclosed you will find our Performance Agreement and Invoice for your review.";
    }

    /**
     * Get contract terms.
     *
     * @return array Array of terms.
     */
    private function get_contract_terms() {
        return array(
            'DEPOSIT: A non-refundable deposit of ' . $this->contract->deposit_percentage . '% (' . smcb_format_currency( $this->contract->calculated->deposit_amount ) . ') is due upon signing of this agreement. The remaining balance is due on the day of the performance.',
            'CANCELLATION: If CLIENT cancels this engagement, the deposit is non-refundable. If cancellation occurs within 30 days of the performance date, CLIENT agrees to pay 50% of the total contract amount. If cancellation occurs within 14 days of the performance date, CLIENT agrees to pay 100% of the total contract amount.',
            'CANCELLATION BY PERFORMER: If PERFORMER must cancel this engagement due to circumstances beyond their control (illness, injury, emergency, etc.), PERFORMER will make every reasonable effort to find a suitable replacement. If no replacement can be found, all payments made by CLIENT will be refunded in full.',
            'FORCE MAJEURE: Neither party shall be liable for failure to perform due to circumstances beyond their reasonable control, including but not limited to: acts of God, natural disasters, government actions, pandemic, or venue closure.',
            'SOUND AND LIGHTING: As specified in this agreement, sound and lighting equipment will be provided as noted. Any additional equipment required must be arranged and paid for separately.',
            'MEALS AND REFRESHMENTS: If the performance extends through normal meal times, CLIENT agrees to provide a meal for the performing members. Reasonable non-alcoholic beverages should be available throughout the engagement.',
            'PARKING: CLIENT will provide convenient and safe parking for PERFORMER vehicles and equipment at no charge.',
            'SAFETY: CLIENT will provide a safe performance environment. PERFORMER reserves the right to stop performance if conditions become unsafe.',
            'RECORDING: PERFORMER may record audio and/or video of the performance for promotional purposes. CLIENT may photograph or record the performance for personal, non-commercial use.',
            'AMENDMENTS: Any modifications to this agreement must be made in writing and signed by both parties.',
            'ENTIRE AGREEMENT: This agreement constitutes the entire agreement between the parties and supersedes all prior negotiations, understandings, and agreements between the parties.',
        );
    }

    /**
     * Save PDF to file.
     *
     * @param string $type PDF type (contract, invoice, cover-letter, signed-contract).
     * @return string|false File path or false on failure.
     */
    private function save_pdf( $type ) {
        $upload_dir = wp_upload_dir();
        $year = date( 'Y', strtotime( $this->contract->performance_date ) );
        $base_dir = $upload_dir['basedir'] . '/smcb-contracts/' . $year;

        // Ensure directory exists
        if ( ! file_exists( $base_dir ) ) {
            wp_mkdir_p( $base_dir );
            file_put_contents( $base_dir . '/index.php', '<?php // Silence is golden' );
        }

        $filename = sprintf(
            '%s-%s-%s.pdf',
            $this->contract->contract_number,
            $type,
            date( 'Ymd' )
        );

        $filepath = $base_dir . '/' . $filename;

        try {
            $this->pdf->Output( $filepath, 'F' );
            return $filepath;
        } catch ( Exception $e ) {
            error_log( 'SMCB PDF Generation Error: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Get PDF file URL.
     *
     * @param string $filepath File path.
     * @return string URL.
     */
    public static function get_pdf_url( $filepath ) {
        $upload_dir = wp_upload_dir();
        return str_replace( $upload_dir['basedir'], $upload_dir['baseurl'], $filepath );
    }
}
