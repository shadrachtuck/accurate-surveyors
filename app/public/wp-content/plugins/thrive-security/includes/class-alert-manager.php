<?php
defined('ABSPATH') || exit;

/**
 * Class THRIVE_SECURITY_ALERT_MANAGER
 *
 * Handles sending alert emails for blocked events.
 */
class THRIVE_SECURITY_ALERT_MANAGER {

    /**
     * Default email address to receive security alerts.
     */
    const DEFAULT_ALERT_EMAIL = 'dev@seoidaho.com';

    /**
     * Sends an HTML email alert with a subject and body.
     *
     * @param string $subject Email subject
     * @param string $body    HTML body of the email
     * @return void
     */
    public static function send(string $subject, string $body): void {
        try {
            $to = apply_filters('thrive_alert_email', self::DEFAULT_ALERT_EMAIL);
            
            // Validate email address
            if (!is_email($to)) {
                THRIVE_SECURITY_HELPER::maybe_debug_log('[Thrive] Invalid email address for alerts: ' . esc_html($to));
                return;
            }

            $headers = ['Content-Type: text/html; charset=UTF-8'];
            $subject = sprintf('[Thrive] %s', $subject);

            if (!wp_mail($to, $subject, $body, $headers)) {
                // Optional: extend this with logging fallback if needed
                THRIVE_SECURITY_HELPER::maybe_debug_log('[Thrive] Failed to send alert email to: ' . esc_html($to));
            }
        } catch (Exception $e) {
            THRIVE_SECURITY_HELPER::maybe_debug_log('[Thrive] Exception while sending alert email: ' . $e->getMessage());
        }
    }
}