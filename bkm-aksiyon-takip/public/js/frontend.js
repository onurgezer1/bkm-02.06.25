jQuery(document).ready(function($) {
    'use strict';
    
    const BKMFrontend = {
        
        // Initialization
        init: function() {
            this.bindEvents();
            this.initializeComponents();
        },
        
        // Event bindings
        bindEvents: function() {
            // Login form submission
            $(document).on('submit', '.bkm-login-form form', this.handleLogin);
            
            // Logout button
            $(document).on('click', '.bkm-logout-btn', this.handleLogout);
            
            // Task form submission
            $(document).on('submit', '.bkm-task-form form', this.handleTaskSave);
            
            // Task deletion
            $(document).on('click', '.bkm-delete-task', this.handleTaskDelete);
            
            // Progress update
            $(document).on('change', '.bkm-progress-input', this.handleProgressUpdate);
            
            // Action completion
            $(document).on('click', '.bkm-complete-action', this.handleActionComplete);
            
            // Action deletion
            $(document).on('click', '.bkm-delete-action', this.handleActionDelete);
            
            // Filter changes
            $(document).on('change', '.bkm-action-filters select', this.handleFilterChange);
            
            // Search functionality
            $(document).on('input', '.bkm-search-input', this.debounce(this.handleSearch, 300));
        },
        
        // Initialize components
        initializeComponents: function() {
            this.initDatePickers();
            this.initTooltips();
            this.loadDashboardData();
        },
        
        // Handle login form submission
        handleLogin: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('input[type="submit"]');
            const originalText = $submitBtn.val();
            
            // Show loading state
            $submitBtn.val('Giriş yapılıyor...').prop('disabled', true);
            
            const formData = {
                action: 'bkm_frontend_login',
                nonce: bkm_frontend_ajax.nonce,
                username: $form.find('input[name="username"]').val(),
                password: $form.find('input[name="password"]').val()
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        
                        // Redirect if specified
                        const redirectUrl = $form.data('redirect-url');
                        if (redirectUrl) {
                            window.location.href = redirectUrl;
                        } else {
                            location.reload();
                        }
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Giriş sırasında bir hata oluştu.', 'error');
                })
                .always(function() {
                    $submitBtn.val(originalText).prop('disabled', false);
                });
        },
        
        // Handle logout
        handleLogout: function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const originalText = $btn.text();
            
            $btn.text('Çıkış yapılıyor...').prop('disabled', true);
            
            const formData = {
                action: 'bkm_frontend_logout',
                nonce: bkm_frontend_ajax.nonce
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        location.reload();
                    } else {
                        BKMFrontend.showNotice('Çıkış sırasında bir hata oluştu.', 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Çıkış sırasında bir hata oluştu.', 'error');
                })
                .always(function() {
                    $btn.text(originalText).prop('disabled', false);
                });
        },
        
        // Handle task form submission
        handleTaskSave: function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const $submitBtn = $form.find('input[type="submit"]');
            const originalText = $submitBtn.val();
            
            $submitBtn.val('Kaydediliyor...').prop('disabled', true);
            
            const formData = {
                action: 'bkm_save_gorev',
                nonce: bkm_frontend_ajax.nonce
            };
            
            // Add all form fields to formData
            $form.serializeArray().forEach(function(field) {
                formData[field.name] = field.value;
            });
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        $form[0].reset();
                        
                        // Refresh task list if visible
                        BKMFrontend.refreshTaskList();
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Görev kaydedilirken bir hata oluştu.', 'error');
                })
                .always(function() {
                    $submitBtn.val(originalText).prop('disabled', false);
                });
        },
        
        // Handle task deletion
        handleTaskDelete: function(e) {
            e.preventDefault();
            
            if (!confirm(bkm_frontend_ajax.messages.confirm_delete)) {
                return;
            }
            
            const $btn = $(this);
            const taskId = $btn.data('task-id');
            const $taskItem = $btn.closest('.bkm-task-item');
            
            $btn.prop('disabled', true);
            
            const formData = {
                action: 'bkm_delete_gorev',
                nonce: bkm_frontend_ajax.nonce,
                gorev_id: taskId
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        $taskItem.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Görev silinirken bir hata oluştu.', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        },
        
        // Handle progress update
        handleProgressUpdate: function() {
            const $input = $(this);
            const actionId = $input.data('action-id');
            const progress = $input.val();
            
            const formData = {
                action: 'bkm_update_aksiyon_progress',
                nonce: bkm_frontend_ajax.nonce,
                aksiyon_id: actionId,
                progress: progress
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        // Update progress bar
                        const $progressBar = $input.closest('.bkm-action-item').find('.bkm-progress-fill');
                        const $progressText = $input.closest('.bkm-action-item').find('.bkm-progress-text');
                        
                        $progressBar.css('width', progress + '%');
                        $progressText.text(progress + '%');
                        
                        BKMFrontend.showNotice('İlerleme güncellendi.', 'success');
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('İlerleme güncellenirken bir hata oluştu.', 'error');
                });
        },
        
        // Handle action completion
        handleActionComplete: function(e) {
            e.preventDefault();
            
            if (!confirm('Bu aksiyonu tamamlandı olarak işaretlemek istediğinizden emin misiniz?')) {
                return;
            }
            
            const $btn = $(this);
            const actionId = $btn.data('action-id');
            
            $btn.prop('disabled', true);
            
            const formData = {
                action: 'bkm_complete_action',
                nonce: bkm_frontend_ajax.nonce,
                aksiyon_id: actionId
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        
                        // Update UI to show completion
                        const $actionItem = $btn.closest('.bkm-action-item');
                        $actionItem.addClass('completed');
                        $actionItem.find('.bkm-progress-fill').css('width', '100%');
                        $actionItem.find('.bkm-progress-text').text('100%');
                        $btn.remove();
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Aksiyon tamamlanırken bir hata oluştu.', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        },
        
        // Handle action deletion
        handleActionDelete: function(e) {
            e.preventDefault();
            
            if (!confirm('Bu aksiyonu silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            
            const $btn = $(this);
            const actionId = $btn.data('action-id');
            const $actionItem = $btn.closest('.bkm-action-item');
            
            $btn.prop('disabled', true);
            
            const formData = {
                action: 'bkm_delete_action',
                nonce: bkm_frontend_ajax.nonce,
                aksiyon_id: actionId
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        BKMFrontend.showNotice(response.data.message, 'success');
                        $actionItem.fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        BKMFrontend.showNotice(response.data.message, 'error');
                    }
                })
                .fail(function() {
                    BKMFrontend.showNotice('Aksiyon silinirken bir hata oluştu.', 'error');
                })
                .always(function() {
                    $btn.prop('disabled', false);
                });
        },
        
        // Handle filter changes
        handleFilterChange: function() {
            BKMFrontend.refreshActionList();
        },
        
        // Handle search
        handleSearch: function() {
            BKMFrontend.refreshActionList();
        },
        
        // Refresh action list based on current filters
        refreshActionList: function() {
            const $container = $('.bkm-action-list-container');
            if ($container.length === 0) return;
            
            $container.html('<div class="bkm-loading-container"><div class="bkm-loading"></div></div>');
            
            // Collect filter values
            const filters = {};
            $('.bkm-action-filters select').each(function() {
                const $select = $(this);
                if ($select.val()) {
                    filters[$select.attr('name')] = $select.val();
                }
            });
            
            const searchTerm = $('.bkm-search-input').val();
            if (searchTerm) {
                filters.search = searchTerm;
            }
            
            // Make AJAX request to refresh list
            const formData = {
                action: 'bkm_get_filtered_actions',
                nonce: bkm_frontend_ajax.nonce,
                filters: filters
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        $container.html(response.data.html);
                    } else {
                        $container.html('<div class="bkm-notice bkm-notice-error">Aksiyonlar yüklenirken bir hata oluştu.</div>');
                    }
                })
                .fail(function() {
                    $container.html('<div class="bkm-notice bkm-notice-error">Aksiyonlar yüklenirken bir hata oluştu.</div>');
                });
        },
        
        // Refresh task list
        refreshTaskList: function() {
            const $container = $('.bkm-task-list-container');
            if ($container.length === 0) return;
            
            const actionId = $container.data('action-id');
            if (!actionId) return;
            
            const formData = {
                action: 'bkm_get_action_tasks',
                nonce: bkm_frontend_ajax.nonce,
                aksiyon_id: actionId
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        $container.html(response.data.html);
                    }
                });
        },
        
        // Load dashboard data
        loadDashboardData: function() {
            const $dashboard = $('.bkm-dashboard-stats');
            if ($dashboard.length === 0) return;
            
            const formData = {
                action: 'bkm_get_dashboard_stats',
                nonce: bkm_frontend_ajax.nonce
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData)
                .done(function(response) {
                    if (response.success) {
                        // Update stats with animation
                        $.each(response.data.stats, function(key, value) {
                            const $stat = $dashboard.find('[data-stat="' + key + '"]');
                            if ($stat.length) {
                                BKMFrontend.animateNumber($stat, value);
                            }
                        });
                    }
                });
        },
        
        // Initialize date pickers
        initDatePickers: function() {
            if ($.fn.datepicker) {
                $('.bkm-date-input').datepicker({
                    dateFormat: 'yy-mm-dd',
                    changeMonth: true,
                    changeYear: true
                });
            }
        },
        
        // Initialize tooltips
        initTooltips: function() {
            if ($.fn.tooltip) {
                $('[data-tooltip]').tooltip({
                    position: { my: "center bottom-20", at: "center top" }
                });
            }
        },
        
        // Show notification
        showNotice: function(message, type) {
            type = type || 'info';
            
            const $notice = $('<div class="bkm-notice bkm-notice-' + type + '">' + message + '</div>');
            
            // Remove existing notices
            $('.bkm-notice').fadeOut(300, function() {
                $(this).remove();
            });
            
            // Add new notice
            if ($('.bkm-notices').length) {
                $('.bkm-notices').prepend($notice);
            } else {
                $('body').prepend('<div class="bkm-notices"></div>');
                $('.bkm-notices').prepend($notice);
            }
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        },
        
        // Animate number
        animateNumber: function($element, targetValue) {
            const startValue = parseInt($element.text()) || 0;
            const duration = 1000;
            const startTime = Date.now();
            
            function updateNumber() {
                const elapsed = Date.now() - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
                $element.text(currentValue);
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                }
            }
            
            updateNumber();
        },
        
        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },
        
        // Log errors
        logError: function(error, context) {
            const formData = {
                action: 'bkm_log_error',
                nonce: bkm_frontend_ajax.nonce,
                error: error.toString(),
                context: context || 'unknown'
            };
            
            $.post(bkm_frontend_ajax.ajax_url, formData);
        }
    };
    
    // Global error handler
    window.addEventListener('error', function(e) {
        BKMFrontend.logError(e.error, 'global');
    });
    
    // Initialize the frontend
    BKMFrontend.init();
    
    // Make BKMFrontend globally available
    window.BKMFrontend = BKMFrontend;
});