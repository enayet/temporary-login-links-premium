/**
 * Public JavaScript for Temporary Login Links Premium.
 *
 * This file provides the interactive functionality for public-facing
 * features, including form enhancements, copy functionality, and
 * countdown timers for temporary access.
 *
 * @package    Temporary_Login_Links_Premium
 * @subpackage Temporary_Login_Links_Premium/public/js
 */

(function($) {
    'use strict';

    /**
     * All of the code for the public-facing JavaScript
     * should reside in this file.
     */

    // Initialize when document is ready
    $(document).ready(function() {
        // Initialize all modules
        TLPPublic.init();
    });

    /**
     * TLP Public Class
     */
    var TLPPublic = {
        /**
         * Initialize the public functionality.
         */
        init: function() {
            this.setupLoginPage();
            this.setupCopyFunctionality();
            this.setupExpiryCountdowns();
            this.setupForms();
            this.setupNotifications();
        },

        /**
         * Set up the temporary login page enhancements.
         */
        setupLoginPage: function() {
            // Only run on login page with temp_login parameter
            if (this.isTemporaryLoginPage()) {
                // Add temporary-login class to body
                $('body').addClass('temporary-login');
                
                // Focus username field
                $('#user_login').focus();
                
                // Hide password field and related elements
                $('#user_pass, label[for="user_pass"]').hide();
                
                // Change button text
                $('#wp-submit').val(tlp_public_vars.access_site_text);
                
                // Hide "remember me" checkbox
                $('.forgetmenot').hide();
                
                // Hide nav links (lost password, back to site)
                $('#nav, #backtoblog').hide();
                
                // Add loading indicator
                if (tlp_public_vars.auto_login == '1') {
                    this.setupAutoLogin();
                }
            }
        },

        /**
         * Set up auto-login functionality.
         */
        setupAutoLogin: function() {
            // Add loading indicator
            $('#loginform').append('<div class="tlp-loading-indicator">' + tlp_public_vars.auto_login_text + '</div>');
            
            // Auto-submit the form after a short delay
            setTimeout(function() {
                $('#loginform').submit();
            }, 1500);
        },

        /**
         * Check if current page is a temporary login page.
         *
         * @return {boolean} True if page is a temporary login page.
         */
        isTemporaryLoginPage: function() {
            return typeof tlp_public_vars !== 'undefined' && 
                   tlp_public_vars.is_temp_login == '1';
        },

        /**
         * Set up copy functionality for links and tokens.
         */
        setupCopyFunctionality: function() {
            // Handle copy button clicks
            $(document).on('click', '.tlp-copy-link, .tlp-copy-link-btn', function(e) {
                e.preventDefault();
                
                var $button = $(this);
                var textToCopy = $button.data('clipboard-text');
                var originalText = $button.text();
                
                // Create a temporary input element
                var $tempInput = $('<input>');
                $('body').append($tempInput);
                $tempInput.val(textToCopy).select();
                
                // Execute copy command
                var copySuccess = document.execCommand('copy');
                
                // Remove temporary element
                $tempInput.remove();
                
                if (copySuccess) {
                    // Change button text to indicate success
                    $button.text(tlp_public_vars.copied_text);
                    
                    // Reset button text after a delay
                    setTimeout(function() {
                        $button.text(originalText);
                    }, 2000);
                }
            });
            
            // Handle click on link input fields (select all text)
            $(document).on('click', '.tlp-link-input', function() {
                $(this).select();
            });
        },

        /**
         * Set up expiry countdown timers.
         */
        setupExpiryCountdowns: function() {
            // Update all countdown elements
            $('.tlp-countdown').each(function() {
                var $countdown = $(this);
                var expiryTime = parseInt($countdown.data('expiry'), 10) * 1000; // Convert to milliseconds
                
                // Skip if expiry time is invalid
                if (isNaN(expiryTime)) {
                    return;
                }
                
                // Update the countdown immediately
                TLPPublic.updateCountdown($countdown, expiryTime);
                
                // Update the countdown every minute
                setInterval(function() {
                    TLPPublic.updateCountdown($countdown, expiryTime);
                }, 60000); // Update every minute
            });
        },

        /**
         * Update a countdown element.
         *
         * @param {jQuery} $element   The countdown element.
         * @param {number} expiryTime The expiry timestamp in milliseconds.
         */
        updateCountdown: function($element, expiryTime) {
            var now = new Date().getTime();
            var timeRemaining = expiryTime - now;
            
            if (timeRemaining <= 0) {
                // Access has expired
                $element.html(tlp_public_vars.expired_text);
                $element.removeClass('tlp-countdown-active').addClass('tlp-countdown-expired');
                
                // Trigger expiry event
                $(document).trigger('tlp_access_expired');
                
                // Reload page if auto-reload is enabled
                if (tlp_public_vars.auto_reload_on_expiry == '1') {
                    location.reload();
                }
            } else {
                // Calculate days, hours, minutes
                var days = Math.floor(timeRemaining / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeRemaining % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                
                // Build the countdown text
                var countdownText = '';
                
                if (days > 0) {
                    countdownText += days + ' ' + (days === 1 ? tlp_public_vars.day_text : tlp_public_vars.days_text) + ' ';
                }
                
                if (hours > 0 || days > 0) {
                    countdownText += hours + ' ' + (hours === 1 ? tlp_public_vars.hour_text : tlp_public_vars.hours_text) + ' ';
                }
                
                countdownText += minutes + ' ' + (minutes === 1 ? tlp_public_vars.minute_text : tlp_public_vars.minutes_text);
                
                // Update the element
                $element.html(countdownText);
            }
        },

        /**
         * Set up form enhancements.
         */
        setupForms: function() {
            // Add form validation
            $('.tlp-shortcode-form form, .tlp-shortcode-login-form form').on('submit', function(e) {
                var $form = $(this);
                var $requiredFields = $form.find('[required]');
                var isValid = true;
                
                // Check each required field
                $requiredFields.each(function() {
                    var $field = $(this);
                    
                    if (!$field.val().trim()) {
                        isValid = false;
                        
                        // Add error class
                        $field.addClass('tlp-field-error');
                        
                        // Show error message if not exists
                        if ($field.parent().find('.tlp-field-error-message').length === 0) {
                            $field.after('<div class="tlp-field-error-message">' + tlp_public_vars.required_field_text + '</div>');
                        }
                    } else {
                        // Remove error class and message
                        $field.removeClass('tlp-field-error');
                        $field.parent().find('.tlp-field-error-message').remove();
                    }
                });
                
                // Validate email fields
                $form.find('input[type="email"]').each(function() {
                    var $field = $(this);
                    var value = $field.val().trim();
                    
                    if (value && !TLPPublic.isValidEmail(value)) {
                        isValid = false;
                        
                        // Add error class
                        $field.addClass('tlp-field-error');
                        
                        // Show error message if not exists
                        if ($field.parent().find('.tlp-field-error-message').length === 0) {
                            $field.after('<div class="tlp-field-error-message">' + tlp_public_vars.invalid_email_text + '</div>');
                        }
                    }
                });
                
                // Prevent submission if form is invalid
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    var $firstError = $form.find('.tlp-field-error').first();
                    if ($firstError.length) {
                        $('html, body').animate({
                            scrollTop: $firstError.offset().top - 100
                        }, 500);
                    }
                    
                    return false;
                }
                
                // Add loading state to submit button
                var $submitButton = $form.find('[type="submit"]');
                $submitButton.prop('disabled', true).addClass('tlp-button-loading');
                
                // Add loading text if defined
                if (tlp_public_vars.loading_text) {
                    $submitButton.data('original-text', $submitButton.val());
                    $submitButton.val(tlp_public_vars.loading_text);
                }
                
                // Allow form submission
                return true;
            });
            
            // Remove error class on input
            $(document).on('input', '.tlp-field-error', function() {
                var $field = $(this);
                $field.removeClass('tlp-field-error');
                $field.parent().find('.tlp-field-error-message').remove();
            });
            
            // Toggle expiry date field based on expiry type
            $('#tlp_expiry').on('change', function() {
                var $select = $(this);
                var $customField = $('.tlp-custom-expiry');
                
                if ($select.val() === 'custom') {
                    $customField.slideDown(200);
                } else {
                    $customField.slideUp(200);
                }
            });
            
            // Initialize any datepickers
            if ($.fn.datepicker) {
                $('.tlp-datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    minDate: 0
                });
            }
        },

        /**
         * Set up notifications.
         */
        setupNotifications: function() {
            // Make notifications dismissible
            $(document).on('click', '.tlp-message .tlp-dismiss', function() {
                $(this).parent('.tlp-message').fadeOut(300, function() {
                    $(this).remove();
                });
            });
            
            // Auto-hide success messages after a delay
            setTimeout(function() {
                $('.tlp-success').fadeOut(500, function() {
                    $(this).remove();
                });
            }, 5000);
            
            // Check for access expiry notifications
            if ($('.tlp-access-expiring').length > 0) {
                // Show expiry notification
                this.showAccessExpiryNotification();
            }
        },

        /**
         * Show access expiry notification.
         */
        showAccessExpiryNotification: function() {
            var $notification = $('.tlp-access-expiring');
            var expiryTime = parseInt($notification.data('expiry'), 10) * 1000; // Convert to milliseconds
            
            if (isNaN(expiryTime)) {
                return;
            }
            
            var now = new Date().getTime();
            var timeRemaining = expiryTime - now;
            
            // Only show notification if expiry is within 24 hours
            if (timeRemaining <= 24 * 60 * 60 * 1000 && timeRemaining > 0) {
                $notification.removeClass('tlp-hidden');
                
                // Calculate hours remaining
                var hoursRemaining = Math.floor(timeRemaining / (1000 * 60 * 60));
                var minutesRemaining = Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60));
                
                // Update notification text
                var timeText = '';
                
                if (hoursRemaining > 0) {
                    timeText += hoursRemaining + ' ' + (hoursRemaining === 1 ? tlp_public_vars.hour_text : tlp_public_vars.hours_text) + ' ';
                }
                
                timeText += minutesRemaining + ' ' + (minutesRemaining === 1 ? tlp_public_vars.minute_text : tlp_public_vars.minutes_text);
                
                // Insert time remaining
                $notification.find('.tlp-time-remaining').text(timeText);
            }
        },

        /**
         * Validate an email address.
         *
         * @param {string} email The email address to validate.
         * @return {boolean} True if the email is valid.
         */
        isValidEmail: function(email) {
            var emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            return emailRegex.test(email);
        }
    };

    /**
     * Expose some functions globally for theme developers.
     */
    window.TLPPublic = {
        /**
         * Get time remaining until expiry.
         *
         * @param {number} expiryTimestamp The expiry timestamp in seconds.
         * @return {object} Object with days, hours, minutes remaining.
         */
        getTimeRemaining: function(expiryTimestamp) {
            var now = Math.floor(new Date().getTime() / 1000);
            var timeRemaining = expiryTimestamp - now;
            
            if (timeRemaining <= 0) {
                return {
                    expired: true,
                    days: 0,
                    hours: 0,
                    minutes: 0,
                    seconds: 0
                };
            }
            
            return {
                expired: false,
                days: Math.floor(timeRemaining / (60 * 60 * 24)),
                hours: Math.floor((timeRemaining % (60 * 60 * 24)) / (60 * 60)),
                minutes: Math.floor((timeRemaining % (60 * 60)) / 60),
                seconds: Math.floor(timeRemaining % 60)
            };
        },
        
        /**
         * Format time remaining as a string.
         *
         * @param {object} timeObj Time object from getTimeRemaining.
         * @return {string} Formatted time string.
         */
        formatTimeRemaining: function(timeObj) {
            if (timeObj.expired) {
                return tlp_public_vars.expired_text;
            }
            
            var result = '';
            
            if (timeObj.days > 0) {
                result += timeObj.days + ' ' + (timeObj.days === 1 ? tlp_public_vars.day_text : tlp_public_vars.days_text) + ' ';
            }
            
            if (timeObj.hours > 0 || timeObj.days > 0) {
                result += timeObj.hours + ' ' + (timeObj.hours === 1 ? tlp_public_vars.hour_text : tlp_public_vars.hours_text) + ' ';
            }
            
            result += timeObj.minutes + ' ' + (timeObj.minutes === 1 ? tlp_public_vars.minute_text : tlp_public_vars.minutes_text);
            
            return result;
        },
        
        /**
         * Copy text to clipboard.
         *
         * @param {string} text The text to copy.
         * @return {boolean} True if copying was successful.
         */
        copyToClipboard: function(text) {
            var $tempInput = $('<input>');
            $('body').append($tempInput);
            $tempInput.val(text).select();
            
            var result = document.execCommand('copy');
            $tempInput.remove();
            
            return result;
        }
    };

})(jQuery);

/**
 * Fallback for environments without jQuery.
 */
if (typeof jQuery === 'undefined') {
    // Add a simplified version of the essential functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Handle auto-login on temporary login page
        var tempLoginParam = /[?&]temp_login=([^&#]*)/.exec(window.location.search);
        
        if (tempLoginParam && document.body.classList.contains('login')) {
            // Add temporary-login class to body
            document.body.classList.add('temporary-login');
            
            // Hide password field and related elements
            var passwordField = document.getElementById('user_pass');
            var passwordLabel = document.querySelector('label[for="user_pass"]');
            
            if (passwordField && passwordLabel) {
                passwordField.style.display = 'none';
                passwordLabel.style.display = 'none';
            }
            
            // Hide "remember me" checkbox
            var rememberMe = document.querySelector('.forgetmenot');
            if (rememberMe) {
                rememberMe.style.display = 'none';
            }
            
            // Change submit button text
            var submitButton = document.getElementById('wp-submit');
            if (submitButton) {
                submitButton.value = 'Access Site';
                
                // Auto-submit the form
                setTimeout(function() {
                    document.getElementById('loginform').submit();
                }, 1500);
            }
            
            // Add loading indicator
            var form = document.getElementById('loginform');
            if (form) {
                var loadingIndicator = document.createElement('div');
                loadingIndicator.className = 'tlp-loading-indicator';
                loadingIndicator.textContent = 'Logging in automatically...';
                form.appendChild(loadingIndicator);
            }
        }
        
        // Handle copy functionality
        var copyButtons = document.querySelectorAll('.tlp-copy-link, .tlp-copy-link-btn');
        
        for (var i = 0; i < copyButtons.length; i++) {
            copyButtons[i].addEventListener('click', function(e) {
                e.preventDefault();
                
                var button = this;
                var textToCopy = button.getAttribute('data-clipboard-text');
                var originalText = button.textContent;
                
                // Create temporary input element
                var tempInput = document.createElement('input');
                document.body.appendChild(tempInput);
                tempInput.value = textToCopy;
                tempInput.select();
                
                document.execCommand('copy');
                document.body.removeChild(tempInput);
                
                button.textContent = 'Copied!';
                
                setTimeout(function() {
                    button.textContent = originalText;
                }, 2000);
            });
        }
    });
}