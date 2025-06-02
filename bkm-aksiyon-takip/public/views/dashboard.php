<?php
/**
 * Dashboard View - Frontend
 * Bu dosya kullanıcı dashboard'unu görüntüler
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Frontend instance'ı al
global $wpdb;

// İstatistikleri al
$aksiyon_table = $wpdb->prefix . 'bkm_aksiyonlar';
$gorev_table = $wpdb->prefix . 'bkm_gorevler';
$kategori_table = $wpdb->prefix . 'bkm_kategoriler';

// Kullanıcının aksiyonları
$user_actions = $wpdb->get_results($wpdb->prepare("
    SELECT a.*, k.kategori_adi, p.performans_adi 
    FROM $aksiyon_table a
    LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
    LEFT JOIN {$wpdb->prefix}bkm_performanslar p ON a.performans_id = p.id
    WHERE a.kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(a.sorumlular, ' ', ''))
    ORDER BY a.updated_at DESC
    LIMIT 10
", $user_id, $user_id));

// İstatistikler
$total_actions = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) 
    FROM $aksiyon_table 
    WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
", $user_id, $user_id));

$completed_actions = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) 
    FROM $aksiyon_table 
    WHERE (kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))) AND ilerleme = 100
", $user_id, $user_id));

$pending_tasks = $wpdb->get_var($wpdb->prepare("
    SELECT COUNT(*) 
    FROM $gorev_table 
    WHERE sorumlu_id = %d AND durum != 1
", $user_id));

$avg_progress = $wpdb->get_var($wpdb->prepare("
    SELECT AVG(ilerleme) 
    FROM $aksiyon_table 
    WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
", $user_id, $user_id));

$avg_progress = $avg_progress ? round($avg_progress, 1) : 0;

// Son görevler
$recent_tasks = $wpdb->get_results($wpdb->prepare("
    SELECT g.*, a.aksiyon_adi 
    FROM $gorev_table g
    LEFT JOIN $aksiyon_table a ON g.aksiyon_id = a.id
    WHERE g.sorumlu_id = %d
    ORDER BY g.created_at DESC
    LIMIT 5
", $user_id));
?>

<div class="bkm-frontend bkm-dashboard-page">
    <div class="bkm-container">
        
        <!-- Üst Bar -->
        <div class="bkm-dashboard-header">
            <div class="bkm-welcome-message">
                <h2>Hoş geldiniz, <?php echo esc_html($current_user->display_name); ?>!</h2>
                <p>Aksiyon takip sisteminize genel bakış</p>
            </div>
            <div class="bkm-header-actions">
                <button class="bkm-btn bkm-btn-primary" onclick="location.reload()">
                    <i class="dashicons dashicons-update"></i> Yenile
                </button>
                <a href="<?php echo wp_logout_url(); ?>" class="bkm-btn bkm-logout-btn">
                    <i class="dashicons dashicons-exit"></i> Çıkış
                </a>
            </div>
        </div>

        <!-- İstatistik Kartları -->
        <div class="bkm-dashboard bkm-dashboard-stats">
            <div class="bkm-dashboard-card">
                <div class="bkm-card-icon">
                    <i class="dashicons dashicons-list-view"></i>
                </div>
                <div class="bkm-card-content">
                    <span class="bkm-stat-number" data-stat="total_actions"><?php echo $total_actions; ?></span>
                    <span class="bkm-stat-label">Toplam Aksiyon</span>
                </div>
            </div>
            
            <div class="bkm-dashboard-card">
                <div class="bkm-card-icon">
                    <i class="dashicons dashicons-yes-alt"></i>
                </div>
                <div class="bkm-card-content">
                    <span class="bkm-stat-number" data-stat="completed_actions"><?php echo $completed_actions; ?></span>
                    <span class="bkm-stat-label">Tamamlanan</span>
                </div>
            </div>
            
            <div class="bkm-dashboard-card">
                <div class="bkm-card-icon">
                    <i class="dashicons dashicons-clock"></i>
                </div>
                <div class="bkm-card-content">
                    <span class="bkm-stat-number" data-stat="pending_tasks"><?php echo $pending_tasks; ?></span>
                    <span class="bkm-stat-label">Bekleyen Görev</span>
                </div>
            </div>
            
            <div class="bkm-dashboard-card">
                <div class="bkm-card-icon">
                    <i class="dashicons dashicons-chart-bar"></i>
                </div>
                <div class="bkm-card-content">
                    <span class="bkm-stat-number" data-stat="avg_progress"><?php echo $avg_progress; ?>%</span>
                    <span class="bkm-stat-label">Ortalama İlerleme</span>
                </div>
            </div>
        </div>

        <!-- Ana İçerik -->
        <div class="bkm-dashboard-content">
            <div class="bkm-content-left">
                
                <!-- Son Aksiyonlar -->
                <div class="bkm-dashboard-widget">
                    <div class="bkm-widget-header">
                        <h3>Son Aksiyonlar</h3>
                        <a href="#" class="bkm-widget-action">Tümünü Gör</a>
                    </div>
                    <div class="bkm-widget-content">
                        <?php if ($user_actions): ?>
                            <ul class="bkm-action-list">
                                <?php foreach ($user_actions as $action): 
                                    $priority_class = '';
                                    if ($action->performans_id == 1) $priority_class = 'high-priority';
                                    elseif ($action->performans_id == 2) $priority_class = 'medium-priority';
                                    else $priority_class = 'low-priority';
                                ?>
                                <li class="bkm-action-item <?php echo $priority_class; ?>">
                                    <div class="bkm-action-header">
                                        <h4 class="bkm-action-title"><?php echo esc_html($action->aksiyon_adi); ?></h4>
                                        <span class="bkm-action-category"><?php echo esc_html($action->kategori_adi); ?></span>
                                    </div>
                                    
                                    <div class="bkm-action-meta">
                                        <span><i class="dashicons dashicons-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($action->created_at)); ?></span>
                                        <span><i class="dashicons dashicons-performance"></i> <?php echo esc_html($action->performans_adi); ?></span>
                                    </div>
                                    
                                    <?php if ($action->aciklama): ?>
                                    <div class="bkm-action-description">
                                        <?php echo esc_html(wp_trim_words($action->aciklama, 15)); ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="bkm-action-progress">
                                        <div class="bkm-progress-bar">
                                            <div class="bkm-progress-fill" style="width: <?php echo $action->ilerleme; ?>%"></div>
                                        </div>
                                        <span class="bkm-progress-text"><?php echo $action->ilerleme; ?>%</span>
                                    </div>
                                    
                                    <div class="bkm-action-footer">
                                        <div class="bkm-action-dates">
                                            <?php if ($action->baslangic_tarihi): ?>
                                            <small>Başlangıç: <?php echo date('d.m.Y', strtotime($action->baslangic_tarihi)); ?></small>
                                            <?php endif; ?>
                                            <?php if ($action->bitis_tarihi): ?>
                                            <small>Bitiş: <?php echo date('d.m.Y', strtotime($action->bitis_tarihi)); ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="bkm-action-buttons">
                                            <?php if ($action->ilerleme < 100): ?>
                                            <button class="bkm-btn bkm-btn-small bkm-btn-success bkm-complete-action" 
                                                    data-action-id="<?php echo $action->id; ?>">
                                                Tamamla
                                            </button>
                                            <?php endif; ?>
                                            <button class="bkm-btn bkm-btn-small">
                                                Detay
                                            </button>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="bkm-empty-state">
                                <i class="dashicons dashicons-list-view"></i>
                                <p>Henüz aksiyon bulunmuyor.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            </div>
            
            <div class="bkm-content-right">
                
                <!-- Bekleyen Görevler -->
                <div class="bkm-dashboard-widget">
                    <div class="bkm-widget-header">
                        <h3>Bekleyen Görevlerim</h3>
                        <span class="bkm-widget-count"><?php echo count($recent_tasks); ?></span>
                    </div>
                    <div class="bkm-widget-content">
                        <?php if ($recent_tasks): ?>
                            <ul class="bkm-task-list">
                                <?php foreach ($recent_tasks as $task): 
                                    $task_class = $task->durum == 1 ? 'completed' : 'pending';
                                ?>
                                <li class="bkm-task-item <?php echo $task_class; ?>">
                                    <div class="bkm-task-info">
                                        <h5><?php echo esc_html($task->gorev_adi); ?></h5>
                                        <div class="bkm-task-meta">
                                            <span><i class="dashicons dashicons-admin-links"></i> <?php echo esc_html($task->aksiyon_adi); ?></span>
                                            <?php if ($task->bitis_tarihi): ?>
                                            <span><i class="dashicons dashicons-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($task->bitis_tarihi)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="bkm-task-actions">
                                        <?php if ($task->durum != 1): ?>
                                        <button class="bkm-btn bkm-btn-small bkm-btn-success">
                                            <i class="dashicons dashicons-yes"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="bkm-empty-state">
                                <i class="dashicons dashicons-yes-alt"></i>
                                <p>Tüm görevler tamamlandı!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Hızlı İşlemler -->
                <div class="bkm-dashboard-widget">
                    <div class="bkm-widget-header">
                        <h3>Hızlı İşlemler</h3>
                    </div>
                    <div class="bkm-widget-content">
                        <div class="bkm-quick-actions">
                            <?php if (in_array('administrator', $current_user->roles) || in_array('editor', $current_user->roles)): ?>
                            <a href="#" class="bkm-quick-action-btn">
                                <i class="dashicons dashicons-plus-alt"></i>
                                <span>Yeni Aksiyon</span>
                            </a>
                            <a href="#" class="bkm-quick-action-btn">
                                <i class="dashicons dashicons-editor-ol"></i>
                                <span>Görev Ekle</span>
                            </a>
                            <?php endif; ?>
                            <a href="#" class="bkm-quick-action-btn">
                                <i class="dashicons dashicons-chart-area"></i>
                                <span>Raporlar</span>
                            </a>
                            <a href="#" class="bkm-quick-action-btn">
                                <i class="dashicons dashicons-admin-users"></i>
                                <span>Profil</span>
                            </a>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
        
    </div>
</div>

<style>
/* Dashboard'a özel stiller */
.bkm-dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 20px 0;
    border-bottom: 2px solid #e1e5e9;
}

.bkm-welcome-message h2 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 28px;
}

.bkm-welcome-message p {
    margin: 0;
    color: #666;
    font-size: 16px;
}

.bkm-header-actions {
    display: flex;
    gap: 10px;
}

.bkm-dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin-top: 30px;
}

.bkm-dashboard-widget {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 25px;
    overflow: hidden;
}

.bkm-widget-header {
    background: #f8f9fa;
    padding: 20px 25px;
    border-bottom: 1px solid #e1e5e9;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bkm-widget-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 18px;
}

.bkm-widget-action {
    color: #007cba;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.bkm-widget-count {
    background: #007cba;
    color: #fff;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.bkm-widget-content {
    padding: 25px;
}

.bkm-card-icon {
    font-size: 24px;
    color: #007cba;
    margin-bottom: 10px;
}

.bkm-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #666;
}

.bkm-empty-state i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

.bkm-quick-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.bkm-quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-decoration: none;
    color: #2c3e50;
    transition: all 0.3s ease;
}

.bkm-quick-action-btn:hover {
    background: #007cba;
    color: #fff;
    transform: translateY(-2px);
}

.bkm-quick-action-btn i {
    font-size: 24px;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .bkm-dashboard-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }
    
    .bkm-dashboard-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .bkm-quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>