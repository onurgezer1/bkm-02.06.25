<?php
class BKM_Aksiyon_Takip_Database {
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
        $table_aksiyonlar = $wpdb->prefix . 'bkm_aksiyonlar';
        $table_kategoriler = $wpdb->prefix . 'bkm_kategoriler';
        $table_performanslar = $wpdb->prefix . 'bkm_performanslar';
        $table_gorevler = $wpdb->prefix . 'bkm_gorevler';

        $sql = [];
        $sql[] = "CREATE TABLE $table_aksiyonlar (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            kullanici_id BIGINT UNSIGNED,
            sira_no INT,
            onem_derecesi TINYINT,
            acilma_tarihi DATE,
            hafta INT,
            kategori_id BIGINT UNSIGNED,
            sorumlular TEXT,
            tespit_nedeni TEXT,
            aciklama TEXT,
            hedef_tarih DATE,
            kapanma_tarihi DATE,
            performans_id BIGINT UNSIGNED,
            ilerleme INT DEFAULT 0,
            notlar TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_kullanici_id (kullanici_id),
            KEY idx_kategori_id (kategori_id),
            KEY idx_performans_id (performans_id),
            KEY idx_onem_derecesi (onem_derecesi),
            KEY idx_acilma_tarihi (acilma_tarihi),
            KEY idx_hedef_tarih (hedef_tarih)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE $table_kategoriler (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            kategori_adi VARCHAR(255) NOT NULL,
            aciklama TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_kategori_adi (kategori_adi)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE $table_performanslar (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            performans_adi VARCHAR(255) NOT NULL,
            aciklama TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uk_performans_adi (performans_adi)
        ) $charset_collate;";
        
        $sql[] = "CREATE TABLE $table_gorevler (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            aksiyon_id BIGINT UNSIGNED NOT NULL,
            gorev_adi VARCHAR(255) NOT NULL,
            aciklama TEXT,
            sorumlu_id BIGINT UNSIGNED,
            durum TINYINT DEFAULT 0,
            baslangic_tarihi DATE,
            bitis_tarihi DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_aksiyon_id (aksiyon_id),
            KEY idx_sorumlu_id (sorumlu_id),
            KEY idx_durum (durum)
        ) $charset_collate;";
        
        foreach ($sql as $query) {
            dbDelta($query);
        }
        
        // Insert sample data
        self::insert_sample_data();
    }
    
    public static function insert_sample_data() {
        global $wpdb;
        
        $table_kategoriler = $wpdb->prefix . 'bkm_kategoriler';
        $table_performanslar = $wpdb->prefix . 'bkm_performanslar';
        
        // Check if sample data already exists
        $kategori_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_kategoriler");
        $performans_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_performanslar");
        
        if ($kategori_count == 0) {
            $sample_kategoriler = array(
                array('kategori_adi' => 'Kalite Yönetimi', 'aciklama' => 'Ürün ve hizmet kalitesi ile ilgili aksiyonlar'),
                array('kategori_adi' => 'İş Güvenliği', 'aciklama' => 'Çalışan güvenliği ve iş sağlığı aksiyonları'),
                array('kategori_adi' => 'Çevre Yönetimi', 'aciklama' => 'Çevresel etki ve sürdürülebilirlik aksiyonları'),
                array('kategori_adi' => 'Bilgi Güvenliği', 'aciklama' => 'Veri güvenliği ve siber güvenlik aksiyonları'),
                array('kategori_adi' => 'İnsan Kaynakları', 'aciklama' => 'Personel yönetimi ve eğitim aksiyonları'),
                array('kategori_adi' => 'Süreç İyileştirme', 'aciklama' => 'Operasyonel verimlilik ve süreç optimizasyonu'),
                array('kategori_adi' => 'Müşteri Memnuniyeti', 'aciklama' => 'Müşteri deneyimi ve memnuniyet artırma'),
                array('kategori_adi' => 'Teknoloji', 'aciklama' => 'IT altyapı ve teknoloji geliştirme aksiyonları')
            );
            
            foreach ($sample_kategoriler as $kategori) {
                $wpdb->insert($table_kategoriler, $kategori);
            }
        }
        
        if ($performans_count == 0) {
            $sample_performanslar = array(
                array('performans_adi' => 'Mükemmel', 'aciklama' => 'Hedefleri aşan performans'),
                array('performans_adi' => 'İyi', 'aciklama' => 'Hedeflere ulaşan performans'),
                array('performans_adi' => 'Orta', 'aciklama' => 'Kabul edilebilir performans'),
                array('performans_adi' => 'Zayıf', 'aciklama' => 'Hedeflerin altında performans'),
                array('performans_adi' => 'Beklemede', 'aciklama' => 'Henüz değerlendirilmemiş')
            );
            
            foreach ($sample_performanslar as $performans) {
                $wpdb->insert($table_performanslar, $performans);
            }
        }
    }
    
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'bkm_gorevler',
            $wpdb->prefix . 'bkm_aksiyonlar',
            $wpdb->prefix . 'bkm_kategoriler',
            $wpdb->prefix . 'bkm_performanslar'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    public static function get_table_status() {
        global $wpdb;
        
        $tables = array(
            'aksiyonlar' => $wpdb->prefix . 'bkm_aksiyonlar',
            'kategoriler' => $wpdb->prefix . 'bkm_kategoriler', 
            'performanslar' => $wpdb->prefix . 'bkm_performanslar',
            'gorevler' => $wpdb->prefix . 'bkm_gorevler'
        );
        
        $status = array();
        
        foreach ($tables as $key => $table) {
            $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
            $count = 0;
            
            if ($exists) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            }
            
            $status[$key] = array(
                'exists' => $exists,
                'count' => $count,
                'table_name' => $table
            );
        }
        
        return $status;
    }
}
