<?php
class BKM_Aksiyon_Takip_Shortcode {
    
    private $frontend;
    
    public function __construct() {
        $this->frontend = new BKM_Aksiyon_Takip_Frontend();
        add_shortcode('bkm_aksiyon_dashboard', array($this, 'render_dashboard'));
        add_shortcode('bkm_aksiyon_list', array($this, 'render_aksiyon_list'));
        add_shortcode('bkm_gorev_form', array($this, 'render_gorev_form'));
        add_shortcode('bkm_login_form', array($this, 'render_login_form'));
        add_shortcode('bkm_user_profile', array($this, 'render_user_profile'));
    }
    
    /**
     * Ana dashboard shortcode'u
     * Kullanım: [bkm_aksiyon_dashboard]
     */
    public function render_dashboard($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'show_completed' => 'true',
            'limit' => 10
        ), $atts);
        
        if (!is_user_logged_in()) {
            return $this->render_login_form(array());
        }
        
        ob_start();
        include BKM_AKSIYON_TAKIP_PLUGIN_PATH . 'public/views/dashboard.php';
        return ob_get_clean();
    }
    
    /**
     * Aksiyon listesi shortcode'u
     * Kullanım: [bkm_aksiyon_list user_id="1" category_id="2" limit="20"]
     */
    public function render_aksiyon_list($atts) {
        $atts = shortcode_atts(array(
            'user_id' => get_current_user_id(),
            'category_id' => '',
            'performance_id' => '',
            'status' => '',
            'limit' => 20,
            'show_filters' => 'true',
            'show_progress' => 'true'
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="bkm-notice bkm-notice-warning">Aksiyon listesini görmek için giriş yapmanız gerekiyor.</div>';
        }
        
        ob_start();
        include BKM_AKSIYON_TAKIP_PLUGIN_PATH . 'public/views/aksiyon-list.php';
        return ob_get_clean();
    }
    
    /**
     * Görev formu shortcode'u
     * Kullanım: [bkm_gorev_form aksiyon_id="1"]
     */
    public function render_gorev_form($atts) {
        $atts = shortcode_atts(array(
            'aksiyon_id' => '',
            'gorev_id' => '',
            'redirect_url' => ''
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="bkm-notice bkm-notice-warning">Görev eklemek için giriş yapmanız gerekiyor.</div>';
        }
        
        $current_user = wp_get_current_user();
        if (!in_array('administrator', $current_user->roles) && !in_array('editor', $current_user->roles)) {
            return '<div class="bkm-notice bkm-notice-error">Görev ekleme yetkiniz bulunmuyor.</div>';
        }
        
        ob_start();
        include BKM_AKSIYON_TAKIP_PLUGIN_PATH . 'public/views/gorev-form.php';
        return ob_get_clean();
    }
    
    /**
     * Giriş formu shortcode'u
     * Kullanım: [bkm_login_form redirect_url="/dashboard"]
     */
    public function render_login_form($atts) {
        $atts = shortcode_atts(array(
            'redirect_url' => '',
            'show_register' => 'false',
            'title' => 'Giriş Yap'
        ), $atts);
        
        if (is_user_logged_in()) {
            return '<div class="bkm-notice bkm-notice-success">Zaten giriş yapmış durumdasınız.</div>';
        }
        
        ob_start();
        include BKM_AKSIYON_TAKIP_PLUGIN_PATH . 'public/views/login-form.php';
        return ob_get_clean();
    }
    
    /**
     * Kullanıcı profili shortcode'u
     * Kullanım: [bkm_user_profile]
     */
    public function render_user_profile($atts) {
        $atts = shortcode_atts(array(
            'show_stats' => 'true',
            'show_recent_actions' => 'true',
            'recent_limit' => 5
        ), $atts);
        
        if (!is_user_logged_in()) {
            return '<div class="bkm-notice bkm-notice-warning">Profil bilgilerini görmek için giriş yapmanız gerekiyor.</div>';
        }
        
        $current_user = wp_get_current_user();
        $user_stats = $this->get_user_statistics($current_user->ID);
        
        ob_start();
        ?>
        <div class="bkm-user-profile">
            <div class="bkm-profile-header">
                <div class="bkm-avatar">
                    <?php echo get_avatar($current_user->ID, 80); ?>
                </div>
                <div class="bkm-user-info">
                    <h3><?php echo esc_html($current_user->display_name); ?></h3>
                    <p class="bkm-user-email"><?php echo esc_html($current_user->user_email); ?></p>
                    <p class="bkm-user-role"><?php echo esc_html(ucfirst($current_user->roles[0])); ?></p>
                </div>
            </div>
            
            <?php if ($atts['show_stats'] === 'true'): ?>
            <div class="bkm-profile-stats">
                <div class="bkm-stat-item">
                    <span class="bkm-stat-number"><?php echo $user_stats['total_actions']; ?></span>
                    <span class="bkm-stat-label">Toplam Aksiyon</span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-number"><?php echo $user_stats['completed_actions']; ?></span>
                    <span class="bkm-stat-label">Tamamlanan</span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-number"><?php echo $user_stats['pending_tasks']; ?></span>
                    <span class="bkm-stat-label">Bekleyen Görev</span>
                </div>
                <div class="bkm-stat-item">
                    <span class="bkm-stat-number"><?php echo number_format($user_stats['avg_progress'], 1); ?>%</span>
                    <span class="bkm-stat-label">Ortalama İlerleme</span>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_recent_actions'] === 'true'): ?>
            <div class="bkm-recent-actions">
                <h4>Son Aksiyonlar</h4>
                <?php 
                $recent_actions = $this->get_user_recent_actions($current_user->ID, $atts['recent_limit']);
                if ($recent_actions): ?>
                    <ul class="bkm-action-list">
                        <?php foreach ($recent_actions as $action): ?>
                        <li class="bkm-action-item">
                            <div class="bkm-action-title"><?php echo esc_html($action->aksiyon_adi); ?></div>
                            <div class="bkm-action-progress">
                                <div class="bkm-progress-bar">
                                    <div class="bkm-progress-fill" style="width: <?php echo $action->ilerleme; ?>%"></div>
                                </div>
                                <span class="bkm-progress-text"><?php echo $action->ilerleme; ?>%</span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Henüz aksiyon bulunmuyor.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Kullanıcı istatistiklerini getir
     */
    private function get_user_statistics($user_id) {
        global $wpdb;
        $aksiyon_table = $wpdb->prefix . 'bkm_aksiyonlar';
        $gorev_table = $wpdb->prefix . 'bkm_gorevler';
        
        $stats = array();
        
        // Toplam aksiyon sayısı
        $stats['total_actions'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM $aksiyon_table 
            WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
        ", $user_id, $user_id));
        
        // Tamamlanan aksiyon sayısı
        $stats['completed_actions'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM $aksiyon_table 
            WHERE (kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))) AND ilerleme = 100
        ", $user_id, $user_id));
        
        // Bekleyen görev sayısı
        $stats['pending_tasks'] = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) 
            FROM $gorev_table 
            WHERE sorumlu_id = %d AND durum != 1
        ", $user_id));
        
        // Ortalama ilerleme
        $avg_progress = $wpdb->get_var($wpdb->prepare("
            SELECT AVG(ilerleme) 
            FROM $aksiyon_table 
            WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
        ", $user_id, $user_id));
        
        $stats['avg_progress'] = $avg_progress ? $avg_progress : 0;
        
        return $stats;
    }
    
    /**
     * Kullanıcının son aksiyonlarını getir
     */
    private function get_user_recent_actions($user_id, $limit = 5) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * 
            FROM $table_name 
            WHERE kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(sorumlular, ' ', ''))
            ORDER BY updated_at DESC 
            LIMIT %d
        ", $user_id, $user_id, $limit));
    }
}
