<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bkm_kategoriler';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_kategori_' . $_GET['id'])) {
        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
        echo '<div class="notice notice-success"><p>Kategori başarıyla silindi.</p></div>';
    }
}

$kategoriler = $wpdb->get_results("SELECT * FROM $table_name ORDER BY kategori_adi");

$kategori = null;
$is_edit = false;

if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
    $kategori = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_GET['id'])));
    $is_edit = true;
}
?>

<div class="wrap">
    <h1>Kategori Yönetimi</h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p>Kategori başarıyla kaydedildi.</p>
        </div>
    <?php endif; ?>

    <div class="bkm-admin-container">
        <div class="bkm-form-section">
            <h2><?php echo $is_edit ? 'Kategori Düzenle' : 'Yeni Kategori Ekle'; ?></h2>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <?php wp_nonce_field('bkm_save_kategori', 'bkm_nonce'); ?>
                <input type="hidden" name="action" value="bkm_kategori_save">
                <?php if ($is_edit): ?>
                    <input type="hidden" name="kategori_id" value="<?php echo esc_attr($kategori->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="kategori_adi">Kategori Adı *</label></th>
                        <td>
                            <input type="text" name="kategori_adi" id="kategori_adi" 
                                   value="<?php echo $is_edit ? esc_attr($kategori->kategori_adi) : ''; ?>" 
                                   required class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button($is_edit ? 'Kategori Güncelle' : 'Kategori Ekle'); ?>
            </form>
            
            <?php if ($is_edit): ?>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=bkm-kategoriler'); ?>" class="button">İptal</a>
                </p>
            <?php endif; ?>
        </div>
        
        <div class="bkm-list-section">
            <h2>Mevcut Kategoriler</h2>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Kategori Adı</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategoriler)): ?>
                        <tr>
                            <td colspan="3">Henüz kategori bulunmuyor.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kategoriler as $kat): ?>
                            <tr>
                                <td><?php echo esc_html($kat->id); ?></td>
                                <td><?php echo esc_html($kat->kategori_adi); ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=bkm-kategoriler&action=edit&id=' . $kat->id); ?>" 
                                       class="button button-small">Düzenle</a>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=bkm-kategoriler&action=delete&id=' . $kat->id), 'delete_kategori_' . $kat->id); ?>" 
                                       class="button button-small button-link-delete" 
                                       onclick="return confirm('Bu kategoriyi silmek istediğinizden emin misiniz?')">Sil</a>
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
