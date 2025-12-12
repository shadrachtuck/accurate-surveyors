jQuery(document).ready(function($) {
    'use strict';

    // Initialize modern UI for dependencies page
    initDependenciesUI();
    
    // Initialize AJAX handlers
    initAjaxHandlers();
    
    // Initialize bulk actions
    initBulkActions();
    
    // Initialize notifications
    initNotifications();
    
    // Initialize search and filtering
    initSearchFiltering();
    
    // Initialize manual background run
    initManualBackgroundRun();

    /**
     * Initialize modern UI for dependencies page
     */
    function initDependenciesUI() {
        // Add smooth animations to table rows
        $('#thrive-dependencies table tbody tr').each(function(index) {
            $(this).css({
                'opacity': '0',
                'transform': 'translateY(20px)'
            });
            
            setTimeout(() => {
                $(this).animate({
                    'opacity': '1',
                    'transform': 'translateY(0)'
                }, 300 + (index * 50));
            }, 100);
        });

        // Add hover effects to table rows
        $('#thrive-dependencies table tbody tr').hover(
            function() {
                $(this).addClass('thrive-row-hover');
            },
            function() {
                $(this).removeClass('thrive-row-hover');
            }
        );

        // Add click effects to buttons
        $('#thrive-dependencies .button').on('click', function(e) {
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

        // Add focus effects to form elements
        $('#thrive-dependencies input, #thrive-dependencies select').on('focus', function() {
            $(this).parent().addClass('thrive-input-focused');
        }).on('blur', function() {
            $(this).parent().removeClass('thrive-input-focused');
        });

        // Add smooth scrolling to page anchors
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 600, 'easeInOutQuart');
            }
        });
    }

    /**
     * Initialize AJAX handlers
     */
    function initAjaxHandlers() {
        // Handle plugin installation
        $(document).on('click', '.thrive-install-plugin', function(e) {
            e.preventDefault();
            const button = $(this);
            const pluginData = button.data('plugin');
            
            if (!pluginData) return;
            
            // Show loading state
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Installing...');
            
            // Make AJAX request
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_force_plugin_action',
                    action_type: 'install',
                    slug: pluginData.slug,
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Plugin installed successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification('Installation failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Installation failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });

        // Handle plugin activation
        $(document).on('click', '.thrive-activate-plugin', function(e) {
            e.preventDefault();
            const button = $(this);
            const pluginSlug = button.data('slug');
            
            if (!pluginSlug) return;
            
            // Show loading state
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Activating...');
            
            // Make AJAX request
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_force_plugin_action',
                    action_type: 'activate',
                    slug: pluginSlug,
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Plugin activated successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification('Activation failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Activation failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });

        // Handle plugin deactivation
        $(document).on('click', '.thrive-deactivate-plugin', function(e) {
            e.preventDefault();
            const button = $(this);
            const pluginSlug = button.data('slug');
            
            if (!pluginSlug) return;
            
            if (!confirm('Are you sure you want to deactivate this plugin?')) {
                return;
            }
            
            // Show loading state
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Deactivating...');
            
            // Make AJAX request
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_force_plugin_action',
                    action_type: 'deactivate',
                    slug: pluginSlug,
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Plugin deactivated successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        showNotification('Deactivation failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Deactivation failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });

        // Add uninstall handler for plugins
        $(document).on('click', '.thrive-uninstall-plugin', function(e) {
            e.preventDefault();
            const button = $(this);
            const pluginSlug = button.data('slug');
            if (!pluginSlug) return;
            if (!confirm('Are you sure you want to uninstall this plugin? This will delete its files.')) return;
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Uninstalling...');
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_force_plugin_action',
                    action_type: 'delete',
                    slug: pluginSlug,
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Plugin uninstalled successfully!', 'success');
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        showNotification('Uninstall failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Uninstall failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });

        // Add force delete handler for blocked plugins
        $(document).on('click', '.thrive-force-delete-plugin', function(e) {
            e.preventDefault();
            const button = $(this);
            const pluginSlug = button.data('slug');
            if (!pluginSlug) return;
            if (!confirm('Are you sure you want to force delete this blocked plugin? This action cannot be undone.')) return;
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Deleting...');
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_force_plugin_action',
                    action_type: 'delete',
                    slug: pluginSlug,
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Plugin force deleted successfully!', 'success');
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        showNotification('Force delete failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Force delete failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });
    }

    /**
     * Initialize bulk actions
     */
    function initBulkActions() {
        // Handle bulk install for required plugins
        $('#thrive-bulk-install').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const selectedPlugins = $('.thrive-required-row .thrive-plugin-checkbox:checked').map(function() {
                return $(this).data('plugin');
            }).get();
            
            if (selectedPlugins.length === 0) {
                showNotification('Please select plugins to install', 'warning');
                return;
            }
            
            performBulkAction(selectedPlugins, 'install', button, 'Installing...');
        });

        // Handle bulk install for recommended plugins
        $('#thrive-bulk-install-recommended').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const selectedPlugins = $('.thrive-recommended-row .thrive-plugin-checkbox:checked').map(function() {
                return $(this).data('plugin');
            }).get();
            
            if (selectedPlugins.length === 0) {
                showNotification('Please select plugins to install', 'warning');
                return;
            }
            
            performBulkAction(selectedPlugins, 'install', button, 'Installing...');
        });

        // Handle bulk activate for required plugins
        $('#thrive-bulk-activate').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const selectedPlugins = $('.thrive-required-row .thrive-plugin-checkbox:checked').map(function() {
                return $(this).data('plugin');
            }).get();
            
            if (selectedPlugins.length === 0) {
                showNotification('Please select plugins to activate', 'warning');
                return;
            }
            
            performBulkAction(selectedPlugins, 'activate', button, 'Activating...');
        });

        // Handle bulk activate for recommended plugins
        $('#thrive-bulk-activate-recommended').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const selectedPlugins = $('.thrive-recommended-row .thrive-plugin-checkbox:checked').map(function() {
                return $(this).data('plugin');
            }).get();
            
            if (selectedPlugins.length === 0) {
                showNotification('Please select plugins to activate', 'warning');
                return;
            }
            
            performBulkAction(selectedPlugins, 'activate', button, 'Activating...');
        });

        // Handle bulk delete for blocked plugins
        $('#thrive-bulk-delete-blocked').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            const selectedPlugins = $('.thrive-blocked-row .thrive-plugin-checkbox:checked').map(function() {
                return $(this).data('plugin');
            }).get();
            
            if (selectedPlugins.length === 0) {
                showNotification('Please select plugins to delete', 'warning');
                return;
            }
            
            if (confirm('Are you sure you want to force delete the selected blocked plugins? This action cannot be undone.')) {
                performBulkAction(selectedPlugins, 'force_delete', button, 'Deleting...');
            }
        });

        // Handle select all checkboxes for each tab
        $('[id^="thrive-select-all-"]').on('change', function() {
            const isChecked = $(this).is(':checked');
            const tabType = $(this).attr('id').replace('thrive-select-all-', '');
            $(`.thrive-${tabType}-row .thrive-plugin-checkbox`).prop('checked', isChecked);
            updateBulkActionsVisibility();
        });

        // Handle individual checkboxes
        $(document).on('change', '.thrive-plugin-checkbox', function() {
            updateBulkActionsVisibility();
        });
    }

    /**
     * Perform bulk action
     */
    function performBulkAction(plugins, action, button, loadingText) {
        // Show loading state
        button.addClass('thrive-loading');
        button.prop('disabled', true);
        const originalText = button.text();
        button.text(loadingText);
        
        // Make AJAX request
        $.ajax({
            url: ThrivePluginAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'thrive_bulk_plugin_action',
                plugins: plugins,
                bulk_action: action,
                nonce: ThrivePluginAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const actionText = action === 'install' ? 'installed' : 
                                     action === 'activate' ? 'activated' : 
                                     action === 'force_delete' ? 'deleted' : 'processed';
                    showNotification(`${plugins.length} plugins ${actionText} successfully!`, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification('Bulk action failed: ' + (response.data?.message || 'Unknown error'), 'error');
                }
            },
            error: function() {
                showNotification('Bulk action failed: Network error', 'error');
            },
            complete: function() {
                button.removeClass('thrive-loading');
                button.prop('disabled', false);
                button.text(originalText);
            }
        });
    }

    /**
     * Initialize manual background run
     */
    function initManualBackgroundRun() {
        $('#thrive-manual-background-run').on('click', function(e) {
            e.preventDefault();
            const button = $(this);
            
            // Show loading state
            button.addClass('thrive-loading');
            button.prop('disabled', true);
            const originalText = button.text();
            button.text('Running...');
            
            // Make AJAX request
            $.ajax({
                url: ThrivePluginAjax.ajax_url,
                type: 'POST',
                data: {
                    action: 'thrive_manual_background_run',
                    nonce: ThrivePluginAjax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Background process completed successfully!', 'success');
                        setTimeout(() => {
                            location.reload();
                        }, 2000);
                    } else {
                        showNotification('Background process failed: ' + (response.data?.message || 'Unknown error'), 'error');
                    }
                },
                error: function() {
                    showNotification('Background process failed: Network error', 'error');
                },
                complete: function() {
                    button.removeClass('thrive-loading');
                    button.prop('disabled', false);
                    button.text(originalText);
                }
            });
        });


    }

    /**
     * Update bulk actions visibility
     */
    function updateBulkActionsVisibility() {
        const checkedCount = $('.thrive-plugin-checkbox:checked').length;
        const bulkActions = $('.thrive-bulk-actions');
        
        if (checkedCount > 0) {
            bulkActions.slideDown(300);
            $('#thrive-bulk-install').text(`Install ${checkedCount} Plugin${checkedCount > 1 ? 's' : ''}`);
        } else {
            bulkActions.slideUp(300);
        }
    }

    /**
     * Initialize notifications
     */
    function initNotifications() {
        // Create notification container
        if ($('#thrive-notifications').length === 0) {
            $('body').append('<div id="thrive-notifications"></div>');
        }
    }

    /**
     * Show notification
     */
    function showNotification(message, type = 'info') {
        const notification = $(`
            <div class="thrive-notification thrive-notification-${type}">
                <div class="thrive-notification-content">
                    <span class="thrive-notification-message">${message}</span>
                    <button class="thrive-notification-close">&times;</button>
                </div>
            </div>
        `);
        
        $('#thrive-notifications').append(notification);
        
        // Animate in
        notification.css({
            'opacity': '0',
            'transform': 'translateX(100%)'
        }).animate({
            'opacity': '1',
            'transform': 'translateX(0)'
        }, 300);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            hideNotification(notification);
        }, 5000);
        
        // Close button
        notification.find('.thrive-notification-close').on('click', function() {
            hideNotification(notification);
        });
    }

    /**
     * Hide notification
     */
    function hideNotification(notification) {
        notification.animate({
            'opacity': '0',
            'transform': 'translateX(100%)'
        }, 300, function() {
            $(this).remove();
        });
    }

    /**
     * Initialize search and filtering
     */
    function initSearchFiltering() {
        // Add search functionality
        $('#thrive-search').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            const rows = $('#thrive-dependencies table tbody tr');
            
            rows.each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            
            // Update row count
            const visibleRows = rows.filter(':visible').length;
            $('#thrive-row-count').text(visibleRows);
        });

        // Add filter functionality
        $('#thrive-status-filter').on('change', function() {
            const filterValue = $(this).val();
            const rows = $('#thrive-dependencies table tbody tr');
            
            if (filterValue === 'all') {
                rows.show();
            } else {
                rows.each(function() {
                    const status = $(this).find('.thrive-status-indicator').attr('class');
                    if (status && status.includes(filterValue)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
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
            .thrive-row-hover {
                background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%) !important;
                transform: scale(1.01);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            
            #thrive-notifications {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                max-width: 400px;
            }
            
            .thrive-notification {
                background: white;
                border-radius: var(--thrive-border-radius);
                box-shadow: var(--thrive-shadow-lg);
                margin-bottom: 10px;
                overflow: hidden;
                border-left: 4px solid var(--thrive-primary);
            }
            
            .thrive-notification-success {
                border-left-color: var(--thrive-success);
            }
            
            .thrive-notification-error {
                border-left-color: var(--thrive-danger);
            }
            
            .thrive-notification-warning {
                border-left-color: var(--thrive-warning);
            }
            
            .thrive-notification-content {
                padding: 1rem;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            
            .thrive-notification-message {
                flex: 1;
                margin-right: 1rem;
                font-weight: 500;
            }
            
            .thrive-notification-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--thrive-gray-500);
                padding: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: var(--thrive-transition);
            }
            
            .thrive-notification-close:hover {
                background: var(--thrive-gray-200);
                color: var(--thrive-gray-700);
            }
            
            .thrive-bulk-actions {
                display: none;
            }
            
            .thrive-search-container {
                margin-bottom: 2rem;
                display: flex;
                gap: 1rem;
                align-items: center;
                flex-wrap: wrap;
            }
            
            .thrive-search-container input,
            .thrive-search-container select {
                padding: 0.75rem 1rem;
                border: 2px solid var(--thrive-gray-200);
                border-radius: var(--thrive-border-radius);
                font-size: 0.95rem;
                transition: var(--thrive-transition);
            }
            
            .thrive-search-container input:focus,
            .thrive-search-container select:focus {
                outline: none;
                border-color: var(--thrive-primary);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
            }
            
            .thrive-row-count {
                font-size: 0.9rem;
                color: var(--thrive-gray-600);
                font-weight: 500;
            }
            
            .thrive-required-status {
                color: #EF4444;
                font-weight: 600;
                font-size: 0.9rem;
                padding: 4px 8px;
                background: rgba(239, 68, 68, 0.1);
                border-radius: 4px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }
            
            .thrive-loading {
                opacity: 0.7;
                pointer-events: none;
                position: relative;
            }
            
            .thrive-loading::after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 16px;
                height: 16px;
                margin: -8px 0 0 -8px;
                border: 2px solid transparent;
                border-top: 2px solid currentColor;
                border-radius: 50%;
                animation: thrive-spin 1s linear infinite;
            }
            
            @keyframes thrive-spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    
    $('head').append(additionalCSS);
});