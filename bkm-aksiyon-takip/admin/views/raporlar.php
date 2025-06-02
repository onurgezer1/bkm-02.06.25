<?php
if (!defined('WPINC')) {
    die;
}

global $wpdb;
$aksiyonlar_table = $wpdb->prefix . 'bkm_aksiyonlar';
$kategoriler_table = $wpdb->prefix . 'bkm_kategoriler';
$performanslar_table = $wpdb->prefix . 'bkm_performanslar';

// Temel istatistikler
$toplam_aksiyon = $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table");
$tamamlanan_aksiyon = $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE ilerleme = 100");
$bekleyen_aksiyon = $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE ilerleme < 100");
$geciken_aksiyon = $wpdb->get_var("SELECT COUNT(*) FROM $aksiyonlar_table WHERE hedef_tarih < CURDATE() AND ilerleme < 100");

// Kategori bazında dağılım
$kategori_dagilim = $wpdb->get_results("
    SELECT k.kategori_adi, COUNT(a.id) as sayi
    FROM $kategoriler_table k
    LEFT JOIN $aksiyonlar_table a ON k.id = a.kategori_id
    GROUP BY k.id, k.kategori_adi
    ORDER BY sayi DESC
");

// Performans bazında dağılım
$performans_dagilim = $wpdb->get_results("
    SELECT p.performans_adi, COUNT(a.id) as sayi
    FROM $performanslar_table p
    LEFT JOIN $aksiyonlar_table a ON p.id = a.performans_id
    GROUP BY p.id, p.performans_adi
    ORDER BY sayi DESC
");

// Aylık aksiyon trendi
$aylik_trend = $wpdb->get_results("
    SELECT DATE_FORMAT(acilma_tarihi, '%Y-%m') as ay, COUNT(*) as sayi
    FROM $aksiyonlar_table
    WHERE acilma_tarihi >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ay
    ORDER BY ay DESC
");

// İlerleme durumu dağılımı
$ilerleme_dagilim = $wpdb->get_results("
    SELECT 
        CASE 
            WHEN ilerleme = 0 THEN 'Başlanmadı'
            WHEN ilerleme > 0 AND ilerleme < 50 THEN 'Başlangıç'
            WHEN ilerleme >= 50 AND ilerleme < 100 THEN 'Devam Ediyor'
            WHEN ilerleme = 100 THEN 'Tamamlandı'
        END as durum,
        COUNT(*) as sayi
    FROM $aksiyonlar_table
    GROUP BY durum
    ORDER BY sayi DESC
");
?>

<div class="wrap">
    <h1>BKM Aksiyon Takip Raporları</h1>

    <!-- Genel İstatistikler -->
    <div class="bkm-stats-grid">
        <div class="bkm-stat-card">
            <h3>Toplam Aksiyon</h3>
            <div class="stat-number"><?php echo number_format($toplam_aksiyon); ?></div>
        </div>
        <div class="bkm-stat-card success">
            <h3>Tamamlanan</h3>
            <div class="stat-number"><?php echo number_format($tamamlanan_aksiyon); ?></div>
        </div>
        <div class="bkm-stat-card warning">
            <h3>Devam Eden</h3>
            <div class="stat-number"><?php echo number_format($bekleyen_aksiyon); ?></div>
        </div>
        <div class="bkm-stat-card danger">
            <h3>Geciken</h3>
            <div class="stat-number"><?php echo number_format($geciken_aksiyon); ?></div>
        </div>
    </div>

    <div class="bkm-reports-container">
        <!-- Kategori Dağılımı -->
        <div class="bkm-report-section">
            <h2>Kategori Bazında Dağılım</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Aksiyon Sayısı</th>
                        <th>Yüzde</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kategori_dagilim)): ?>
                        <tr><td colspan="3">Veri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($kategori_dagilim as $kategori): ?>
                            <?php $yuzde = $toplam_aksiyon > 0 ? ($kategori->sayi / $toplam_aksiyon) * 100 : 0; ?>
                            <tr>
                                <td><?php echo esc_html($kategori->kategori_adi); ?></td>
                                <td><?php echo number_format($kategori->sayi); ?></td>
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?php echo $yuzde; ?>%;"></div>
                                        <span class="percentage-text"><?php echo number_format($yuzde, 1); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- İlerleme Durumu -->
        <div class="bkm-report-section">
            <h2>İlerleme Durumu Dağılımı</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Durum</th>
                        <th>Aksiyon Sayısı</th>
                        <th>Yüzde</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ilerleme_dagilim)): ?>
                        <tr><td colspan="3">Veri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($ilerleme_dagilim as $durum): ?>
                            <?php $yuzde = $toplam_aksiyon > 0 ? ($durum->sayi / $toplam_aksiyon) * 100 : 0; ?>
                            <tr>
                                <td><?php echo esc_html($durum->durum); ?></td>
                                <td><?php echo number_format($durum->sayi); ?></td>
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?php echo $yuzde; ?>%;"></div>
                                        <span class="percentage-text"><?php echo number_format($yuzde, 1); ?>%</span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Aylık Trend -->
        <div class="bkm-report-section">
            <h2>Son 6 Aylık Aksiyon Trendi</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Ay</th>
                        <th>Yeni Aksiyon Sayısı</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($aylik_trend)): ?>
                        <tr><td colspan="2">Veri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($aylik_trend as $ay): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y F', strtotime($ay->ay . '-01'))); ?></td>
                                <td><?php echo number_format($ay->sayi); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Performans Dağılımı -->
        <div class="bkm-report-section">
            <h2>Performans Bazında Dağılım</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Performans</th>
                        <th>Aksiyon Sayısı</th>
                        <th>Yüzde</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($performans_dagilim)): ?>
                        <tr><td colspan="3">Veri bulunamadı.</td></tr>
                    <?php else: ?>
                        <?php foreach ($performans_dagilim as $performans): ?>
                            <?php $yuzde = $toplam_aksiyon > 0 ? ($performans->sayi / $toplam_aksiyon) * 100 : 0; ?>
                            <tr>
                                <td><?php echo esc_html($performans->performans_adi); ?></td>
                                <td><?php echo number_format($performans->sayi); ?></td>
                                <td>
                                    <div class="percentage-bar">
                                        <div class="percentage-fill" style="width: <?php echo $yuzde; ?>%;"></div>
                                        <span class="percentage-text"><?php echo number_format($yuzde, 1); ?>%</span>
                                    </div>
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
.bkm-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.bkm-stat-card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.bkm-stat-card h3 {
    margin: 0 0 10px 0;
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
}

.bkm-stat-card .stat-number {
    font-size: 32px;
    font-weight: bold;
    color: #0073aa;
}

.bkm-stat-card.success .stat-number {
    color: #46b450;
}

.bkm-stat-card.warning .stat-number {
    color: #ffb900;
}

.bkm-stat-card.danger .stat-number {
    color: #dc3232;
}

.bkm-reports-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
}

.bkm-report-section {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.bkm-report-section h2 {
    margin-top: 0;
    color: #23282d;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.percentage-bar {
    position: relative;
    background: #f0f0f0;
    height: 25px;
    border-radius: 3px;
    overflow: hidden;
}

.percentage-fill {
    height: 100%;
    background: #0073aa;
    transition: width 0.3s ease;
}

.percentage-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #333;
    font-size: 12px;
    font-weight: bold;
}
</style>
