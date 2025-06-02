jQuery(document).ready(function($) {
    'use strict';
    
    // Progress bar range input handler
    $('#ilerleme').on('input', function() {
        var value = $(this).val();
        $('#ilerleme-value').text(value + '%');
        $('#progress-preview').css('width', value + '%');
    });
    
    // Form validation
    $('#aksiyon-form').on('submit', function(e) {
        var isValid = true;
        var errorMessages = [];
        
        // Required field validation
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('error');
                var label = $(this).closest('tr').find('label').text().replace('*', '').trim();
                errorMessages.push(label + ' alanı zorunludur.');
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Date validation
        var acilmaTarihi = $('input[name="acilma_tarihi"]').val();
        var hedefTarih = $('input[name="hedef_tarih"]').val();
        var kapanmaTarihi = $('input[name="kapanma_tarihi"]').val();
        
        if (acilmaTarihi && hedefTarih && new Date(acilmaTarihi) > new Date(hedefTarih)) {
            isValid = false;
            errorMessages.push('Hedef tarih açılma tarihinden önce olamaz.');
        }
        
        if (hedefTarih && kapanmaTarihi && new Date(kapanmaTarihi) < new Date(hedefTarih)) {
            // Warning but don't prevent submission
            console.warn('Kapanma tarihi hedef tarihten önce');
        }
        
        if (!isValid) {
            e.preventDefault();
            showErrorMessages(errorMessages);
        }
    });
    
    // Show error messages
    function showErrorMessages(messages) {
        var errorHtml = '<div class="notice notice-error is-dismissible"><ul>';
        messages.forEach(function(message) {
            errorHtml += '<li>' + message + '</li>';
        });
        errorHtml += '</ul></div>';
        
        $('.wrap h1').after(errorHtml);
        
        // Auto dismiss after 5 seconds
        setTimeout(function() {
            $('.notice-error').fadeOut();
        }, 5000);
    }
    
    // Auto-dismiss notices
    $('.notice.is-dismissible').each(function() {
        var $notice = $(this);
        setTimeout(function() {
            $notice.fadeOut();
        }, 5000);
    });
    
    // Confirm delete actions
    $('.button-link-delete').on('click', function(e) {
        if (!confirm('Bu işlemi geri alamazsınız. Silmek istediğinizden emin misiniz?')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-calculate week number from date
    $('input[name="acilma_tarihi"]').on('change', function() {
        var date = new Date($(this).val());
        if (date && !isNaN(date)) {
            var weekNumber = getWeekNumber(date);
            $('input[name="hafta"]').val(weekNumber);
        }
    });
    
    // Get week number from date
    function getWeekNumber(d) {
        d = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
        d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
        var yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
        var weekNo = Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
        return weekNo;
    }
    
    // Enhanced table sorting (if needed in future)
    $('.wp-list-table th').on('click', function() {
        // Table sorting functionality can be added here
    });
    
    // AJAX form submissions for quick actions
    $('.quick-action').on('click', function(e) {
        e.preventDefault();
        var $button = $(this);
        var action = $button.data('action');
        var id = $button.data('id');
        
        $button.prop('disabled', true).text('İşleniyor...');
        
        $.ajax({
            url: bkm_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'bkm_quick_action',
                quick_action: action,
                item_id: id,
                nonce: bkm_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Hata: ' + response.data);
                    $button.prop('disabled', false).text($button.data('original-text'));
                }
            },
            error: function() {
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                $button.prop('disabled', false).text($button.data('original-text'));
            }
        });
    });
    
    // Store original button text for quick actions
    $('.quick-action').each(function() {
        $(this).data('original-text', $(this).text());
    });
    
    // Auto-save draft functionality (can be implemented later)
    var autoSaveTimer;
    $('#aksiyon-form input, #aksiyon-form textarea, #aksiyon-form select').on('change input', function() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(function() {
            // Auto-save draft logic can be implemented here
            console.log('Auto-save triggered');
        }, 5000);
    });
    
    // Character counter for textareas
    $('textarea').each(function() {
        var $textarea = $(this);
        var maxLength = $textarea.attr('maxlength');
        
        if (maxLength) {
            var $counter = $('<div class="char-counter"></div>');
            $textarea.after($counter);
            
            function updateCounter() {
                var remaining = maxLength - $textarea.val().length;
                $counter.text(remaining + ' karakter kaldı');
                
                if (remaining < 50) {
                    $counter.addClass('warning');
                } else {
                    $counter.removeClass('warning');
                }
            }
            
            $textarea.on('input', updateCounter);
            updateCounter();
        }
    });
    
    // Enhanced progress bar animations
    $('.progress-bar, .percentage-fill').each(function() {
        var $bar = $(this);
        var width = $bar.css('width');
        $bar.css('width', '0%');
        
        setTimeout(function() {
            $bar.css('width', width);
        }, 100);
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+S to save form
        if (e.ctrlKey && e.which === 83) {
            e.preventDefault();
            $('#aksiyon-form').submit();
        }
        
        // Esc to close modals or go back
        if (e.which === 27) {
            // Close any open modals or dialogs
        }
    });
    
    // Smooth scrolling for anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        var target = $($(this).attr('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 50
            }, 500);
        }
    });
    
    // Add loading states to buttons
    $('form').on('submit', function() {
        $(this).find('input[type="submit"], button[type="submit"]').each(function() {
            var $btn = $(this);
            $btn.data('original-text', $btn.val() || $btn.text());
            $btn.prop('disabled', true);
            
            if ($btn.is('input')) {
                $btn.val('Kaydediliyor...');
            } else {
                $btn.text('Kaydediliyor...');
            }
        });
    });
    
    // Initialize tooltips (if WordPress doesn't provide them)
    $('[title]').each(function() {
        var $element = $(this);
        var title = $element.attr('title');
        
        if (title) {
            $element.removeAttr('title');
            $element.on('mouseenter', function(e) {
                var $tooltip = $('<div class="bkm-tooltip">' + title + '</div>');
                $('body').append($tooltip);
                
                $tooltip.css({
                    position: 'absolute',
                    top: e.pageY + 10,
                    left: e.pageX + 10,
                    background: '#333',
                    color: '#fff',
                    padding: '5px 10px',
                    borderRadius: '3px',
                    fontSize: '12px',
                    zIndex: 1000
                });
            }).on('mouseleave', function() {
                $('.bkm-tooltip').remove();
            });
        }
    });
});
