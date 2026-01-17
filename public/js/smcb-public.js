/**
 * Skinny Moo Contract Builder - Public JavaScript
 */

(function($) {
    'use strict';

    var signaturePad = null;

    /**
     * Initialize public functionality
     */
    function init() {
        initSignaturePad();
        initSignatureForm();
        checkScrollToTop();
    }

    /**
     * Check if we should scroll to top (after signature)
     */
    function checkScrollToTop() {
        var params = new URLSearchParams(window.location.search);
        if (params.get('signed') === '1') {
            // Scroll to top to show success message
            $('html, body').animate({
                scrollTop: 0
            }, 500);
            // Clean up URL by removing the signed parameter
            params.delete('signed');
            var newUrl = window.location.pathname;
            if (params.toString()) {
                newUrl += '?' + params.toString();
            }
            window.history.replaceState({}, '', newUrl);
        }
    }

    /**
     * Initialize signature pad
     */
    function initSignaturePad() {
        var canvas = document.getElementById('signature-pad');
        if (!canvas) return;

        // Initialize SignaturePad
        signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)',
            minWidth: 1,
            maxWidth: 3
        });

        // Handle canvas resize
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            var rect = canvas.getBoundingClientRect();
            canvas.width = rect.width * ratio;
            canvas.height = rect.height * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        // Initial resize
        resizeCanvas();

        // Resize on window resize
        $(window).on('resize', function() {
            var data = signaturePad.toData();
            resizeCanvas();
            signaturePad.fromData(data);
        });

        // Clear signature button
        $('#clear-signature').on('click', function() {
            signaturePad.clear();
        });
    }

    /**
     * Initialize signature form
     */
    function initSignatureForm() {
        var $form = $('#smcb-signature-form');
        if (!$form.length) return;

        $form.on('submit', function(e) {
            e.preventDefault();

            var $submitBtn = $('#submit-signature');
            var $errorBox = $('#signature-error');
            var $successBox = $('#signature-success');

            // Reset messages
            $errorBox.hide().html('');
            $successBox.hide().html('');

            // Validate
            var signedName = $('#signed_name').val().trim();
            var agreeTerms = $('#agree_terms').is(':checked');

            if (!signedName) {
                showError(smcb_public.name_required);
                return;
            }

            if (!signaturePad || signaturePad.isEmpty()) {
                showError(smcb_public.signature_required);
                return;
            }

            if (!agreeTerms) {
                showError(smcb_public.agree_required);
                return;
            }

            // Get signature data
            var signatureData = signaturePad.toDataURL('image/png');

            // Disable button
            $submitBtn.prop('disabled', true).text(smcb_public.signing);

            // Submit via REST API
            $.ajax({
                url: smcb_public.rest_url + 'sign',
                type: 'POST',
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', smcb_public.nonce);
                },
                data: {
                    token: $form.data('token'),
                    signed_name: signedName,
                    signature: signatureData
                },
                success: function(response) {
                    $submitBtn.prop('disabled', false).text(smcb_public.sign_contract);

                    if (response.success) {
                        showSuccess(response.message);
                        // Hide form, show success
                        $form.slideUp(function() {
                            $('.smcb-signature-section h2').text('Contract Signed!');
                        });
                        // Reload after delay and scroll to top to show success message
                        setTimeout(function() {
                            // Add signed parameter to trigger scroll on reload
                            var url = window.location.href.split('?')[0];
                            var params = new URLSearchParams(window.location.search);
                            params.set('signed', '1');
                            window.location.href = url + '?' + params.toString();
                        }, 2000);
                    } else {
                        showError(response.message || 'An error occurred. Please try again.');
                    }
                },
                error: function(xhr) {
                    $submitBtn.prop('disabled', false).text(smcb_public.sign_contract);
                    var message = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showError(message);
                }
            });
        });

        function showError(message) {
            $('#signature-error').html(message).slideDown();
            $('html, body').animate({
                scrollTop: $('#signature-error').offset().top - 100
            }, 300);
        }

        function showSuccess(message) {
            $('#signature-success').html(message).slideDown();
        }
    }

    // Initialize when document is ready
    $(document).ready(init);

})(jQuery);
