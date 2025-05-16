/**
 * Admin JavaScript for Temporary Login Links Premium.
 *
 * This file includes scripts for the admin interface of the plugin,
 * including link management, form handling, and UI interactions.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/admin/js
 */

(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
     * should reside in this file.
     */

    // Initialize when document is ready
    $(document).ready(function() {
        // Initialize TLP Admin
        TLPAdmin.init();
    });

    /**
     * TLP Admin Class
     */
    var TLPAdmin = {
        /**
         * Initialize the admin functionality.
         */
        init: function() {
            this.setupTabs();
            this.setupDatepicker();
            this.setupColorPickers();
            this.setupMediaUploader();
            this.setupCopyToClipboard();
            this.setupDeleteConfirmation();
            this.setupDeactivateConfirmation();
            this.setupExpiryToggle();
            this.setupAjaxActions();
            this.setupTooltips();
            this.setupModalDialogs();
            this.setupBrandingPreview();
            this.setupNotificationDismiss();
        },

        /**
         * Set up tabs for settings pages.
         */
        setupTabs: function() {
            $('.tlp-tabs-wrapper a.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                $('.tlp-tabs-wrapper a.nav-tab').removeClass('nav-tab-active');
                
                // Add active class to the clicked tab
                $(this).addClass('nav-tab-active');
                
                // Hide all tab content
                $('.tlp-tab-content').hide();
                
                // Show the content for the clicked tab
                var target = $(this).data('tab');
                $('#' + target).show();
                
                // Update URL hash
                window.history.pushState({}, '', $(this).attr('href'));
            });
            
            // Check for hash in URL
            if (window.location.hash) {
                var hash = window.location.hash.substring(1);
                if ($('[data-tab="' + hash + '"]').length) {
                    $('[data-tab="' + hash + '"]').trigger('click');
                } else {
                    // Show the first tab by default
                    $('.tlp-tabs-wrapper a.nav-tab').first().trigger('click');
                }
            } else {
                // Show the first tab by default
                $('.tlp-tabs-wrapper a.nav-tab').first().trigger('click');
            }
        },

        /**
         * Set up datepicker for expiry date fields.
         */
        setupDatepicker: function() {
            if ($.fn.datepicker) {
                $('.tlp-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true,
                    minDate: 0 // Only future dates
                });
                
                // Add timepicker if available
                if ($.fn.timepicker) {
                    $('.tlp-timepicker').timepicker({
                        timeFormat: 'HH:mm:ss',
                        showSecond: true,
                        showMillisec: false,
                        stepMinute: 1,
                        stepSecond: 5,
                        showButtonPanel: false
                    });
                }
            }
        },

        /**
         * Set up color pickers for branding settings.
         */
        setupColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.tlp-color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Update the branding preview when color changes
                        TLPAdmin.updateBrandingPreview();
                    }
                });
            }
        },

        /**
         * Set up WordPress media uploader for logo upload.
         */
        setupMediaUploader: function() {
            var mediaUploader;
            
            $('.tlp-upload-button').on('click', function(e) {
                e.preventDefault();
                
                var buttonElement = $(this);
                var previewElement = buttonElement.closest('.tlp-media-wrapper').find('.tlp-media-preview');
                var inputElement = buttonElement.closest('.tlp-media-wrapper').find('.tlp-media-input');
                var removeButton = buttonElement.closest('.tlp-media-wrapper').find('.tlp-remove-media');
                
                // If the media uploader exists, open it
                if (mediaUploader) {
                    mediaUploader.open();
                    return;
                }
                
                // Create the media uploader
                mediaUploader = wp.media({
                    title: 'Select or Upload Media',
                    button: {
                        text: 'Use this media'
                    },
                    multiple: false
                });
                
                // When a media is selected, run a callback
                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();
                    
                    // Set the input value
                    inputElement.val(attachment.url);
                    
                    // Update the preview
                    if (attachment.type === 'image') {
                        previewElement.html('<img src="' + attachment.url + '" alt="Media preview">');
                    } else {
                        previewElement.html('<div class="tlp-file-preview">' + attachment.filename + '</div>');
                    }
                    
                    // Show the remove button
                    removeButton.show();
                    
                    // Update branding preview if applicable
                    TLPAdmin.updateBrandingPreview();
                });
                
                // Open the media uploader
                mediaUploader.open();
            });
            
            // Handle remove media button
            $('.tlp-remove-media').on('click', function(e) {
                e.preventDefault();
                
                var buttonElement = $(this);
                var previewElement = buttonElement.closest('.tlp-media-wrapper').find('.tlp-media-preview');
                var inputElement = buttonElement.closest('.tlp-media-wrapper').find('.tlp-media-input');
                
                // Clear the input value
                inputElement.val('');
                
                // Clear the preview
                previewElement.html('');
                
                // Hide the remove button
                buttonElement.hide();
                
                // Update branding preview if applicable
                TLPAdmin.updateBrandingPreview();
            });
        },

        /**
         * Set up copy to clipboard functionality.
         */
        setupCopyToClipboard: function() {
            $('.tlp-copy-button').on('click', function() {
                var textToCopy = $(this).data('copy');
                var successMessage = $(this).siblings('.tlp-copy-success');
                
                // Create a temporary input element
                var tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(textToCopy).select();
                
                // Copy the text
                document.execCommand('copy');
                
                // Remove the temporary input
                tempInput.remove();
                
                // Show success message
                successMessage.fadeIn(200);
                
                // Hide success message after 2 seconds
                setTimeout(function() {
                    successMessage.fadeOut(200);
                }, 2000);
            });
        },

        /**
         * Set up delete confirmation for links.
         */
        setupDeleteConfirmation: function() {
            $(document).on('click', '.tlp-delete-link', function(e) {
                var confirmMessage = $(this).data('confirm') || tlp_ajax.confirm_delete;
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        },

        /**
         * Set up deactivate confirmation for links.
         */
        setupDeactivateConfirmation: function() {
            $(document).on('click', '.tlp-deactivate-link', function(e) {
                var confirmMessage = $(this).data('confirm') || tlp_ajax.confirm_deactivate;
                
                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            });
        },

        /**
         * Set up expiry date toggle for custom expiry.
         */
        setupExpiryToggle: function() {
            $('select[name="expiry"]').on('change', function() {
                var value = $(this).val();
                
                if (value === 'custom') {
                    $('.tlp-custom-expiry').slideDown(200);
                } else {
                    $('.tlp-custom-expiry').slideUp(200);
                }
            });
            
            // Trigger on page load
            $('select[name="expiry"]').trigger('change');
        },

        /**
         * Set up AJAX actions for link management.
         */
        setupAjaxActions: function() {
            // AJAX delete link
            $(document).on('click', '.tlp-ajax-delete', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                var row = $(this).closest('tr');
                var confirmMessage = $(this).data('confirm') || tlp_ajax.confirm_delete;
                
                if (!confirm(confirmMessage)) {
                    return false;
                }
                
                $.ajax({
                    url: tlp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tlp_delete_link',
                        link_id: linkId,
                        nonce: tlp_ajax.nonce
                    },
                    beforeSend: function() {
                        // Add loading state
                        row.addClass('tlp-loading');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove the row with animation
                            row.fadeOut(300, function() {
                                row.remove();
                            });
                            
                            // Show success message
                            TLPAdmin.showNotification(response.data.message, 'success');
                        } else {
                            // Show error message
                            TLPAdmin.showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        row.removeClass('tlp-loading');
                        
                        // Show error message
                        TLPAdmin.showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            });
            
            // AJAX deactivate link
            $(document).on('click', '.tlp-ajax-deactivate', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                var confirmMessage = $(this).data('confirm') || tlp_ajax.confirm_deactivate;
                
                if (!confirm(confirmMessage)) {
                    return false;
                }
                
                $.ajax({
                    url: tlp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tlp_deactivate_link',
                        link_id: linkId,
                        nonce: tlp_ajax.nonce
                    },
                    beforeSend: function() {
                        // Add loading state
                        $('#tlp-link-' + linkId).addClass('tlp-loading');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the status indicator
                            $('#tlp-link-' + linkId + ' .tlp-status').removeClass('tlp-status-active').addClass('tlp-status-inactive').text('Inactive');
                            
                            // Update the action buttons
                            $('#tlp-link-' + linkId + ' .tlp-deactivate-link').hide();
                            $('#tlp-link-' + linkId + ' .tlp-activate-link').show();
                            
                            // Remove loading state
                            $('#tlp-link-' + linkId).removeClass('tlp-loading');
                            
                            // Show success message
                            TLPAdmin.showNotification(response.data.message, 'success');
                        } else {
                            // Remove loading state
                            $('#tlp-link-' + linkId).removeClass('tlp-loading');
                            
                            // Show error message
                            TLPAdmin.showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        $('#tlp-link-' + linkId).removeClass('tlp-loading');
                        
                        // Show error message
                        TLPAdmin.showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            });
            
            // AJAX activate link
            $(document).on('click', '.tlp-ajax-activate', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                
                $.ajax({
                    url: tlp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tlp_activate_link',
                        link_id: linkId,
                        nonce: tlp_ajax.nonce
                    },
                    beforeSend: function() {
                        // Add loading state
                        $('#tlp-link-' + linkId).addClass('tlp-loading');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update the status indicator
                            $('#tlp-link-' + linkId + ' .tlp-status').removeClass('tlp-status-inactive').addClass('tlp-status-active').text('Active');
                            
                            // Update the action buttons
                            $('#tlp-link-' + linkId + ' .tlp-activate-link').hide();
                            $('#tlp-link-' + linkId + ' .tlp-deactivate-link').show();
                            
                            // Remove loading state
                            $('#tlp-link-' + linkId).removeClass('tlp-loading');
                            
                            // Show success message
                            TLPAdmin.showNotification(response.data.message, 'success');
                        } else {
                            // Remove loading state
                            $('#tlp-link-' + linkId).removeClass('tlp-loading');
                            
                            // Show error message
                            TLPAdmin.showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        $('#tlp-link-' + linkId).removeClass('tlp-loading');
                        
                        // Show error message
                        TLPAdmin.showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            });
            
            // AJAX extend link
            $(document).on('click', '.tlp-extend-link-submit', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                var duration = $('#tlp-extend-duration').val();
                
                $.ajax({
                    url: tlp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tlp_extend_link',
                        link_id: linkId,
                        duration: duration,
                        nonce: tlp_ajax.nonce
                    },
                    beforeSend: function() {
                        // Add loading state
                        $('.tlp-extend-link-submit').addClass('disabled').text('Extending...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            TLPAdmin.closeModal();
                            
                            // Show success message
                            TLPAdmin.showNotification(response.data.message, 'success');
                            
                            // Reload the page after a short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Remove loading state
                            $('.tlp-extend-link-submit').removeClass('disabled').text('Extend');
                            
                            // Show error message
                            TLPAdmin.showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        $('.tlp-extend-link-submit').removeClass('disabled').text('Extend');
                        
                        // Show error message
                        TLPAdmin.showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            });
            
            // AJAX resend email
            $(document).on('click', '.tlp-resend-email', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                
                $.ajax({
                    url: tlp_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tlp_resend_email',
                        link_id: linkId,
                        nonce: tlp_ajax.nonce
                    },
                    beforeSend: function() {
                        // Add loading state
                        $('.tlp-resend-email').addClass('disabled').text('Sending...');
                    },
                    success: function(response) {
                        if (response.success) {
                            // Remove loading state
                            $('.tlp-resend-email').removeClass('disabled').text('Resend Email');
                            
                            // Show success message
                            TLPAdmin.showNotification(response.data.message, 'success');
                        } else {
                            // Remove loading state
                            $('.tlp-resend-email').removeClass('disabled').text('Resend Email');
                            
                            // Show error message
                            TLPAdmin.showNotification(response.data.message, 'error');
                        }
                    },
                    error: function() {
                        // Remove loading state
                        $('.tlp-resend-email').removeClass('disabled').text('Resend Email');
                        
                        // Show error message
                        TLPAdmin.showNotification('An error occurred. Please try again.', 'error');
                    }
                });
            });
        },

        /**
         * Set up tooltips.
         */
        setupTooltips: function() {
            // Initialize tooltips
            $('.tlp-tooltip').hover(
                function() {
                    // On hover in
                    $(this).find('.tlp-tooltip-content').stop().fadeIn(200);
                },
                function() {
                    // On hover out
                    $(this).find('.tlp-tooltip-content').stop().fadeOut(200);
                }
            );
        },

        /**
         * Set up modal dialogs.
         */
        setupModalDialogs: function() {
            // Open modal dialog
            $(document).on('click', '.tlp-open-modal', function(e) {
                e.preventDefault();
                
                var target = $(this).data('target');
                
                // Show backdrop and modal
                $('.tlp-modal-backdrop').fadeIn(200);
                $('#' + target).fadeIn(200);
            });
            
            // Close modal dialog
            $(document).on('click', '.tlp-modal-close, .tlp-modal-cancel', function(e) {
                e.preventDefault();
                
                TLPAdmin.closeModal();
            });
            
            // Close modal when clicking on backdrop
            $(document).on('click', '.tlp-modal-backdrop', function() {
                TLPAdmin.closeModal();
            });
            
            // Prevent closing when clicking inside the modal
            $(document).on('click', '.tlp-modal', function(e) {
                e.stopPropagation();
            });
            
            // Open extend link modal
            $(document).on('click', '.tlp-extend-link', function(e) {
                e.preventDefault();
                
                var linkId = $(this).data('id');
                
                // Set the link ID in the modal
                $('.tlp-extend-link-submit').data('id', linkId);
                
                // Show backdrop and modal
                $('.tlp-modal-backdrop').fadeIn(200);
                $('#tlp-extend-modal').fadeIn(200);
            });
        },

        /**
         * Close modal dialog.
         */
        closeModal: function() {
            $('.tlp-modal-backdrop, .tlp-modal').fadeOut(200);
        },

        /**
         * Set up branding preview.
         */
        setupBrandingPreview: function() {
            // Update branding preview on page load
            TLPAdmin.updateBrandingPreview();
            
            // Update branding preview on input change
            $('.tlp-branding-setting').on('change input', function() {
                TLPAdmin.updateBrandingPreview();
            });
        },

        /**
         * Update branding preview.
         */
        updateBrandingPreview: function() {
            // Only update if preview exists
            if ($('.tlp-branding-preview').length === 0) {
                return;
            }
            
            // Get values
            var logoUrl = $('#login_logo').val();
            var backgroundColor = $('#login_background_color').val();
            var formBackground = $('#login_form_background').val();
            var formTextColor = $('#login_form_text_color').val();
            var buttonColor = $('#login_button_color').val();
            var buttonTextColor = $('#login_button_text_color').val();
            var welcomeText = $('#login_welcome_text').val();
            
            // Update preview
            if (logoUrl) {
                $('.tlp-branding-preview-logo').html('<img src="' + logoUrl + '" alt="Logo">');
            } else {
                $('.tlp-branding-preview-logo').html('<h2>' + $('#company_name').val() + '</h2>');
            }
            
            $('.tlp-branding-preview').css('background-color', backgroundColor);
            $('.tlp-branding-preview-form').css({
                'background-color': formBackground,
                'color': formTextColor
            });
            
            $('.tlp-branding-preview-form button').css({
                'background-color': buttonColor,
                'color': buttonTextColor
            });
            
            $('.tlp-branding-preview-welcome').html(welcomeText);
        },

        /**
         * Set up notification dismiss.
         */
        setupNotificationDismiss: function() {
            // Dismiss notification
            $(document).on('click', '.tlp-notification .tlp-dismiss', function() {
                $(this).closest('.tlp-notification').fadeOut(200, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show a notification.
         * 
         * @param {string} message The notification message.
         * @param {string} type    The notification type (success, error, warning).
         */
        showNotification: function(message, type) {
            var notificationHtml = '<div class="tlp-notification tlp-' + type + '">';
            notificationHtml += '<p>' + message + '</p>';
            notificationHtml += '<span class="tlp-dismiss dashicons dashicons-dismiss"></span>';
            notificationHtml += '</div>';
            
            // Remove existing notifications
            $('.tlp-notification').remove();
            
            // Add the new notification
            $('.tlp-wrap').prepend(notificationHtml);
            
            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                $('.tlp-notification').fadeOut(200, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

})(jQuery);