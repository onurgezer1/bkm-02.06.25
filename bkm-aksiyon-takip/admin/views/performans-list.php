<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bkm_performanslar';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_performans_' . $_GET['id'])) {
        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
        echo '<div class="notice notice-success"><p>Performans başarıyla silindi.</p></div>';
    }
}

$performanslar = $wpdb->get_results("SELECT * FROM $table_name ORDER BY performans_adi");

$performans = null;
$is_edit = false;

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $performans = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
    $is_edit = true;
}
?>

<div class="wrap">
    <h1>Performans Yönetimi</h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p>Performans başarıyla kaydedildi.</p>
        </div>
    <?php endif; ?>

    <div class="bkm-admin-container">
        <div class="bkm-form-section">
            <h2><?php echo $is_edit ? 'Performans Düzenle' : 'Yeni Performans Ekle'; ?></h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('bkm_save_performans', 'bkm_nonce'); ?>
                <input type="hidden" name="action" value="bkm_performans_save">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="performans_id" value="<?php echo esc_attr($performans->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="performans_adi">Performans Adı *</label></th>
                        <td>
                            <input type="text" name="performans_adi" id="performans_adi" 
                                   value="<?php echo $is_edit ? esc_attr($performans->performans_adi) : ''; ?>" 
                                   required class="regular-text">
                            <p class="description">Örn: Mükemmel, İyi, Orta, Zayıf</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($is_edit ? 'Performans Güncelle' : 'Performans Ekle'); ?>
            </form>
            
            <?php if ($is_edit): ?>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=bkm-performanslar'); ?>" class="button">İptal</a>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="bkm-list-section">
            <h2>Mevcut Performanslar</h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Performans Adı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($performanslar)): ?>
                        <tr>
                            <td colspan="3">Henüz performans bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($performanslar as $perf): ?>
                            <tr>
                                <td><?php echo esc_html($perf->id); ?></td>
                                <td><?php echo esc_html($perf->performans_adi); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=bkm-performanslar&action=edit&id=' . $perf->id); ?>" 
                                       class="button button-small">Düzenle</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=bkm-performanslar&action=delete&id=' . $perf->id), 'delete_performans_' . $perf->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('Bu performansı silmek istediğinizden emin misiniz?')">Sil</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.bkm-admin-container {
    display: flex;
    gap: 30px;
    flex-wrap: wrap;
}
.bkm-form-section {
    flex: 1;
    min-width: 300px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}
.bkm-list-section {
    flex: 2;
    min-width: 400px;
}
</style>
