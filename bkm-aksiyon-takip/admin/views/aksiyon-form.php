<?php
if (!defined('WPINC')) {
    die;
}

$admin = new BKM_Aksiyon_Takip_Admin();
$kategoriler = $admin->get_kategoriler();
$performanslar = $admin->get_performanslar();
$users = $admin->get_wordpress_users();

$aksiyon = null;
$is_edit = false;

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
    $aksiyon = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
    $is_edit = true;
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? 'Aksiyon Düzenle' : 'Yeni Aksiyon Ekle'; ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="aksiyon-form">
        <?php wp_nonce_field('bkm_save_aksiyon', 'bkm_nonce'); ?>
        <input type="hidden" name="action" value="bkm_aksiyon_save">
        <?php if ($is_edit): ?>
            <input type="hidden" name="aksiyon_id" value="<?php echo esc_attr($aksiyon->id); ?>">
        <?php endif; ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="kullanici_id">Aksiyonu Tanımlayan *</label></th>
                <td>
                    <select name="kullanici_id" id="kullanici_id" required class="regular-text">
                        <option value="">Seçiniz...</option>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo esc_attr($user->ID); ?>" 
                                    <?php selected($is_edit ? $aksiyon->kullanici_id : '', $user->ID); ?>>
                                <?php echo esc_html($user->display_name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="onem_derecesi">Önem Derecesi *</label></th>
                <td>
                    <select name="onem_derecesi" id="onem_derecesi" required class="regular-text">
                        <option value="">Seçiniz...</option>
                        <option value="1" <?php selected($is_edit ? $aksiyon->onem_derecesi : '', 1); ?>>1 - Yüksek</option>
                        <option value="2" <?php selected($is_edit ? $aksiyon->onem_derecesi : '', 2); ?>>2 - Orta</option>
                        <option value="3" <?php selected($is_edit ? $aksiyon->onem_derecesi : '', 3); ?>>3 - Düşük</option>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="acilma_tarihi">Açılma Tarihi *</label></th>
                <td>
                    <input type="date" name="acilma_tarihi" id="acilma_tarihi" 
                           value="<?php echo $is_edit ? esc_attr($aksiyon->acilma_tarihi) : ''; ?>" 
                           required class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="hafta">Hafta</label></th>
                <td>
                    <input type="number" name="hafta" id="hafta" 
                           value="<?php echo $is_edit ? esc_attr($aksiyon->hafta) : ''; ?>" 
                           min="1" max="53" class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="kategori_id">Kategori *</label></th>
                <td>
                    <select name="kategori_id" id="kategori_id" required class="regular-text">
                        <option value="">Seçiniz...</option>
                        <?php foreach ($kategoriler as $kategori): ?>
                            <option value="<?php echo esc_attr($kategori->id); ?>" 
                                    <?php selected($is_edit ? $aksiyon->kategori_id : '', $kategori->id); ?>>
                                <?php echo esc_html($kategori->kategori_adi); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <a href="<?php echo admin_url('admin.php?page=bkm-kategoriler'); ?>" target="_blank">Yeni kategori ekle</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="sorumlular">Aksiyon Sorumlusu</label></th>
                <td>
                    <textarea name="sorumlular" id="sorumlular" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($aksiyon->sorumlular) : ''; ?></textarea>
                    <p class="description">Birden fazla sorumlu varsa virgül ile ayırın.</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="tespit_nedeni">Tespit Nedeni</label></th>
                <td>
                    <textarea name="tespit_nedeni" id="tespit_nedeni" rows="3" class="large-text"><?php echo $is_edit ? esc_textarea($aksiyon->tespit_nedeni) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="aciklama">Aksiyon Açıklaması *</label></th>
                <td>
                    <textarea name="aciklama" id="aciklama" rows="5" class="large-text" required><?php echo $is_edit ? esc_textarea($aksiyon->aciklama) : ''; ?></textarea>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="hedef_tarih">Hedef Tarih</label></th>
                <td>
                    <input type="date" name="hedef_tarih" id="hedef_tarih" 
                           value="<?php echo $is_edit ? esc_attr($aksiyon->hedef_tarih) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="kapanma_tarihi">Kapanma Tarihi</label></th>
                <td>
                    <input type="date" name="kapanma_tarihi" id="kapanma_tarihi" 
                           value="<?php echo $is_edit ? esc_attr($aksiyon->kapanma_tarihi) : ''; ?>" 
                           class="regular-text">
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="performans_id">Performans</label></th>
                <td>
                    <select name="performans_id" id="performans_id" class="regular-text">
                        <option value="">Seçiniz...</option>
                        <?php foreach ($performanslar as $performans): ?>
                            <option value="<?php echo esc_attr($performans->id); ?>" 
                                    <?php selected($is_edit ? $aksiyon->performans_id : '', $performans->id); ?>>
                                <?php echo esc_html($performans->performans_adi); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <a href="<?php echo admin_url('admin.php?page=bkm-performanslar'); ?>" target="_blank">Yeni performans ekle</a>
                    </p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="ilerleme">İlerleme Durumu (%)</label></th>
                <td>
                    <input type="range" name="ilerleme" id="ilerleme" 
                           value="<?php echo $is_edit ? esc_attr($aksiyon->ilerleme) : '0'; ?>" 
                           min="0" max="100" step="5" class="regular-text">
                    <span id="ilerleme-value"><?php echo $is_edit ? esc_attr($aksiyon->ilerleme) : '0'; ?>%</span>
                    <div class="progress-preview">
                        <div class="progress-bar" id="progress-preview" style="width: <?php echo $is_edit ? esc_attr($aksiyon->ilerleme) : '0'; ?>%;"></div>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="notlar">Notlar</label></th>
                <td>
                    <textarea name="notlar" id="notlar" rows="5" class="large-text"><?php echo $is_edit ? esc_textarea($aksiyon->notlar) : ''; ?></textarea>
                </td>
            </tr>
        </table>
        
        <?php submit_button($is_edit ? 'Aksiyonu Güncelle' : 'Aksiyon Ekle'); ?>
    </form>
</div>

<script>
document.getElementById('ilerleme').addEventListener('input', function() {
    const value = this.value;
    document.getElementById('ilerleme-value').textContent = value + '%';
    document.getElementById('progress-preview').style.width = value + '%';
});
</script>

<style>
.progress-preview {
    width: 200px;
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
    margin-top: 5px;
}
.progress-bar {
    height: 100%;
    background-color: #0073aa;
    transition: width 0.3s ease;
}
</style>
