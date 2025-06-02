<?php
class BKM_Aksiyon_Takip_Admin {
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_post_bkm_aksiyon_save', array($this, 'save_aksiyon'));
        add_action('admin_post_bkm_kategori_save', array($this, 'save_kategori'));
        add_action('admin_post_bkm_performans_save', array($this, 'save_performans'));
    }
    
    public function add_admin_menus() {
        add_menu_page('BKM Aksiyonlar', 'BKM Aksiyonlar', 'manage_options', 'bkm-aksiyonlar', array($this, 'aksiyon_list_page'), 'dashicons-list-view');
        add_submenu_page('bkm-aksiyonlar', 'Tüm Aksiyonlar', 'Tüm Aksiyonlar', 'manage_options', 'bkm-aksiyonlar', array($this, 'aksiyon_list_page'));
        add_submenu_page('bkm-aksiyonlar', 'Aksiyon Ekle', 'Aksiyon Ekle', 'manage_options', 'bkm-aksiyon-ekle', array($this, 'aksiyon_form_page'));
        add_submenu_page('bkm-aksiyonlar', 'Kategoriler', 'Kategoriler', 'manage_options', 'bkm-kategoriler', array($this, 'kategori_page'));
        add_submenu_page('bkm-aksiyonlar', 'Performanslar', 'Performanslar', 'manage_options', 'bkm-performanslar', array($this, 'performans_page'));
        add_submenu_page('bkm-aksiyonlar', 'Raporlar', 'Raporlar', 'manage_options', 'bkm-raporlar', array($this, 'raporlar_page'));
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'bkm-') !== false) {
            wp_enqueue_script('bkm-admin-js', plugin_dir_url(__DIR__) . 'admin/js/admin.js', array('jquery'), '1.0.0', true);
            wp_enqueue_style('bkm-admin-css', plugin_dir_url(__DIR__) . 'admin/css/admin.css', array(), '1.0.0');
            wp_localize_script('bkm-admin-js', 'bkm_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('bkm_nonce')
            ));
        }
    }
    
    public function aksiyon_list_page() {
        include plugin_dir_path(__DIR__) . 'admin/views/aksiyon-list.php';
    }
    
    public function aksiyon_form_page() {
        include plugin_dir_path(__DIR__) . 'admin/views/aksiyon-form.php';
    }
    
    public function kategori_page() {
        include plugin_dir_path(__DIR__) . 'admin/views/kategori-list.php';
    }
    
    public function performans_page() {
        include plugin_dir_path(__DIR__) . 'admin/views/performans-list.php';
    }
    
    public function raporlar_page() {
        include plugin_dir_path(__DIR__) . 'admin/views/raporlar.php';
    }
    
    public function save_aksiyon() {
        if (!wp_verify_nonce($_POST['bkm_nonce'], 'bkm_save_aksiyon')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        
        $data = array(
            'kullanici_id' => sanitize_text_field($_POST['kullanici_id']),
            'onem_derecesi' => sanitize_text_field($_POST['onem_derecesi']),
            'acilma_tarihi' => sanitize_text_field($_POST['acilma_tarihi']),
            'hafta' => sanitize_text_field($_POST['hafta']),
            'kategori_id' => sanitize_text_field($_POST['kategori_id']),
            'sorumlular' => sanitize_textarea_field($_POST['sorumlular']),
            'tespit_nedeni' => sanitize_textarea_field($_POST['tespit_nedeni']),
            'aciklama' => sanitize_textarea_field($_POST['aciklama']),
            'hedef_tarih' => sanitize_text_field($_POST['hedef_tarih']),
            'kapanma_tarihi' => sanitize_text_field($_POST['kapanma_tarihi']),
            'performans_id' => sanitize_text_field($_POST['performans_id']),
            'ilerleme' => intval($_POST['ilerleme']),
            'notlar' => sanitize_textarea_field($_POST['notlar'])
        );
        
        if (isset($_POST['aksiyon_id']) && !empty($_POST['aksiyon_id'])) {
            $wpdb->update($table_name, $data, array('id' => intval($_POST['aksiyon_id'])));
        } else {
            $data['sira_no'] = $this->get_next_sira_no();
            $wpdb->insert($table_name, $data);
        }
        
        wp_redirect(admin_url('admin.php?page=bkm-aksiyonlar&message=saved'));
        exit;
    }
    
    public function save_kategori() {
        if (!wp_verify_nonce($_POST['bkm_nonce'], 'bkm_save_kategori')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_kategoriler';
        
        $data = array(
            'kategori_adi' => sanitize_text_field($_POST['kategori_adi'])
        );
        
        if (isset($_POST['kategori_id']) && !empty($_POST['kategori_id'])) {
            $wpdb->update($table_name, $data, array('id' => intval($_POST['kategori_id'])));
        } else {
            $wpdb->insert($table_name, $data);
        }
        
        wp_redirect(admin_url('admin.php?page=bkm-kategoriler&message=saved'));
        exit;
    }
    
    public function save_performans() {
        if (!wp_verify_nonce($_POST['bkm_nonce'], 'bkm_save_performans')) {
            wp_die('Güvenlik kontrolü başarısız.');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_performanslar';
        
        $data = array(
            'performans_adi' => sanitize_text_field($_POST['performans_adi'])
        );
        
        if (isset($_POST['performans_id']) && !empty($_POST['performans_id'])) {
            $wpdb->update($table_name, $data, array('id' => intval($_POST['performans_id'])));
        } else {
            $wpdb->insert($table_name, $data);
        }
        
        wp_redirect(admin_url('admin.php?page=bkm-performanslar&message=saved'));
        exit;
    }
    
    private function get_next_sira_no() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_aksiyonlar';
        $result = $wpdb->get_var("SELECT MAX(sira_no) FROM $table_name");
        return $result ? $result + 1 : 1;
    }
    
    public function get_kategoriler() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_kategoriler';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY kategori_adi");
    }
    
    public function get_performanslar() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'bkm_performanslar';
        return $wpdb->get_results("SELECT * FROM $table_name ORDER BY performans_adi");
    }
    
    public function get_wordpress_users() {
        return get_users(array('orderby' => 'display_name'));
    }
}
new BKM_Aksiyon_Takip_Admin();
