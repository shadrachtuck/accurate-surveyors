<?php
/**
 * Modern HTML Email Template for 2FA Verification
 * 
 * @param string $user_name The user's display name
 * @param string $site_name The site name
 * @param string $otp The verification code
 * @param int $expiry_minutes The expiry time in minutes
 * @return string The HTML email content
 */
function wp_2fa_get_email_template($user_name, $site_name, $otp, $expiry_minutes) {
    $logo_url = get_site_icon_url(96) ?: get_template_directory_uri() . '/assets/images/logo.png';
    $site_url = get_site_url();
    
    return '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }
        
        .logo {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            margin-bottom: 20px;
            background-color: rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .header-subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .email-body {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
        }
        
        .message {
            font-size: 16px;
            color: #4a5568;
            margin-bottom: 30px;
            line-height: 1.7;
        }
        
        .otp-container {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            margin: 30px 0;
        }
        
        .otp-label {
            font-size: 14px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .otp-code {
            font-size: 36px;
            font-weight: 700;
            color: #2d3748;
            letter-spacing: 8px;
            font-family: "Courier New", monospace;
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 2px dashed #cbd5e0;
            display: inline-block;
            min-width: 200px;
        }
        
        .expiry-info {
            font-size: 14px;
            color: #718096;
            margin-top: 15px;
            font-style: italic;
        }
        
        .security-notice {
            background-color: #fff5f5;
            border-left: 4px solid #f56565;
            padding: 20px;
            margin: 30px 0;
            border-radius: 0 8px 8px 0;
        }
        
        .security-notice h4 {
            color: #c53030;
            font-size: 16px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .security-notice p {
            color: #742a2a;
            font-size: 14px;
            margin: 0;
        }
        
        .footer {
            background-color: #f7fafc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer-text {
            font-size: 14px;
            color: #718096;
            margin-bottom: 15px;
        }
        
        .footer-links {
            margin-top: 15px;
        }
        
        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }
        
        .footer-links a:hover {
            text-decoration: underline;
        }
        
        /* Responsive design */
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            
            .email-header,
            .email-body,
            .footer {
                padding: 20px;
            }
            
            .header-title {
                font-size: 24px;
            }
            
            .otp-code {
                font-size: 28px;
                letter-spacing: 4px;
                padding: 15px;
            }
            
            .greeting {
                font-size: 16px;
            }
            
            .message {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="logo">
                ' . (get_site_icon_url(96) ? '<img src="' . esc_url($logo_url) . '" alt="' . esc_attr($site_name) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">' : substr($site_name, 0, 1)) . '
            </div>
            <h1 class="header-title">' . esc_html__('Verification Code', 'wp-2fa') . '</h1>
            <p class="header-subtitle">' . esc_html__('Secure access to your account', 'wp-2fa') . '</p>
        </div>
        
        <div class="email-body">
            <p class="greeting">' . sprintf(esc_html__('Hello %s,', 'wp-2fa'), esc_html($user_name)) . '</p>
            
            <p class="message">' . sprintf(esc_html__('We received a login request for your account on %s. To complete your login, please use the verification code below:', 'wp-2fa'), esc_html($site_name)) . '</p>
            
            <div class="otp-container">
                <div class="otp-label">' . esc_html__('Your Verification Code', 'wp-2fa') . '</div>
                <div class="otp-code">' . esc_html($otp) . '</div>
                <div class="expiry-info">' . sprintf(esc_html__('This code will expire in %d minutes', 'wp-2fa'), $expiry_minutes) . '</div>
            </div>
            
            <div class="security-notice">
                <h4>' . esc_html__('Security Notice', 'wp-2fa') . '</h4>
                <p>' . esc_html__('If you did not attempt to log in to your account, please contact your site administrator immediately and change your password.', 'wp-2fa') . '</p>
            </div>
            
            <p class="message">' . esc_html__('For your security, this code can only be used once and will expire automatically. Never share this code with anyone.', 'wp-2fa') . '</p>
        </div>
        
        <div class="footer">
            <p class="footer-text">' . sprintf(esc_html__('This email was sent from %s', 'wp-2fa'), esc_html($site_name)) . '</p>
            <div class="footer-links">
                <a href="' . esc_url($site_url) . '">' . esc_html__('Visit Website', 'wp-2fa') . '</a>
                <a href="' . esc_url(wp_login_url()) . '">' . esc_html__('Login Page', 'wp-2fa') . '</a>
            </div>
        </div>
    </div>
</body>
</html>';
} 