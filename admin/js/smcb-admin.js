/**
 * Skinny Moo Contract Builder - Admin JavaScript
 */

(function($) {
    'use strict';

    // Global state
    var lineItemIndex = 0;

    /**
     * Initialize admin functionality
     */
    function init() {
        initContractForm();
        initContractsList();
        initContractView();
        initLineItems();
        initCalculations();
        initConditionalFields();
    }

    /**
     * Contract Form functionality
     */
    function initContractForm() {
        var $form = $('#smcb-contract-form');
        if (!$form.length) return;

        // Save Draft button
        $('#save-draft').on('click', function() {
            $('#smcb_action').val('save');
        });

        // Save & Send button
        $('#save-send').on('click', function() {
            if (confirm(smcb_admin.confirm_send)) {
                $('#smcb_action').val('send');
            } else {
                return false;
            }
        });

        // Set times calculation
        $('#first_set_start_time, #number_of_sets, #set_length, #break_length').on('change', calculateSetTimes);
        calculateSetTimes(); // Initial calculation
    }

    /**
     * Calculate and display set times
     */
    function calculateSetTimes() {
        var startTime = $('#first_set_start_time').val();
        var numSets = parseInt($('#number_of_sets').val()) || 3;
        var setLength = parseInt($('#set_length').val()) || 60;
        var breakLength = parseInt($('#break_length').val()) || 30;

        if (!startTime) {
            $('#set-times-preview').hide();
            return;
        }

        var sets = [];
        var currentTime = parseTime(startTime);

        for (var i = 1; i <= numSets; i++) {
            var endTime = addMinutes(currentTime, setLength);
            sets.push({
                number: i,
                start: formatTime(currentTime),
                end: formatTime(endTime)
            });
            currentTime = addMinutes(endTime, breakLength);
        }

        // Display set times
        var html = '';
        sets.forEach(function(set) {
            html += '<span class="set-time-item"><strong>Set ' + set.number + ':</strong> ' + set.start + ' - ' + set.end + '</span>';
        });

        $('#set-times-list').html(html);
        $('#set-times-preview').show();
    }

    /**
     * Parse time string to object
     */
    function parseTime(timeStr) {
        var parts = timeStr.split(':');
        return {
            hours: parseInt(parts[0]),
            minutes: parseInt(parts[1])
        };
    }

    /**
     * Add minutes to time object
     */
    function addMinutes(time, minutes) {
        var totalMinutes = time.hours * 60 + time.minutes + minutes;
        return {
            hours: Math.floor(totalMinutes / 60) % 24,
            minutes: totalMinutes % 60
        };
    }

    /**
     * Format time object to display string
     */
    function formatTime(time) {
        var hours = time.hours;
        var ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        var minutes = time.minutes < 10 ? '0' + time.minutes : time.minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }

    /**
     * Initialize totals calculations
     */
    function initCalculations() {
        var $form = $('#smcb-contract-form');
        if (!$form.length) return;

        // Fields that affect totals
        var calcFields = '#base_compensation, #mileage_travel_fee, #deposit_percentage, #early_loadin_required, #early_loadin_hours';
        $(calcFields).on('change input', calculateTotals);

        // Line item changes
        $(document).on('change input', '.line-item-quantity, .line-item-price', function() {
            updateLineItemTotal($(this).closest('tr'));
            calculateTotals();
        });

        calculateTotals(); // Initial calculation
    }

    /**
     * Calculate totals
     */
    function calculateTotals() {
        var baseComp = parseFloat($('#base_compensation').val()) || 0;
        var travelFee = parseFloat($('#mileage_travel_fee').val()) || 0;
        var depositPercent = parseFloat($('#deposit_percentage').val()) || 30;
        var earlyRequired = $('#early_loadin_required').is(':checked');
        var earlyHours = parseFloat($('#early_loadin_hours').val()) || 0;
        var earlyRate = 100; // $100/hour

        var earlyFee = earlyRequired ? (earlyHours * earlyRate) : 0;
        var total = baseComp + travelFee + earlyFee;
        var deposit = total * (depositPercent / 100);
        var balance = total - deposit;

        $('#total-compensation').text(formatCurrency(total));
        $('#deposit-amount').text(formatCurrency(deposit));
        $('#balance-due').text(formatCurrency(balance));
    }

    /**
     * Format currency
     */
    function formatCurrency(amount) {
        return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }

    /**
     * Initialize conditional fields
     */
    function initConditionalFields() {
        // Early load-in hours field
        $('#early_loadin_required').on('change', function() {
            if ($(this).is(':checked')) {
                $('.smcb-early-loadin-hours').slideDown();
            } else {
                $('.smcb-early-loadin-hours').slideUp();
            }
        });

        // Outside production notes field
        $('#outside_production').on('change', function() {
            if ($(this).is(':checked')) {
                $('.smcb-outside-production-notes').slideDown();
            } else {
                $('.smcb-outside-production-notes').slideUp();
            }
        });
    }

    /**
     * Initialize line items functionality
     */
    function initLineItems() {
        var $table = $('#line-items-table');
        if (!$table.length) return;

        // Set initial index based on existing items
        lineItemIndex = $table.find('tbody tr').length;

        // Add line item
        $('#add-line-item').on('click', function() {
            var template = $('#line-item-template').html();
            template = template.replace(/\{\{index\}\}/g, lineItemIndex);
            $table.find('tbody').append(template);
            lineItemIndex++;
        });

        // Remove line item
        $(document).on('click', '.smcb-remove-line-item', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });
    }

    /**
     * Update line item total
     */
    function updateLineItemTotal($row) {
        var qty = parseFloat($row.find('.line-item-quantity').val()) || 0;
        var price = parseFloat($row.find('.line-item-price').val()) || 0;
        var total = qty * price;
        $row.find('.line-item-total').text(formatCurrency(total));
    }

    /**
     * Contracts List functionality
     */
    function initContractsList() {
        var $table = $('.smcb-contracts-table');
        if (!$table.length) return;

        // Send contract
        $(document).on('click', '.smcb-action-send', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var contractId = $btn.data('contract-id');

            if (!confirm(smcb_admin.confirm_send)) {
                return;
            }

            $btn.addClass('smcb-loading');

            $.ajax({
                url: smcb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'smcb_send_contract',
                    nonce: smcb_admin.nonce,
                    contract_id: contractId
                },
                success: function(response) {
                    $btn.removeClass('smcb-loading');
                    if (response.success) {
                        alert(response.data.message);
                        // Update status badge
                        var $row = $btn.closest('tr');
                        $row.find('.smcb-status').removeClass('smcb-status-draft').addClass('smcb-status-sent').text('Sent');
                    } else {
                        alert(response.data.message || 'Error sending contract');
                    }
                },
                error: function() {
                    $btn.removeClass('smcb-loading');
                    alert('Error sending contract');
                }
            });
        });

        // Delete contract
        $(document).on('click', '.smcb-action-delete', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var contractId = $btn.data('contract-id');

            if (!confirm(smcb_admin.confirm_delete)) {
                return;
            }

            $.ajax({
                url: smcb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'smcb_delete_contract',
                    nonce: smcb_admin.nonce,
                    contract_id: contractId
                },
                success: function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || 'Error deleting contract');
                    }
                },
                error: function() {
                    alert('Error deleting contract');
                }
            });
        });
    }

    /**
     * Contract View functionality
     */
    function initContractView() {
        var $page = $('.smcb-contract-view-page');
        if (!$page.length) return;

        // Copy contract link
        $('.smcb-copy-link').on('click', function() {
            var $input = $('#contract-url');
            $input.select();
            document.execCommand('copy');

            var $btn = $(this);
            $btn.find('.dashicons').removeClass('dashicons-clipboard').addClass('dashicons-yes');
            setTimeout(function() {
                $btn.find('.dashicons').removeClass('dashicons-yes').addClass('dashicons-clipboard');
            }, 2000);
        });

        // Regenerate token
        $('.smcb-regenerate-token').on('click', function() {
            var $btn = $(this);
            var contractId = $btn.data('contract-id');

            if (!confirm('Generate a new access link? The old link will no longer work.')) {
                return;
            }

            $btn.prop('disabled', true);

            $.ajax({
                url: smcb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'smcb_regenerate_token',
                    nonce: smcb_admin.nonce,
                    contract_id: contractId
                },
                success: function(response) {
                    $btn.prop('disabled', false);
                    if (response.success) {
                        $('#contract-url').val(response.data.url);
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || 'Error regenerating token');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false);
                    alert('Error regenerating token');
                }
            });
        });

        // Generate PDFs
        $('.smcb-generate-pdfs').on('click', function() {
            var $btn = $(this);
            var contractId = $btn.data('contract-id');

            $btn.prop('disabled', true).text('Generating...');

            $.ajax({
                url: smcb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'smcb_generate_pdfs',
                    nonce: smcb_admin.nonce,
                    contract_id: contractId
                },
                success: function(response) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> Generate PDFs');
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error generating PDFs');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> Generate PDFs');
                    alert('Error generating PDFs');
                }
            });
        });

        // Send contract
        $page.find('.smcb-action-send').on('click', function() {
            var $btn = $(this);
            var contractId = $btn.data('contract-id');

            if (!confirm(smcb_admin.confirm_send)) {
                return;
            }

            $btn.prop('disabled', true).text('Sending...');

            $.ajax({
                url: smcb_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'smcb_send_contract',
                    nonce: smcb_admin.nonce,
                    contract_id: contractId
                },
                success: function(response) {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Send Contract');
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || 'Error sending contract');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-email-alt"></span> Send Contract');
                    alert('Error sending contract');
                }
            });
        });
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
