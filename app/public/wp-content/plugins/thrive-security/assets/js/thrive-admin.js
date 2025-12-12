jQuery(document).ready(function($) {
    'use strict';

    // Initialize modern UI components
    initModernUI();
    
    // Initialize tooltips and descriptions
    initTooltips();
    
    // Initialize toggle functionality
    initToggles();
    
    // Initialize form enhancements
    initFormEnhancements();
    
    // Initialize loading states
    initLoadingStates();

    /**
     * Initialize modern UI components
     */
    function initModernUI() {
        // Add smooth scrolling to all internal links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 600, 'easeInOutQuart');
            }
        });

        // Add hover effects to cards
        $('.thrive-card').hover(
            function() {
                $(this).addClass('thrive-card-hover');
            },
            function() {
                $(this).removeClass('thrive-card-hover');
            }
        );

        // Add focus effects to form elements
        $('.thrive-admin-wrap input, .thrive-admin-wrap select, .thrive-admin-wrap textarea').on('focus', function() {
            $(this).parent().addClass('thrive-input-focused');
        }).on('blur', function() {
            $(this).parent().removeClass('thrive-input-focused');
        });

        // Add ripple effect to buttons
        $('.thrive-admin-wrap .button').on('click', function(e) {
            const button = $(this);
            const ripple = $('<span class="thrive-ripple"></span>');
            
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.css({
                width: size,
                height: size,
                left: x,
                top: y
            });
            
            button.append(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    }

    /**
     * Initialize tooltips and descriptions
     */
    function initTooltips() {
        $('.thrive-description').each(function() {
            const $this = $(this);
            const text = $this.text().trim();
            
            if (text.length > 100) {
                $this.attr('title', text);
                $this.addClass('thrive-tooltip');
                
                // Create a custom tooltip
                $this.on('mouseenter', function() {
                    showCustomTooltip($this, text);
                }).on('mouseleave', function() {
                    hideCustomTooltip();
                });
            }
        });
    }

    /**
     * Show custom tooltip
     */
    function showCustomTooltip(element, text) {
        const tooltip = $('<div class="thrive-custom-tooltip"></div>')
            .text(text)
            .appendTo('body');
        
        const rect = element[0].getBoundingClientRect();
        tooltip.css({
            position: 'absolute',
            top: rect.top - tooltip.outerHeight() - 10,
            left: rect.left + (rect.width / 2) - (tooltip.outerWidth() / 2),
            zIndex: 10000
        });
        
        tooltip.fadeIn(200);
    }

    /**
     * Hide custom tooltip
     */
    function hideCustomTooltip() {
        $('.thrive-custom-tooltip').fadeOut(200, function() {
            $(this).remove();
        });
    }

    /**
     * Initialize toggle functionality
     */
    function initToggles() {
        // Toggle blocking module sections
        $("input[name='thrive_blocking_enabled']").on("change", function() {
            const $settingsContent = $("#thrive-settings-content");
            const $disabledNotice = $("#thrive-disabled-notice");
            
            if ($(this).is(":checked")) {
                $settingsContent.slideDown(300);
                $disabledNotice.slideUp(300);
            } else {
                $settingsContent.slideUp(300);
                $disabledNotice.slideDown(300);
            }
        });

        // Toggle cron job sections
        $("input[name='thrive_enable_config_refresh']").on("change", function() {
            $("#thrive-config-interval-section").slideToggle(300);
        });

        $("input[name='thrive_enable_block_plugins']").on("change", function() {
            $("#thrive-block-plugins-interval-section").slideToggle(300);
        });

        $("input[name='thrive_enable_install_plugins']").on("change", function() {
            $("#thrive-install-plugins-interval-section").slideToggle(300);
        });

        // Add smooth transitions to all toggles
        $('.thrive-toggle input').on('change', function() {
            const $toggle = $(this).closest('.thrive-toggle');
            $toggle.addClass('thrive-toggle-changing');
            
            setTimeout(() => {
                $toggle.removeClass('thrive-toggle-changing');
            }, 300);
        });
    }

    /**
     * Initialize form enhancements
     */
    function initFormEnhancements() {
        // Add character counter to text areas
        $('.thrive-admin-wrap textarea').each(function() {
            const $textarea = $(this);
            const maxLength = $textarea.attr('maxlength');
            
            if (maxLength) {
                const counter = $('<div class="thrive-char-counter"></div>');
                $textarea.after(counter);
                
                function updateCounter() {
                    const current = $textarea.val().length;
                    counter.text(`${current}/${maxLength}`);
                    
                    if (current > maxLength * 0.9) {
                        counter.addClass('thrive-char-counter-warning');
                    } else {
                        counter.removeClass('thrive-char-counter-warning');
                    }
                }
                
                $textarea.on('input', updateCounter);
                updateCounter();
            }
        });

        // Add validation feedback
        $('.thrive-admin-wrap form').on('submit', function(e) {
            const $form = $(this);
            const $submitBtn = $form.find('.thrive-submit-btn');
            
            // Add loading state
            $submitBtn.addClass('thrive-loading');
            $submitBtn.prop('disabled', true);
            
            // Simulate form processing (remove in production)
            setTimeout(() => {
                $submitBtn.removeClass('thrive-loading');
                $submitBtn.prop('disabled', false);
            }, 2000);
        });

        // Add auto-save functionality
        let autoSaveTimer;
        $('.thrive-admin-wrap input, .thrive-admin-wrap select, .thrive-admin-wrap textarea').on('input change', function() {
            clearTimeout(autoSaveTimer);
            
            autoSaveTimer = setTimeout(() => {
                showAutoSaveIndicator();
            }, 1000);
        });
    }

    /**
     * Show auto-save indicator
     */
    function showAutoSaveIndicator() {
        const indicator = $('<div class="thrive-auto-save-indicator">Saving...</div>');
        $('.thrive-admin-wrap').append(indicator);
        
        indicator.fadeIn(200);
        
        setTimeout(() => {
            indicator.fadeOut(200, function() {
                $(this).remove();
            });
        }, 2000);
    }

    /**
     * Initialize loading states
     */
    function initLoadingStates() {
        // Add loading overlay for AJAX requests
        $(document).on('ajaxStart', function() {
            showLoadingOverlay();
        }).on('ajaxStop', function() {
            hideLoadingOverlay();
        });

        // Add loading states to buttons
        $('.thrive-admin-wrap .button').on('click', function() {
            const $button = $(this);
            const originalText = $button.text();
            
            if (!$button.hasClass('thrive-loading')) {
                $button.addClass('thrive-loading');
                $button.text('Loading...');
                $button.prop('disabled', true);
                
                // Reset after 3 seconds (adjust as needed)
                setTimeout(() => {
                    $button.removeClass('thrive-loading');
                    $button.text(originalText);
                    $button.prop('disabled', false);
                }, 3000);
            }
        });
    }

    /**
     * Show loading overlay
     */
    function showLoadingOverlay() {
        if ($('.thrive-loading-overlay').length === 0) {
            const overlay = $(`
                <div class="thrive-loading-overlay">
                    <div class="thrive-loading-spinner"></div>
                    <div class="thrive-loading-text">Processing...</div>
                </div>
            `);
            $('body').append(overlay);
        }
    }

    /**
     * Hide loading overlay
     */
    function hideLoadingOverlay() {
        $('.thrive-loading-overlay').fadeOut(300, function() {
            $(this).remove();
        });
    }

    // Add custom easing for smooth animations
    $.easing.easeInOutQuart = function (x, t, b, c, d) {
        if ((t/=d/2) < 1) return c/2*t*t*t*t + b;
        return -c/2 * ((t-=2)*t*t*t - 2) + b;
    };

    // Add CSS for new components
    const additionalCSS = `
        <style>
            .thrive-card-hover {
                transform: translateY(-4px) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25) !important;
            }
            
            .thrive-input-focused {
                transform: scale(1.02);
            }
            
            .thrive-ripple {
                position: absolute;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transform: scale(0);
                animation: thrive-ripple 0.6s linear;
                pointer-events: none;
            }
            
            @keyframes thrive-ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
            
            .thrive-toggle-changing .thrive-toggle-slider {
                transform: scale(1.1);
            }
            
            .thrive-char-counter {
                font-size: 0.8rem;
                color: var(--thrive-gray-500);
                margin-top: 0.25rem;
                text-align: right;
            }
            
            .thrive-char-counter-warning {
                color: var(--thrive-warning);
                font-weight: 600;
            }
            
            .thrive-auto-save-indicator {
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--thrive-success);
                color: white;
                padding: 0.75rem 1.5rem;
                border-radius: var(--thrive-border-radius);
                box-shadow: var(--thrive-shadow-lg);
                z-index: 10000;
                display: none;
                font-weight: 600;
            }
            
            .thrive-custom-tooltip {
                background: var(--thrive-gray-800);
                color: white;
                padding: 0.75rem 1rem;
                border-radius: var(--thrive-border-radius);
                box-shadow: var(--thrive-shadow-lg);
                font-size: 0.9rem;
                max-width: 300px;
                line-height: 1.4;
                display: none;
            }
            
            .thrive-loading-text {
                color: white;
                margin-top: 1rem;
                font-weight: 600;
            }
        </style>
    `;
    
    $('head').append(additionalCSS);
}); 