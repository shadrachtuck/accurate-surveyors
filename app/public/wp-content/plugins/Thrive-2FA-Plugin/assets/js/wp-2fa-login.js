(function() {
    'use strict';
    
    document.addEventListener('DOMContentLoaded', function() {
        // Focus on OTP input field
        var otpInput = document.getElementById('wp_2fa_otp');
        if (otpInput) {
            otpInput.focus();
        }
        
        // Check if this is a resend (new code sent)
        var isResend = window.location.search.includes('resent=1');
        
        // If this is a resend, clear any stored timer state and reset form
        if (isResend) {
            clearTimerState();
            resetFormState();
        }
        
        // Add countdown timer for OTP expiration
        var timerElement = document.getElementById('wp-2fa-timer');
        if (timerElement) {
            var expirySeconds = parseInt(timerElement.getAttribute('data-expiry'), 10) || 600; // Default to 10 minutes
            
            // Ensure we have a valid expiry time
            if (expirySeconds <= 0) {
                expirySeconds = 600; // Default to 10 minutes if invalid
            }
            
            // Check if we have a stored timer state
            var storedTimerState = sessionStorage.getItem('wp_2fa_timer_state');
            var startTime = sessionStorage.getItem('wp_2fa_timer_start');
            var currentTime = Math.floor(Date.now() / 1000);
            
            if (storedTimerState && startTime && startTime !== 'null' && startTime !== 'undefined') {
                // Calculate elapsed time since timer started
                var elapsedTime = currentTime - parseInt(startTime);
                var remainingSeconds = Math.max(0, expirySeconds - elapsedTime);
                
                // If timer has already expired, show expired state
                if (remainingSeconds <= 0) {
                    showExpiredState();
                    return;
                }
                
                // Continue countdown from remaining time
                expirySeconds = remainingSeconds;
            } else {
                // Store initial timer state
                sessionStorage.setItem('wp_2fa_timer_state', 'active');
                sessionStorage.setItem('wp_2fa_timer_start', currentTime.toString());
            }
            
            // Start the countdown
            startCountdown(expirySeconds);
        }
        
        // Function to start countdown
        function startCountdown(seconds) {
            var countdownInterval = setInterval(function() {
                seconds--;
                
                if (seconds <= 0) {
                    clearInterval(countdownInterval);
                    showExpiredState();
                } else {
                    var minutes = Math.floor(seconds / 60);
                    var secs = seconds % 60;
                    var displayTime = minutes + ':' + (secs < 10 ? '0' : '') + secs;
                    
                    if (timerElement) {
                        timerElement.innerHTML = displayTime;
                    }
                }
            }, 1000);
        }
        
        // Function to show expired state
        function showExpiredState() {
            if (timerElement) {
                timerElement.innerHTML = 'Expired';
                timerElement.classList.add('wp-2fa-timer-expired');
            }
            
            // Show resend button
            var resendButton = document.getElementById('wp-2fa-resend');
            if (resendButton) {
                resendButton.style.display = 'inline-block';
                resendButton.style.float = 'right';
            }
            
            // Disable the verify button and input
            var submitButton = document.getElementById('wp-submit');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.classList.add('wp-2fa-button-disabled');
            }
            
            if (otpInput) {
                otpInput.disabled = true;
                otpInput.classList.add('wp-2fa-input-disabled');
            }
            
            // Show expiry message if not already shown
            if (!document.querySelector('.wp-2fa-error')) {
                var formElement = document.getElementById('wp_2fa_verification_form');
                if (formElement) {
                    var expiryMessage = document.createElement('div');
                    expiryMessage.className = 'wp-2fa-error';
                    expiryMessage.textContent = 'Your verification code has expired. Please request a new code.';
                    formElement.insertBefore(expiryMessage, formElement.firstChild);
                }
            }
        }
        
        // Function to reset form state (enable input and button)
        function resetFormState() {
            // Enable the verify button and input
            var submitButton = document.getElementById('wp-submit');
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.classList.remove('wp-2fa-button-disabled');
            }
            
            if (otpInput) {
                otpInput.disabled = false;
                otpInput.classList.remove('wp-2fa-input-disabled');
                otpInput.value = ''; // Clear the input
            }
            
            // Hide resend button initially
            var resendButton = document.getElementById('wp-2fa-resend');
            if (resendButton) {
                resendButton.style.display = 'none';
            }
            
            // Remove any existing error messages
            var errorMessages = document.querySelectorAll('.wp-2fa-error');
            errorMessages.forEach(function(error) {
                if (error.textContent.includes('expired')) {
                    error.remove();
                }
            });
            
            // Reset timer display
            if (timerElement) {
                timerElement.classList.remove('wp-2fa-timer-expired');
            }
        }
        
        // Auto-submit when all digits are entered
        if (otpInput) {
            otpInput.addEventListener('input', function() {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6) {
                    var form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }
            });
        }
        
        // Add functionality to resend button
        var resendButton = document.getElementById('wp-2fa-resend');
        if (resendButton) {
            resendButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Clear stored timer state when resending
                clearTimerState();
                window.location.href = this.href;
            });
        }
        
        // Clear timer state when form is submitted successfully
        var form = document.getElementById('wp_2fa_verification_form');
        if (form) {
            form.addEventListener('submit', function() {
                // Clear timer state on successful submission
                clearTimerState();
            });
        }
        
        // Clear timer state when going back to login
        var backToLoginLink = document.getElementById('wp-2fa-back-to-login');
        if (backToLoginLink) {
            backToLoginLink.addEventListener('click', function() {
                clearTimerState();
            });
        }
        
        // Function to clear timer state
        function clearTimerState() {
            sessionStorage.removeItem('wp_2fa_timer_state');
            sessionStorage.removeItem('wp_2fa_timer_start');
        }
    });
})();