<?php
/**
 * Aksiyon Listesi View - Frontend
 * Bu dosya kullanıcının aksiyonlarını listeler
 */

if (!defined('ABSPATH')) {
    exit;
}

$current_user = wp_get_current_user();
$user_id = $atts['user_id'] ?: get_current_user_id();

global $wpdb;

// Filtreler
$category_filter = isset($_GET['category_id']) ? intval($_GET['category_id']) : ($atts['category_id'] ?: '');
$performance_filter = isset($_GET['performance_id']) ? intval($_GET['performance_id']) : ($atts['performance_id'] ?: '');
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : ($atts['status'] ?: '');
$search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Sayfalama
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$per_page = $atts['limit'] ?: 20;
$offset = ($page - 1) * $per_page;

// WHERE koşulu oluştur
$where_conditions = array();
$where_params = array();

// Kullanıcı filtresi
$where_conditions[] = "(a.kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(a.sorumlular, ' ', '')))";
$where_params[] = $user_id;
$where_params[] = $user_id;

// Kategori filtresi
if ($category_filter) {
    $where_conditions[] = "a.kategori_id = %d";
    $where_params[] = $category_filter;
}

// Performans filtresi
if ($performance_filter) {
    $where_conditions[] = "a.performans_id = %d";
    $where_params[] = $performance_filter;
}

// Durum filtresi
if ($status_filter) {
    switch ($status_filter) {
        case 'completed':
            $where_conditions[] = "a.ilerleme = 100";
            break;
        case 'in_progress':
            $where_conditions[] = "a.ilerleme > 0 AND a.ilerleme < 100";
            break;
        case 'not_started':
            $where_conditions[] = "a.ilerleme = 0";
            break;
    }
}

// Arama filtresi
if ($search_term) {
    $where_conditions[] = "(a.aksiyon_adi LIKE %s OR a.aciklama LIKE %s)";
    $where_params[] = '%' . $wpdb->esc_like($search_term) . '%';
    $where_params[] = '%' . $wpdb->esc_like($search_term) . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Toplam kayıt sayısı
$total_query = "
    SELECT COUNT(*) 
    FROM {$wpdb->prefix}bkm_aksiyonlar a 
    WHERE $where_clause
";

$total_items = $wpdb->get_var($wpdb->prepare($total_query, $where_params));

// Ana sorgu
$query = "
    SELECT a.*, k.kategori_adi, p.performans_adi, u.display_name as kullanici_adi
    FROM {$wpdb->prefix}bkm_aksiyonlar a
    LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
    LEFT JOIN {$wpdb->prefix}bkm_performanslar p ON a.performans_id = p.id
    LEFT JOIN {$wpdb->users} u ON a.kullanici_id = u.ID
    WHERE $where_clause
    ORDER BY a.updated_at DESC
    LIMIT %d OFFSET %d
";

$where_params[] = $per_page;
$where_params[] = $offset;

$actions = $wpdb->get_results($wpdb->prepare($query, $where_params));

// Kategoriler ve performans seviyeleri
$categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bkm_kategoriler ORDER BY kategori_adi");
$performances = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}bkm_performanslar ORDER BY id");

// Sayfalama hesaplamaları
$total_pages = ceil($total_items / $per_page);
?>

<div class="bkm-frontend bkm-action-list-page">
    <div class="bkm-container">
        
        <!-- Başlık -->
        <div class="bkm-page-header">
            <h2>Aksiyonlar</h2>
            <div class="bkm-page-actions">
                <?php if (in_array('administrator', $current_user->roles) || in_array('editor', $current_user->roles)): ?>
                <a href="#" class="bkm-btn bkm-btn-primary">
                    <i class="dashicons dashicons-plus-alt"></i> Yeni Aksiyon
                </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filtreler -->
        <?php if ($atts['show_filters'] === 'true'): ?>
        <div class="bkm-action-filters">
            <form method="GET" class="bkm-filter-form">
                
                <div class="bkm-filter-group">
                    <label for="search">Arama</label>
                    <input type="text" id="search" name="search" class="bkm-search-input" 
                           value="<?php echo esc_attr($search_term); ?>" placeholder="Aksiyon ara...">
                </div>
                
                <div class="bkm-filter-group">
                    <label for="category_id">Kategori</label>
                    <select id="category_id" name="category_id" class="bkm-filter-select">
                        <option value="">Tümü</option>
                        <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category->id; ?>" 
                                <?php selected($category_filter, $category->id); ?>>
                            <?php echo esc_html($category->kategori_adi); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="bkm-filter-group">
                    <label for="performance_id">Performans</label>
                    <select id="performance_id" name="performance_id" class="bkm-filter-select">
                        <option value="">Tümü</option>
                        <?php foreach ($performances as $performance): ?>
                        <option value="<?php echo $performance->id; ?>" 
                                <?php selected($performance_filter, $performance->id); ?>>
                            <?php echo esc_html($performance->performans_adi); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="bkm-filter-group">
                    <label for="status">Durum</label>
                    <select id="status" name="status" class="bkm-filter-select">
                        <option value="">Tümü</option>
                        <option value="not_started" <?php selected($status_filter, 'not_started'); ?>>Başlanmamış</option>
                        <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>>Devam Ediyor</option>
                        <option value="completed" <?php selected($status_filter, 'completed'); ?>>Tamamlandı</option>
                    </select>
                </div>
                
                <div class="bkm-filter-group">
                    <button type="submit" class="bkm-btn bkm-btn-primary">
                        <i class="dashicons dashicons-search"></i> Filtrele
                    </button>
                    <a href="?" class="bkm-btn">
                        <i class="dashicons dashicons-dismiss"></i> Temizle
                    </a>
                </div>
                
            </form>
        </div>
        <?php endif; ?>

        <!-- Sonuç Bilgisi -->
        <div class="bkm-results-info">
            <p>Toplam <strong><?php echo $total_items; ?></strong> aksiyon bulundu</p>
            <?php if ($total_pages > 1): ?>
            <p>Sayfa <strong><?php echo $page; ?></strong> / <strong><?php echo $total_pages; ?></strong></p>
            <?php endif; ?>
        </div>

        <!-- Aksiyon Listesi -->
        <div class="bkm-action-list-container">
            <?php if ($actions): ?>
                <ul class="bkm-action-list">
                    <?php foreach ($actions as $action): 
                        $priority_class = '';
                        if ($action->performans_id == 1) $priority_class = 'high-priority';
                        elseif ($action->performans_id == 2) $priority_class = 'medium-priority';
                        else $priority_class = 'low-priority';
                        
                        $completed_class = $action->ilerleme == 100 ? 'completed' : '';
                        
                        // Görev sayısını al
                        $task_count = $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_gorevler WHERE aksiyon_id = %d
                        ", $action->id));
                        
                        $completed_task_count = $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(*) FROM {$wpdb->prefix}bkm_gorevler WHERE aksiyon_id = %d AND durum = 1
                        ", $action->id));
                    ?>
                    <li class="bkm-action-item <?php echo $priority_class . ' ' . $completed_class; ?>">
                        <div class="bkm-action-header">
                            <h3 class="bkm-action-title"><?php echo esc_html($action->aksiyon_adi); ?></h3>
                            <div class="bkm-action-status">
                                <?php if ($action->ilerleme == 100): ?>
                                <span class="bkm-status-badge bkm-status-completed">Tamamlandı</span>
                                <?php elseif ($action->ilerleme > 0): ?>
                                <span class="bkm-status-badge bkm-status-progress">Devam Ediyor</span>
                                <?php else: ?>
                                <span class="bkm-status-badge bkm-status-not-started">Başlanmamış</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="bkm-action-meta">
                            <span><i class="dashicons dashicons-category"></i> <?php echo esc_html($action->kategori_adi); ?></span>
                            <span><i class="dashicons dashicons-performance"></i> <?php echo esc_html($action->performans_adi); ?></span>
                            <span><i class="dashicons dashicons-admin-users"></i> <?php echo esc_html($action->kullanici_adi); ?></span>
                            <span><i class="dashicons dashicons-calendar-alt"></i> <?php echo date('d.m.Y', strtotime($action->created_at)); ?></span>
                        </div>
                        
                        <?php if ($action->aciklama): ?>
                        <div class="bkm-action-description">
                            <?php echo esc_html(wp_trim_words($action->aciklama, 25)); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_progress'] === 'true'): ?>
                        <div class="bkm-action-progress">
                            <div class="bkm-progress-bar">
                                <div class="bkm-progress-fill" style="width: <?php echo $action->ilerleme; ?>%"></div>
                            </div>
                            <span class="bkm-progress-text"><?php echo $action->ilerleme; ?>%</span>
                            <?php if ($action->ilerleme < 100): ?>
                            <input type="range" min="0" max="100" value="<?php echo $action->ilerleme; ?>" 
                                   class="bkm-progress-input" data-action-id="<?php echo $action->id; ?>">
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Görev Bilgisi -->
                        <?php if ($task_count > 0): ?>
                        <div class="bkm-task-summary">
                            <span class="bkm-task-count">
                                <i class="dashicons dashicons-editor-ol"></i>
                                <?php echo $completed_task_count; ?>/<?php echo $task_count; ?> görev tamamlandı
                            </span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="bkm-action-footer">
                            <div class="bkm-action-dates">
                                <?php if ($action->baslangic_tarihi): ?>
                                <small><strong>Başlangıç:</strong> <?php echo date('d.m.Y', strtotime($action->baslangic_tarihi)); ?></small>
                                <?php endif; ?>
                                <?php if ($action->bitis_tarihi): ?>
                                <small><strong>Bitiş:</strong> <?php echo date('d.m.Y', strtotime($action->bitis_tarihi)); ?></small>
                                <?php endif; ?>
                            </div>
                            <div class="bkm-action-buttons">
                                <button class="bkm-btn bkm-btn-small" onclick="location.href='#action-<?php echo $action->id; ?>'">
                                    <i class="dashicons dashicons-visibility"></i> Detay
                                </button>
                                <?php if ($action->ilerleme < 100): ?>
                                <button class="bkm-btn bkm-btn-small bkm-btn-success bkm-complete-action" 
                                        data-action-id="<?php echo $action->id; ?>">
                                    <i class="dashicons dashicons-yes"></i> Tamamla
                                </button>
                                <?php endif; ?>
                                <?php if (in_array('administrator', $current_user->roles)): ?>
                                <button class="bkm-btn bkm-btn-small bkm-btn-danger bkm-delete-action" 
                                        data-action-id="<?php echo $action->id; ?>">
                                    <i class="dashicons dashicons-trash"></i> Sil
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="bkm-empty-state">
                    <i class="dashicons dashicons-list-view"></i>
                    <h3>Aksiyon bulunamadı</h3>
                    <p>Belirtilen kriterlere uygun aksiyon bulunmuyor.</p>
                    <?php if ($search_term || $category_filter || $performance_filter || $status_filter): ?>
                    <a href="?" class="bkm-btn bkm-btn-primary">Filtreleri Temizle</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sayfalama -->
        <?php if ($total_pages > 1): ?>
        <div class="bkm-pagination">
            <?php
            $base_url = remove_query_arg('paged');
            $base_url = add_query_arg(array(
                'search' => $search_term,
                'category_id' => $category_filter,
                'performance_id' => $performance_filter,
                'status' => $status_filter
            ), $base_url);
            ?>
            
            <?php if ($page > 1): ?>
            <a href="<?php echo add_query_arg('paged', $page - 1, $base_url); ?>" class="bkm-pagination-btn bkm-pagination-prev">
                <i class="dashicons dashicons-arrow-left-alt2"></i> Önceki
            </a>
            <?php endif; ?>
            
            <div class="bkm-pagination-numbers">
                <?php
                $start = max(1, $page - 2);
                $end = min($total_pages, $page + 2);
                
                for ($i = $start; $i <= $end; $i++):
                    $active_class = ($i == $page) ? 'active' : '';
                ?>
                <a href="<?php echo add_query_arg('paged', $i, $base_url); ?>" 
                   class="bkm-pagination-number <?php echo $active_class; ?>">
                    <?php echo $i; ?>
                </a>
                <?php endfor; ?>
            </div>
            
            <?php if ($page < $total_pages): ?>
            <a href="<?php echo add_query_arg('paged', $page + 1, $base_url); ?>" class="bkm-pagination-btn bkm-pagination-next">
                Sonraki <i class="dashicons dashicons-arrow-right-alt2"></i>
            </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<style>
/* Aksiyon listesi özel stiller */
.bkm-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #e1e5e9;
}

.bkm-page-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 28px;
}

.bkm-results-info {
    background: #f8f9fa;
    padding: 15px 20px;
    border-radius: 6px;
    margin-bottom: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.bkm-results-info p {
    margin: 0;
    color: #666;
}

.bkm-status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.bkm-status-completed {
    background: #28a745;
    color: #fff;
}

.bkm-status-progress {
    background: #007cba;
    color: #fff;
}

.bkm-status-not-started {
    background: #6c757d;
    color: #fff;
}

.bkm-task-summary {
    margin: 15px 0;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 6px;
    border-left: 3px solid #007cba;
}

.bkm-task-count {
    color: #555;
    font-size: 14px;
    font-weight: 500;
}

.bkm-progress-input {
    margin-left: 15px;
    width: 100px;
}

.bkm-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    margin-top: 30px;
    padding: 20px 0;
}

.bkm-pagination-btn,
.bkm-pagination-number {
    padding: 8px 16px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #555;
    transition: all 0.3s ease;
}

.bkm-pagination-btn:hover,
.bkm-pagination-number:hover {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
}

.bkm-pagination-number.active {
    background: #007cba;
    color: #fff;
    border-color: #007cba;
    font-weight: 600;
}

.bkm-pagination-numbers {
    display: flex;
    gap: 5px;
}

@media (max-width: 768px) {
    .bkm-page-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .bkm-results-info {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .bkm-pagination {
        flex-wrap: wrap;
        gap: 5px;
    }
    
    .bkm-pagination-btn,
    .bkm-pagination-number {
        padding: 6px 12px;
        font-size: 14px;
    }
}
</style>