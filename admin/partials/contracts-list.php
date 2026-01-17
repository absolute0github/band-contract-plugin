<?php
/**
 * Admin contracts list template.
 *
 * @package Skinny_Moo_Contract_Builder
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap smcb-admin">
    <h1 class="wp-heading-inline"><?php esc_html_e( 'Contracts', 'skinny-moo-contract-builder' ); ?></h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=smcb-add-contract' ) ); ?>" class="page-title-action">
        <?php esc_html_e( 'Add New', 'skinny-moo-contract-builder' ); ?>
    </a>
    <hr class="wp-header-end">

    <!-- Statistics Cards -->
    <div class="smcb-stats-cards">
        <div class="smcb-stat-card">
            <span class="smcb-stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
            <span class="smcb-stat-label"><?php esc_html_e( 'Total Contracts', 'skinny-moo-contract-builder' ); ?></span>
        </div>
        <div class="smcb-stat-card smcb-stat-pending">
            <span class="smcb-stat-number"><?php echo esc_html( $stats['sent'] + $stats['viewed'] ); ?></span>
            <span class="smcb-stat-label"><?php esc_html_e( 'Pending Signature', 'skinny-moo-contract-builder' ); ?></span>
        </div>
        <div class="smcb-stat-card smcb-stat-signed">
            <span class="smcb-stat-number"><?php echo esc_html( $stats['signed_this_month'] ); ?></span>
            <span class="smcb-stat-label"><?php esc_html_e( 'Signed This Month', 'skinny-moo-contract-builder' ); ?></span>
        </div>
        <div class="smcb-stat-card smcb-stat-upcoming">
            <span class="smcb-stat-number"><?php echo esc_html( $stats['upcoming_events'] ); ?></span>
            <span class="smcb-stat-label"><?php esc_html_e( 'Upcoming Events', 'skinny-moo-contract-builder' ); ?></span>
        </div>
        <div class="smcb-stat-card smcb-stat-revenue">
            <span class="smcb-stat-number"><?php echo esc_html( smcb_format_currency( $stats['total_revenue'] ) ); ?></span>
            <span class="smcb-stat-label"><?php esc_html_e( 'Signed Revenue', 'skinny-moo-contract-builder' ); ?></span>
        </div>
    </div>

    <!-- Filters -->
    <div class="smcb-filters">
        <ul class="subsubsub">
            <li>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=smcb-contracts' ) ); ?>" <?php echo empty( $args['status'] ) ? 'class="current"' : ''; ?>>
                    <?php esc_html_e( 'All', 'skinny-moo-contract-builder' ); ?>
                    <span class="count">(<?php echo esc_html( $stats['total'] ); ?>)</span>
                </a> |
            </li>
            <?php foreach ( smcb_get_contract_statuses() as $status_key => $status_label ) : ?>
                <li>
                    <a href="<?php echo esc_url( add_query_arg( 'status', $status_key, admin_url( 'admin.php?page=smcb-contracts' ) ) ); ?>" <?php echo $args['status'] === $status_key ? 'class="current"' : ''; ?>>
                        <?php echo esc_html( $status_label ); ?>
                        <span class="count">(<?php echo esc_html( $stats[ $status_key ] ?? 0 ); ?>)</span>
                    </a>
                    <?php echo $status_key !== 'cancelled' ? '|' : ''; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <form method="get" class="smcb-search-form">
            <input type="hidden" name="page" value="smcb-contracts">
            <?php if ( ! empty( $args['status'] ) ) : ?>
                <input type="hidden" name="status" value="<?php echo esc_attr( $args['status'] ); ?>">
            <?php endif; ?>
            <p class="search-box">
                <label class="screen-reader-text" for="contract-search-input"><?php esc_html_e( 'Search contracts:', 'skinny-moo-contract-builder' ); ?></label>
                <input type="search" id="contract-search-input" name="s" value="<?php echo esc_attr( $args['search'] ); ?>" placeholder="<?php esc_attr_e( 'Search by client, event, or contract #', 'skinny-moo-contract-builder' ); ?>">
                <input type="submit" id="search-submit" class="button" value="<?php esc_attr_e( 'Search', 'skinny-moo-contract-builder' ); ?>">
            </p>
        </form>
    </div>

    <!-- Contracts Table -->
    <table class="wp-list-table widefat fixed striped smcb-contracts-table">
        <thead>
            <tr>
                <th scope="col" class="column-contract-number"><?php esc_html_e( 'Contract #', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-client"><?php esc_html_e( 'Client', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-event"><?php esc_html_e( 'Event', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-date"><?php esc_html_e( 'Date', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-amount"><?php esc_html_e( 'Amount', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-status"><?php esc_html_e( 'Status', 'skinny-moo-contract-builder' ); ?></th>
                <th scope="col" class="column-actions"><?php esc_html_e( 'Actions', 'skinny-moo-contract-builder' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( empty( $contracts ) ) : ?>
                <tr>
                    <td colspan="7" class="smcb-no-contracts">
                        <?php esc_html_e( 'No contracts found.', 'skinny-moo-contract-builder' ); ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=smcb-add-contract' ) ); ?>">
                            <?php esc_html_e( 'Create your first contract', 'skinny-moo-contract-builder' ); ?>
                        </a>
                    </td>
                </tr>
            <?php else : ?>
                <?php foreach ( $contracts as $contract ) : ?>
                    <tr data-contract-id="<?php echo esc_attr( $contract->id ); ?>">
                        <td class="column-contract-number">
                            <strong>
                                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-contracts', 'action' => 'view', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>">
                                    <?php echo esc_html( $contract->contract_number ); ?>
                                </a>
                            </strong>
                        </td>
                        <td class="column-client">
                            <strong><?php echo esc_html( $contract->client_company_name ); ?></strong>
                            <br><span class="smcb-contact"><?php echo esc_html( $contract->contact_person_name ); ?></span>
                        </td>
                        <td class="column-event">
                            <?php echo esc_html( $contract->event_name ); ?>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html( smcb_format_date( $contract->performance_date ) ); ?>
                            <br><span class="smcb-time"><?php echo esc_html( smcb_format_time( $contract->first_set_start_time ) ); ?></span>
                        </td>
                        <td class="column-amount">
                            <?php echo esc_html( smcb_format_currency( $contract->calculated->total_compensation ) ); ?>
                        </td>
                        <td class="column-status">
                            <span class="smcb-status smcb-status-<?php echo esc_attr( $contract->status ); ?>">
                                <?php echo esc_html( smcb_get_contract_statuses()[ $contract->status ] ?? $contract->status ); ?>
                            </span>
                        </td>
                        <td class="column-actions">
                            <div class="smcb-row-actions">
                                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-contracts', 'action' => 'view', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>" class="smcb-action-view" title="<?php esc_attr_e( 'View', 'skinny-moo-contract-builder' ); ?>">
                                    <span class="dashicons dashicons-visibility"></span>
                                </a>
                                <a href="<?php echo esc_url( add_query_arg( array( 'page' => 'smcb-add-contract', 'id' => $contract->id ), admin_url( 'admin.php' ) ) ); ?>" class="smcb-action-edit" title="<?php esc_attr_e( 'Edit', 'skinny-moo-contract-builder' ); ?>">
                                    <span class="dashicons dashicons-edit"></span>
                                </a>
                                <?php if ( $contract->status === 'draft' || $contract->status === 'sent' || $contract->status === 'viewed' ) : ?>
                                    <a href="#" class="smcb-action-send" data-contract-id="<?php echo esc_attr( $contract->id ); ?>" title="<?php esc_attr_e( 'Send', 'skinny-moo-contract-builder' ); ?>">
                                        <span class="dashicons dashicons-email-alt"></span>
                                    </a>
                                <?php endif; ?>
                                <a href="#" class="smcb-action-delete" data-contract-id="<?php echo esc_attr( $contract->id ); ?>" title="<?php esc_attr_e( 'Delete', 'skinny-moo-contract-builder' ); ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ( $total_pages > 1 ) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <span class="displaying-num">
                    <?php
                    printf(
                        /* translators: %s: Number of items */
                        esc_html( _n( '%s item', '%s items', $total, 'skinny-moo-contract-builder' ) ),
                        esc_html( number_format_i18n( $total ) )
                    );
                    ?>
                </span>
                <span class="pagination-links">
                    <?php
                    $base_url = add_query_arg(
                        array(
                            'page'   => 'smcb-contracts',
                            'status' => $args['status'],
                            's'      => $args['search'],
                        ),
                        admin_url( 'admin.php' )
                    );

                    // First page
                    if ( $args['page'] > 1 ) {
                        echo '<a class="first-page button" href="' . esc_url( add_query_arg( 'paged', 1, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'First page', 'skinny-moo-contract-builder' ) . '</span><span aria-hidden="true">&laquo;</span></a>';
                        echo '<a class="prev-page button" href="' . esc_url( add_query_arg( 'paged', $args['page'] - 1, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Previous page', 'skinny-moo-contract-builder' ) . '</span><span aria-hidden="true">&lsaquo;</span></a>';
                    } else {
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&laquo;</span>';
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&lsaquo;</span>';
                    }

                    // Current page
                    echo '<span class="paging-input">';
                    echo '<span class="tablenav-paging-text">' . esc_html( $args['page'] ) . ' of <span class="total-pages">' . esc_html( $total_pages ) . '</span></span>';
                    echo '</span>';

                    // Last page
                    if ( $args['page'] < $total_pages ) {
                        echo '<a class="next-page button" href="' . esc_url( add_query_arg( 'paged', $args['page'] + 1, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Next page', 'skinny-moo-contract-builder' ) . '</span><span aria-hidden="true">&rsaquo;</span></a>';
                        echo '<a class="last-page button" href="' . esc_url( add_query_arg( 'paged', $total_pages, $base_url ) ) . '"><span class="screen-reader-text">' . esc_html__( 'Last page', 'skinny-moo-contract-builder' ) . '</span><span aria-hidden="true">&raquo;</span></a>';
                    } else {
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&rsaquo;</span>';
                        echo '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">&raquo;</span>';
                    }
                    ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>
