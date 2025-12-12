(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Toggle user roles field based on enabled toggle
        var $enabledCheckbox = $('input[name="wp_2fa_enabled"]');
        var $userRolesRow = $('tr:has(th:contains("User Roles"))');
        var $otpExpiryRow = $('tr:has(th:contains("OTP Expiry"))');
        var $statusIndicator = $('.wp-2fa-status');
        
        function toggleFields() {
            if ($enabledCheckbox.is(':checked')) {
                $userRolesRow.slideDown(300);
                $otpExpiryRow.slideDown(300);
                $statusIndicator.removeClass('wp-2fa-status-disabled').addClass('wp-2fa-status-enabled').text('Enabled');
            } else {
                $userRolesRow.slideUp(300);
                $otpExpiryRow.slideUp(300);
                $statusIndicator.removeClass('wp-2fa-status-enabled').addClass('wp-2fa-status-disabled').text('Disabled');
            }
        }
        
        toggleFields();
        
        $enabledCheckbox.on('change', function() {
            toggleFields();
        });
        
        // Add animation to settings cards
        $('.wp-2fa-card').each(function(index) {
            $(this).css({
                'opacity': 0,
                'transform': 'translateY(20px)'
            });
            
            setTimeout(function() {
                $(this).css({
                    'transition': 'all 0.5s ease',
                    'opacity': 1,
                    'transform': 'translateY(0)'
                });
            }.bind(this), index * 100);
        });
        
        // Form validation
        $('.wp-2fa-settings-form').on('submit', function(e) {
            var $otpExpiry = $('input[name="wp_2fa_otp_expiry"]');
            var expiryValue = parseInt($otpExpiry.val(), 10);
            
            if (isNaN(expiryValue) || expiryValue < 1 || expiryValue > 60) {
                e.preventDefault();
                
                $otpExpiry.css('border-color', '#d63638');
                
                if (!$('.wp-2fa-error-message').length) {
                    $otpExpiry.after('<p class="wp-2fa-error-message" style="color: #d63638; margin-top: 5px;">Please enter a valid number between 1 and 60.</p>');
                }
                
                $('html, body').animate({
                    scrollTop: $otpExpiry.offset().top - 100
                }, 300);
            }
        });
        
        // Clear validation errors on input change
        $('input[name="wp_2fa_otp_expiry"]').on('input', function() {
            $(this).css('border-color', '');
            $('.wp-2fa-error-message').remove();
        });
    });
})(jQuery);