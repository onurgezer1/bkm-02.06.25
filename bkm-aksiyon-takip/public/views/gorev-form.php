<?php
/**
 * Görev Formu View - Frontend
 * Bu dosya yeni görev ekleme/düzenleme formunu görüntüler
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
global $wpdb;

// Parametreler
$aksiyon_id = $atts['aksiyon_id'] ?: '';
$gorev_id = $atts['gorev_id'] ?: '';
$redirect_url = $atts['redirect_url'] ?: '';

// Düzenleme modunda ise mevcut görevi getir
$gorev = null;
if ($gorev_id) {
    $gorev = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}bkm_gorevler WHERE id = %d
    ", $gorev_id));
    
    if ($gorev) {
        $aksiyon_id = $gorev->aksiyon_id;
    }
}

// Aksiyon bilgisini getir
$aksiyon = null;
if ($aksiyon_id) {
    $aksiyon = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}bkm_aksiyonlar WHERE id = %d
    ", $aksiyon_id));
}

// Kullanıcıları getir (sadece editör ve yönetici)
$users = get_users(array(
    'role__in' => array('administrator', 'editor', 'contributor'),
    'orderby' => 'display_name'
));

// Durum seçenekleri
$durum_options = array(
    0 => 'Bekliyor',
    1 => 'Tamamlandı',
    2 => 'İptal Edildi'
);
?>

<div class="bkm-frontend bkm-task-form-page">
    <div class="bkm-container">
        
        <!-- Başlık -->
        <div class="bkm-page-header">
            <h2><?php echo $gorev ? 'Görev Düzenle' : 'Yeni Görev Ekle'; ?></h2>
            <?php if ($aksiyon): ?>
            <div class="bkm-page-subtitle">
                <strong>Aksiyon:</strong> <?php echo esc_html($aksiyon->aksiyon_adi); ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Form -->
        <div class="bkm-task-form">
            <form method="post" class="bkm-form">
                <?php wp_nonce_field('bkm_save_gorev', 'bkm_nonce'); ?>
                
                <?php if ($gorev_id): ?>
                <input type="hidden" name="gorev_id" value="<?php echo $gorev_id; ?>">
                <?php endif; ?>
                
                <?php if ($aksiyon_id): ?>
                <input type="hidden" name="aksiyon_id" value="<?php echo $aksiyon_id; ?>">
                <?php else: ?>
                
                <!-- Aksiyon Seçimi -->
                <div class="bkm-form-group">
                    <label for="aksiyon_id">Aksiyon Seçin <span class="bkm-required">*</span></label>
                    <select id="aksiyon_id" name="aksiyon_id" class="bkm-form-control bkm-select" required>
                        <option value="">Aksiyon seçin...</option>
                        <?php
                        $user_actions = $wpdb->get_results($wpdb->prepare("
                            SELECT id, aksiyon_adi 
                            FROM {$wpdb->prefix}bkm_aksiyonlar 
                            WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
                            ORDER BY aksiyon_adi
                        ", $current_user->ID, $current_user->ID));
                        
                        foreach ($user_actions as $action):
                        ?>
                        <option value="<?php echo $action->id; ?>" 
                                <?php selected($gorev ? $gorev->aksiyon_id : '', $action->id); ?>>
                            <?php echo esc_html($action->aksiyon_adi); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php endif; ?>
                
                <!-- Görev Adı -->
                <div class="bkm-form-group">
                    <label for="gorev_adi">Görev Adı <span class="bkm-required">*</span></label>
                    <input type="text" id="gorev_adi" name="gorev_adi" class="bkm-form-control" 
                           value="<?php echo $gorev ? esc_attr($gorev->gorev_adi) : ''; ?>" 
                           placeholder="Görev adını girin..." required maxlength="255">
                </div>
                
                <!-- Açıklama -->
                <div class="bkm-form-group">
                    <label for="aciklama">Açıklama</label>
                    <textarea id="aciklama" name="aciklama" class="bkm-form-control bkm-textarea" 
                              placeholder="Görev açıklamasını girin..." rows="4"><?php echo $gorev ? esc_textarea($gorev->aciklama) : ''; ?></textarea>
                </div>
                
                <!-- Form Satırı - Sorumlu ve Durum -->
                <div class="bkm-form-row">
                    <div class="bkm-form-col">
                        <label for="sorumlu_id">Sorumlu Kişi <span class="bkm-required">*</span></label>
                        <select id="sorumlu_id" name="sorumlu_id" class="bkm-form-control bkm-select" required>
                            <option value="">Sorumlu seçin...</option>
                            <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user->ID; ?>" 
                                    <?php selected($gorev ? $gorev->sorumlu_id : $current_user->ID, $user->ID); ?>>
                                <?php echo esc_html($user->display_name . ' (' . ucfirst($user->roles[0]) . ')'); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="bkm-form-col">
                        <label for="durum">Durum</label>
                        <select id="durum" name="durum" class="bkm-form-control bkm-select">
                            <?php foreach ($durum_options as $value => $label): ?>
                            <option value="<?php echo $value; ?>" 
                                    <?php selected($gorev ? $gorev->durum : 0, $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Form Satırı - Tarihler -->
                <div class="bkm-form-row">
                    <div class="bkm-form-col">
                        <label for="baslangic_tarihi">Başlangıç Tarihi</label>
                        <input type="date" id="baslangic_tarihi" name="baslangic_tarihi" class="bkm-form-control bkm-date-input"
                               value="<?php echo $gorev && $gorev->baslangic_tarihi ? date('Y-m-d', strtotime($gorev->baslangic_tarihi)) : ''; ?>">
                    </div>
                    
                    <div class="bkm-form-col">
                        <label for="bitis_tarihi">Bitiş Tarihi</label>
                        <input type="date" id="bitis_tarihi" name="bitis_tarihi" class="bkm-form-control bkm-date-input"
                               value="<?php echo $gorev && $gorev->bitis_tarihi ? date('Y-m-d', strtotime($gorev->bitis_tarihi)) : ''; ?>">
                    </div>
                </div>
                
                <!-- Form Butonları -->
                <div class="bkm-form-actions">
                    <input type="submit" value="<?php echo $gorev ? 'Görevi Güncelle' : 'Görevi Kaydet'; ?>" 
                           class="bkm-btn bkm-btn-primary bkm-btn-large">
                    
                    <?php if ($redirect_url): ?>
                    <a href="<?php echo esc_url($redirect_url); ?>" class="bkm-btn bkm-btn-secondary">İptal</a>
                    <?php else: ?>
                    <button type="button" onclick="history.back()" class="bkm-btn bkm-btn-secondary">İptal</button>
                    <?php endif; ?>
                </div>
                
            </form>
        </div>
        
        <!-- Yardım Bilgisi -->
        <div class="bkm-help-info">
            <h4><i class="dashicons dashicons-info"></i> Bilgi</h4>
            <ul>
                <li><strong>Görev Adı:</strong> Görevin kısa ve açıklayıcı adını girin.</li>
                <li><strong>Açıklama:</strong> Görevin detaylarını ve gereksinimlerini açıklayın.</li>
                <li><strong>Sorumlu Kişi:</strong> Görevi gerçekleştirecek kişiyi seçin.</li>
                <li><strong>Durum:</strong> Görevin mevcut durumunu belirtin.</li>
                <li><strong>Tarihler:</strong> Görevin başlangıç ve bitiş tarihlerini belirleyin (isteğe bağlı).</li>
            </ul>
        </div>
        
    </div>
</div>

<style>
/* Görev formu özel stiller */
.bkm-task-form-page .bkm-page-header {
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e5e9;
}

.bkm-page-subtitle {
    margin-top: 10px;
    color: #666;
    font-size: 16px;
}

.bkm-task-form {
    max-width: 800px;
    margin: 0 auto 30px auto;
}

.bkm-form {
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
}

.bkm-form-group {
    margin-bottom: 25px;
}

.bkm-form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.bkm-required {
    color: #dc3545;
}

.bkm-form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 16px;
    transition: all 0.3s ease;
    background: #fff;
    box-sizing: border-box;
}

.bkm-form-control:focus {
    outline: none;
    border-color: #007cba;
    box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
}

.bkm-textarea {
    resize: vertical;
    min-height: 100px;
    font-family: inherit;
    line-height: 1.5;
}

.bkm-select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
    cursor: pointer;
}

.bkm-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 25px;
}

.bkm-form-col {
    flex: 1;
}

.bkm-form-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
    padding-top: 25px;
    border-top: 1px solid #e1e5e9;
}

.bkm-btn-large {
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
}

.bkm-btn-secondary {
    background: #6c757d;
    color: #fff;
}

.bkm-btn-secondary:hover {
    background: #545b62;
}

.bkm-help-info {
    max-width: 800px;
    margin: 0 auto;
    background: #f8f9fa;
    border: 1px solid #e1e5e9;
    border-radius: 10px;
    padding: 25px;
}

.bkm-help-info h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.bkm-help-info ul {
    margin: 0;
    padding-left: 20px;
}

.bkm-help-info li {
    margin-bottom: 8px;
    color: #555;
    line-height: 1.5;
}

/* Form validation styles */
.bkm-form-control:invalid {
    border-color: #dc3545;
}

.bkm-form-control:valid {
    border-color: #28a745;
}

/* Loading state */
.bkm-form.loading .bkm-btn-primary {
    opacity: 0.7;
    cursor: not-allowed;
}

.bkm-form.loading .bkm-btn-primary:after {
    content: "";
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: bkm-spin 1s linear infinite;
    margin-left: 10px;
}

@media (max-width: 768px) {
    .bkm-task-form {
        margin: 0 10px 20px 10px;
    }
    
    .bkm-form {
        padding: 20px;
    }
    
    .bkm-form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .bkm-form-actions {
        flex-direction: column;
        align-items: stretch;
    }
    
    .bkm-help-info {
        margin: 0 10px;
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .bkm-form-actions {
        gap: 10px;
    }
    
    .bkm-btn-large {
        padding: 12px 20px;
        font-size: 14px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Form validation
    $('.bkm-form').on('submit', function(e) {
        const $form = $(this);
        const $submitBtn = $form.find('input[type="submit"]');
        
        // Basic validation
        let isValid = true;
        $form.find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).focus();
                isValid = false;
                return false;
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        $form.addClass('loading');
        $submitBtn.prop('disabled', true);
    });
    
    // Date validation
    $('#baslangic_tarihi, #bitis_tarihi').on('change', function() {
        const startDate = $('#baslangic_tarihi').val();
        const endDate = $('#bitis_tarihi').val();
        
        if (startDate && endDate && startDate > endDate) {
            alert('Bitiş tarihi başlangıç tarihinden önce olamaz.');
            $(this).val('');
        }
    });
});
</script>