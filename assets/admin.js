/**
 * CORS Manager Admin JavaScript
 * Enhances the admin interface with validation and user experience improvements
 */

(function($) {
    'use strict';
    
    // Initialize when document is ready
    $(document).ready(function() {
        initCORSManager();
    });
    
    function initCORSManager() {
        // Initialize all components
        initOriginValidation();
        initMethodToggle();
        initFormValidation();
        initHelpTooltips();
        initStatusUpdates();
    }
    
    /**
     * Origin validation and formatting
     */
    function initOriginValidation() {
        const $originsTextarea = $('textarea[name="cors_manager_options[allowed_origins]"]');
        
        if ($originsTextarea.length) {
            // Add validation on blur
            $originsTextarea.on('blur', function() {
                validateOrigins($(this));
            });
            
            // Add real-time feedback
            $originsTextarea.on('input', function() {
                clearTimeout($(this).data('timeout'));
                const $this = $(this);
                
                $this.data('timeout', setTimeout(function() {
                    validateOrigins($this);
                }, 1000));
            });
            
            // Add helper buttons
            addOriginHelpers($originsTextarea);
        }
    }
    
    /**
     * Validate origins format
     */
    function validateOrigins($textarea) {
        const origins = $textarea.val().split('\n').filter(line => line.trim());
        const validOrigins = [];
        const invalidOrigins = [];
        
        origins.forEach(function(origin) {
            origin = origin.trim();
            if (origin === '*') {
                validOrigins.push(origin);
            } else if (isValidOrigin(origin)) {
                validOrigins.push(origin);
            } else if (origin) {
                invalidOrigins.push(origin);
            }
        });
        
        // Update validation feedback
        updateOriginValidationFeedback($textarea, validOrigins, invalidOrigins);
    }
    
    /**
     * Check if origin is valid
     */
    function isValidOrigin(origin) {
        try {
            const url = new URL(origin);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }
    
    /**
     * Update validation feedback
     */
    function updateOriginValidationFeedback($textarea, validOrigins, invalidOrigins) {
        // Remove existing feedback
        $textarea.siblings('.cors-validation-feedback').remove();
        
        if (invalidOrigins.length > 0) {
            const $feedback = $('<div class="cors-validation-feedback cors-manager-error">');
            $feedback.html('<strong>Invalid origins detected:</strong><br>' + 
                          invalidOrigins.map(origin => '<code>' + escapeHtml(origin) + '</code>').join(', ') +
                          '<br><small>Origins must be valid URLs (e.g., https://example.com)</small>');
            $textarea.after($feedback);
        } else if (validOrigins.length > 0) {
            const $feedback = $('<div class="cors-validation-feedback cors-manager-success">');
            $feedback.html('<strong>✓ All origins are valid</strong> (' + validOrigins.length + ' origins)');
            $textarea.after($feedback);
        }
    }
    
    /**
     * Add helper buttons for origins
     */
    function addOriginHelpers($textarea) {
        const $helpers = $('<div class="cors-origin-helpers">');
        
        // Add common origins button
        const $addCommon = $('<button type="button" class="button button-secondary">Add Common Origins</button>');
        $addCommon.on('click', function() {
            const commonOrigins = [
                'https://localhost:3000',
                'https://localhost:8080',
                'https://127.0.0.1:3000'
            ];
            
            const currentValue = $textarea.val().trim();
            const newValue = currentValue ? currentValue + '\n' + commonOrigins.join('\n') : commonOrigins.join('\n');
            $textarea.val(newValue);
            validateOrigins($textarea);
        });
        
        // Clear all button
        const $clear = $('<button type="button" class="button button-secondary">Clear All</button>');
        $clear.on('click', function() {
            if (confirm('Are you sure you want to clear all origins?')) {
                $textarea.val('');
                $textarea.siblings('.cors-validation-feedback').remove();
            }
        });
        
        $helpers.append($addCommon).append(' ').append($clear);
        $textarea.after($helpers);
    }
    
    /**
     * Method toggle functionality
     */
    function initMethodToggle() {
        const $methodCheckboxes = $('input[name="cors_manager_options[allowed_methods][]"]');
        
        if ($methodCheckboxes.length) {
            // Add select all/none buttons
            const $methodContainer = $methodCheckboxes.first().closest('td');
            const $toggleButtons = $('<div class="cors-method-toggles">');
            
            const $selectAll = $('<button type="button" class="button button-secondary button-small">Select All</button>');
            $selectAll.on('click', function() {
                $methodCheckboxes.prop('checked', true);
            });
            
            const $selectNone = $('<button type="button" class="button button-secondary button-small">Select None</button>');
            $selectNone.on('click', function() {
                $methodCheckboxes.prop('checked', false);
            });
            
            const $selectCommon = $('<button type="button" class="button button-secondary button-small">Common Only</button>');
            $selectCommon.on('click', function() {
                $methodCheckboxes.prop('checked', false);
                $methodCheckboxes.filter('[value="GET"], [value="POST"], [value="OPTIONS"]').prop('checked', true);
            });
            
            $toggleButtons.append($selectAll).append(' ').append($selectNone).append(' ').append($selectCommon);
            $methodContainer.append($toggleButtons);
        }
    }
    
    /**
     * Form validation before submit
     */
    function initFormValidation() {
        $('form').on('submit', function(e) {
            const $form = $(this);
            const $corsEnabled = $('input[name="cors_manager_options[cors_enabled]"]');
            const $origins = $('textarea[name="cors_manager_options[allowed_origins]"]');
            const $methods = $('input[name="cors_manager_options[allowed_methods][]"]');
            
            // Check if CORS is enabled but no origins specified
            if ($corsEnabled.is(':checked') && !$origins.val().trim()) {
                if (!confirm('CORS is enabled but no origins are specified. This may block all cross-origin requests. Continue?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Check if CORS is enabled but no methods selected
            if ($corsEnabled.is(':checked') && $methods.filter(':checked').length === 0) {
                alert('Please select at least one HTTP method when CORS is enabled.');
                e.preventDefault();
                return false;
            }
            
            // Check for wildcard with credentials
            const $credentials = $('input[name="cors_manager_options[allow_credentials]"]');
            if ($corsEnabled.is(':checked') && $credentials.is(':checked') && $origins.val().includes('*')) {
                if (!confirm('Warning: Using wildcard (*) origins with credentials can be a security risk. Continue?')) {
                    e.preventDefault();
                    return false;
                }
            }
            
            // Show loading state
            $form.addClass('cors-manager-loading');
            $form.find('input[type="submit"]').prop('disabled', true).val('Saving...');
        });
    }
    
    /**
     * Help tooltips
     */
    function initHelpTooltips() {
        // Add tooltips to form fields
        const tooltips = {
            'cors_enabled': 'Enable this to add CORS headers to your WordPress responses.',
            'allowed_origins': 'Specify which domains can make cross-origin requests to your site.',
            'allowed_methods': 'Select which HTTP methods are allowed for cross-origin requests.',
            'allowed_headers': 'Specify which headers can be sent in cross-origin requests.',
            'allow_credentials': 'Allow cookies and authorization headers in cross-origin requests.'
        };
        
        Object.keys(tooltips).forEach(function(fieldName) {
            const $field = $('[name*="' + fieldName + '"]').first();
            const $label = $field.closest('tr').find('th');
            
            if ($label.length) {
                const $tooltip = $('<span class="cors-tooltip" title="' + tooltips[fieldName] + '">?</span>');
                $label.append(' ').append($tooltip);
            }
        });
        
        // Initialize tooltip behavior
        $('.cors-tooltip').on('mouseenter', function() {
            const $this = $(this);
            const title = $this.attr('title');
            
            const $tooltipDiv = $('<div class="cors-tooltip-content">' + title + '</div>');
            $('body').append($tooltipDiv);
            
            const offset = $this.offset();
            $tooltipDiv.css({
                position: 'absolute',
                top: offset.top - $tooltipDiv.outerHeight() - 5,
                left: offset.left - ($tooltipDiv.outerWidth() / 2) + ($this.outerWidth() / 2),
                zIndex: 9999
            });
            
            $this.data('tooltip', $tooltipDiv);
        }).on('mouseleave', function() {
            const $tooltip = $(this).data('tooltip');
            if ($tooltip) {
                $tooltip.remove();
            }
        });
    }
    
    /**
     * Status updates
     */
    function initStatusUpdates() {
        // Auto-refresh status when settings change
        $('input[name="cors_manager_options[cors_enabled]"]').on('change', function() {
            const $statusSection = $('.cors-manager-info');
            if ($(this).is(':checked')) {
                $statusSection.find('.notice-warning').removeClass('notice-warning').addClass('notice-info')
                    .find('p').text('CORS will be enabled after saving settings');
            } else {
                $statusSection.find('.notice-info, .notice-success').removeClass('notice-info notice-success').addClass('notice-warning')
                    .find('p').text('CORS will be disabled after saving settings');
            }
        });
    }
    
    /**
     * Utility function to escape HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) {
            return map[m];
        });
    }
    
})(jQuery);

// Add CSS for tooltips and helpers
jQuery(document).ready(function($) {
    const css = `
        <style>
        .cors-origin-helpers {
            margin-top: 10px;
        }
        
        .cors-method-toggles {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
        
        .cors-tooltip {
            display: inline-block;
            width: 16px;
            height: 16px;
            background: #0073aa;
            color: white;
            border-radius: 50%;
            text-align: center;
            font-size: 12px;
            line-height: 16px;
            cursor: help;
            margin-left: 5px;
        }
        
        .cors-tooltip-content {
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            max-width: 250px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        
        .cors-validation-feedback {
            margin-top: 8px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
        }
        
        .button-small {
            font-size: 11px;
            height: 24px;
            line-height: 22px;
            padding: 0 8px;
        }
        </style>
    `;
    
    $('head').append(css);
});