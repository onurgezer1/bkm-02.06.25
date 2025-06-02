<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$table_name = $wpdb->prefix . 'bkm_aksiyonlar';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_aksiyon_' . $_GET['id'])) {
        $wpdb->delete($table_name, array('id' => intval($_GET['id'])));
        echo '<div class="notice notice-success"><p>Aksiyon başarıyla silindi.</p></div>';
    }
}

$aksiyonlar = $wpdb->get_results("
    SELECT a.*, k.kategori_adi, p.performans_adi, u.display_name as kullanici_adi
    FROM $table_name a
    LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
    LEFT JOIN {$wpdb->prefix}bkm_performanslar p ON a.performans_id = p.id
    LEFT JOIN {$wpdb->users} u ON a.kullanici_id = u.ID
    ORDER BY a.sira_no DESC
");
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Tüm Aksiyonlar</h1>
    <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon-ekle'); ?>" class="page-title-action">Yeni Ekle</a>
    <hr class="wp-header-end">

    <?php if (isset($_GET['message']) && $_GET['message'] == 'saved'): ?>
        <div class="notice notice-success is-dismissible">
            <p>Aksiyon başarıyla kaydedildi.</p>
        </div>
    <?php endif; ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Sıra No</th>
                <th>Tanımlayan</th>
                <th>Kategori</th>
                <th>Açıklama</th>
                <th>Sorumlu</th>
                <th>Hedef Tarih</th>
                <th>İlerleme</th>
                <th>Performans</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($aksiyonlar)): ?>
                <tr>
                    <td colspan="9">Henüz aksiyon bulunmuyor.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($aksiyonlar as $aksiyon): ?>
                    <tr>
                        <td><?php echo esc_html($aksiyon->sira_no); ?></td>
                        <td><?php echo esc_html($aksiyon->kullanici_adi); ?></td>
                        <td><?php echo esc_html($aksiyon->kategori_adi); ?></td>
                        <td><?php echo esc_html(wp_trim_words($aksiyon->aciklama, 10)); ?></td>
                        <td><?php echo esc_html($aksiyon->sorumlular); ?></td>
                        <td><?php echo esc_html($aksiyon->hedef_tarih); ?></td>
                        <td>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width: <?php echo intval($aksiyon->ilerleme); ?>%;">
                                    <?php echo intval($aksiyon->ilerleme); ?>%
                                </div>
                            </div>
                        </td>
                        <td><?php echo esc_html($aksiyon->performans_adi); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=bkm-aksiyon-ekle&action=edit&id=' . $aksiyon->id); ?>" class="button button-small">Düzenle</a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=bkm-aksiyonlar&action=delete&id=' . $aksiyon->id), 'delete_aksiyon_' . $aksiyon->id); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Bu aksiyonu silmek istediğinizden emin misiniz?')">Sil</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.progress-bar-container {
    width: 100%;
    background-color: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
}
.progress-bar {
    height: 20px;
    background-color: #0073aa;
    color: white;
    text-align: center;
    line-height: 20px;
    font-size: 12px;
    transition: width 0.3s ease;
}
</style>
