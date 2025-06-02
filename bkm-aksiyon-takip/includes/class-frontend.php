<?php
class BKM_Aksiyon_Takip_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('wp_ajax_bkm_frontend_login', array($this, 'handle_ajax_login'));
        add_action('wp_ajax_nopriv_bkm_frontend_login', array($this, 'handle_ajax_login'));
        add_action('wp_ajax_bkm_frontend_logout', array($this, 'handle_ajax_logout'));
        add_action('wp_ajax_bkm_save_gorev', array($this, 'save_gorev'));
        add_action('wp_ajax_bkm_delete_gorev', array($this, 'delete_gorev'));
        add_action('wp_ajax_bkm_update_aksiyon_progress', array($this, 'update_aksiyon_progress'));
        add_action('wp_ajax_bkm_complete_action', array($this, 'complete_action'));
        add_action('wp_ajax_bkm_delete_action', array($this, 'delete_action'));
        add_action('wp_ajax_bkm_log_error', array($this, 'log_error'));
    }
    
    public function enqueue_frontend_assets() {
        wp_enqueue_script('bkm-frontend-js', BKM_AKSIYON_TAKIP_PLUGIN_URL . 'public/js/frontend.js', array('jquery'), BKM_AKSIYON_TAKIP_VERSION, true);
        wp_enqueue_style('bkm-frontend-css', BKM_AKSIYON_TAKIP_PLUGIN_URL . 'public/css/frontend.css', array(), BKM_AKSIYON_TAKIP_VERSION);
        
        wp_localize_script('bkm-frontend-js', 'bkm_frontend_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('bkm_frontend_nonce'),
            'messages' => array(
                'login_required' => 'Giriş yapmanız gerekiyor.',
                'invalid_credentials' => 'Kullanıcı adı veya şifre hatalı.',
                'permission_denied' => 'Bu işlem için yetkiniz bulunmuyor.',
                'success' => 'İşlem başarıyla tamamlandı.',
                'error' => 'Bir hata oluştu.',
                'confirm_delete' => 'Bu görevi silmek istediğinizden emin misiniz?'
            )
        ));
    }
    
    public function handle_ajax_login() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        $username = sanitize_text_field($_POST['username']);
        $password = $_POST['password'];
        
        $user = wp_authenticate($username, $password);
        
        if (is_wp_error($user)) {
            wp_send_json_error(array(
                'message' => 'Kullanıcı adı veya şifre hatalı.'
            ));
        }
        
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        
        wp_send_json_success(array(
            'message' => 'Başarıyla giriş yaptınız.',
            'user' => array(
                'id' => $user->ID,
                'display_name' => $user->display_name,
                'roles' => $user->roles
            )
        ));
    }
    
    public function handle_ajax_logout() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        wp_logout();
        wp_send_json_success(array(
            'message' => 'Başarıyla çıkış yaptınız.'
        ));
    }
    
    public function save_gorev() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Giriş yapmanız gerekiyor.'));
        }
        
        $current_user = wp_get_current_user();
        
        // Yetki kontrolü - Sadece Yönetici ve Editör görev ekleyebilir
        if (!in_array('administrator', $current_user->roles) && !in_array('editor', $current_user->roles)) {
            wp_send_json_error(array('message' => 'Görev ekleme yetkiniz bulunmuyor.'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        
        $data = array(
            'aksiyon_id' => intval($_POST['aksiyon_id']),
            'gorev_adi' => sanitize_text_field($_POST['gorev_adi']),
            'aciklama' => sanitize_textarea_field($_POST['aciklama']),
            'sorumlu_id' => intval($_POST['sorumlu_id']),
            'durum' => intval($_POST['durum']),
            'baslangic_tarihi' => sanitize_text_field($_POST['baslangic_tarihi']),
            'bitis_tarihi' => sanitize_text_field($_POST['bitis_tarihi'])
        );
        
        if (isset($_POST['gorev_id']) && !empty($_POST['gorev_id'])) {
            // Düzenleme için yetki kontrolü
            $gorev = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", intval($_POST['gorev_id'])));
            
            if (!$gorev) {
                wp_send_json_error(array('message' => 'Görev bulunamadı.'));
            }
            
            // Sadece yönetici veya görev sahibi düzenleyebilir
            if (!in_array('administrator', $current_user->roles) && $gorev->sorumlu_id != $current_user->ID) {
                wp_send_json_error(array('message' => 'Bu görevi düzenleme yetkiniz bulunmuyor.'));
            }
            
            $result = $wpdb->update($table_name, $data, array('id' => intval($_POST['gorev_id'])));
        } else {
            $result = $wpdb->insert($table_name, $data);
        }
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Görev başarıyla kaydedildi.'));
        } else {
            wp_send_json_error(array('message' => 'Görev kaydedilirken bir hata oluştu.'));
        }
    }
    
    public function delete_gorev() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Giriş yapmanız gerekiyor.'));
        }
        
        $current_user = wp_get_current_user();
        
        // Sadece yönetici silebilir
        if (!in_array('administrator', $current_user->roles)) {
            wp_send_json_error(array('message' => 'Görev silme yetkiniz bulunmuyor.'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        
        $result = $wpdb->delete($table_name, array('id' => intval($_POST['gorev_id'])));
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Görev başarıyla silindi.'));
        } else {
            wp_send_json_error(array('message' => 'Görev silinirken bir hata oluştu.'));
        }
    }
    
    public function update_aksiyon_progress() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Giriş yapmanız gerekiyor.'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        $aksiyon_id = intval($_POST['aksiyon_id']);
        $progress = intval($_POST['progress']);
        
        // Aksiyonun var olduğunu kontrol et
        $aksiyon = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $aksiyon_id));
        
        if (!$aksiyon) {
            wp_send_json_error(array('message' => 'Aksiyon bulunamadı.'));
        }
        
        $result = $wpdb->update(
            $table_name,
            array('ilerleme' => $progress),
            array('id' => $aksiyon_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'İlerleme güncellendi.'));
        } else {
            wp_send_json_error(array('message' => 'İlerleme güncellenirken bir hata oluştu.'));
        }
    }
    
    public function get_user_aksiyonlar($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT a.*, k.kategori_adi, p.performans_adi, u.display_name as kullanici_adi
            FROM $table_name a
            LEFT JOIN {$wpdb->prefix}bkm_kategoriler k ON a.kategori_id = k.id
            LEFT JOIN {$wpdb->prefix}bkm_performanslar p ON a.performans_id = p.id
            LEFT JOIN {$wpdb->users} u ON a.kullanici_id = u.ID
            WHERE a.kullanici_id = %d OR FIND_IN_SET(%d, REPLACE(a.sorumlular, ' ', ''))
            ORDER BY a.created_at DESC
        ", $user_id, $user_id));
    }
    
    public function get_aksiyon_gorevler($aksiyon_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_gorevler';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT g.*, u.display_name as sorumlu_adi
            FROM $table_name g
            LEFT JOIN {$wpdb->users} u ON g.sorumlu_id = u.ID
            WHERE g.aksiyon_id = %d
            ORDER BY g.created_at DESC
        ", $aksiyon_id));
    }
    
    public function can_user_edit_aksiyon($aksiyon_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user = get_user_by('ID', $user_id);
        
        // Yönetici her şeyi düzenleyebilir
        if (in_array('administrator', $user->roles)) {
            return true;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        $aksiyon = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $aksiyon_id));
        
        if (!$aksiyon) {
            return false;
        }
        
        // Aksiyon sahibi düzenleyebilir
        if ($aksiyon->kullanici_id == $user_id) {
            return true;
        }
        
        // Sorumlu kişi düzenleyebilir
        $sorumlular = explode(',', $aksiyon->sorumlular);
        $sorumlular = array_map('trim', $sorumlular);
        
        if (in_array($user_id, $sorumlular)) {
            return true;
        }
        
        return false;
    }
    
    public function complete_action() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Giriş yapmanız gerekiyor.'));
        }
        
        $current_user = wp_get_current_user();
        $aksiyon_id = intval($_POST['aksiyon_id']);
        
        // Aksiyonu tamamlama yetkisi kontrolü
        if (!$this->can_user_edit_aksiyon($aksiyon_id, $current_user->ID)) {
            wp_send_json_error(array('message' => 'Bu aksiyonu tamamlama yetkiniz bulunmuyor.'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'durum' => 'tamamlandi',
                'ilerleme' => 100,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $aksiyon_id)
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Aksiyon başarıyla tamamlandı.'));
        } else {
            wp_send_json_error(array('message' => 'Aksiyon tamamlanırken bir hata oluştu.'));
        }
    }
    
    public function delete_action() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Giriş yapmanız gerekiyor.'));
        }
        
        $current_user = wp_get_current_user();
        
        // Sadece yönetici aksiyon silebilir
        if (!in_array('administrator', $current_user->roles)) {
            wp_send_json_error(array('message' => 'Aksiyon silme yetkiniz bulunmuyor.'));
        }
        
        $aksiyon_id = intval($_POST['aksiyon_id']);
        
        global $wpdb;
        $aksiyon_table = $wpdb->prefix . 'bkm_aksiyonlar';
        $gorev_table = $wpdb->prefix . 'bkm_gorevler';
        
        // Önce bu aksiyona ait tüm görevleri sil
        $wpdb->delete($gorev_table, array('aksiyon_id' => $aksiyon_id));
        
        // Sonra aksiyonu sil
        $result = $wpdb->delete($aksiyon_table, array('id' => $aksiyon_id));
        
        if ($result !== false) {
            wp_send_json_success(array('message' => 'Aksiyon ve bağlı görevler başarıyla silindi.'));
        } else {
            wp_send_json_error(array('message' => 'Aksiyon silinirken bir hata oluştu.'));
        }
    }
    
    public function log_error() {
        if (!wp_verify_nonce($_POST['nonce'], 'bkm_frontend_nonce')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        $error_message = sanitize_text_field($_POST['error_message']);
        $error_type = sanitize_text_field($_POST['error_type']);
        $user_id = get_current_user_id();
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
        $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']);
        
        // WordPress error log'a kaydet
        error_log("BKM Aksiyon Takip Frontend Error: {$error_type} - {$error_message} (User: {$user_id}, IP: {$ip_address}, UA: {$user_agent})");
        
        wp_send_json_success(array('message' => 'Hata kaydedildi.'));
    }
}
