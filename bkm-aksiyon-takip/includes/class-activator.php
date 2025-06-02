<?php
class BKM_Aksiyon_Takip_Activator {
    public static function activate() {
        require_once plugin_dir_path( __FILE__ ) . 'class-database.php';
        BKM_Aksiyon_Takip_Database::create_tables();
    }
}
