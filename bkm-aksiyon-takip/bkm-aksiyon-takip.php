<?php
/*
Plugin Name: BKM Aksiyon Takip
Description: WordPress tabanlı aksiyon ve görev yönetim sistemi.
Version: 1.0.0
Author: onurgezer1
Text Domain: bkm-aksiyon-takip
Domain Path: /languages
*/

// Plugin ana dosyası
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Plugin constants
define( 'BKM_AKSIYON_TAKIP_VERSION', '1.0.0' );
define( 'BKM_AKSIYON_TAKIP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BKM_AKSIYON_TAKIP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-database.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-frontend.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-shortcode.php';

register_activation_hook( __FILE__, array( 'BKM_Aksiyon_Takip_Activator', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'BKM_Aksiyon_Takip_Deactivator', 'deactivate' ) );

// Initialize the plugin
function bkm_aksiyon_takip_init() {
    // Load text domain for translations
    load_plugin_textdomain( 'bkm-aksiyon-takip', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // Initialize admin if in admin area
    if ( is_admin() ) {
        new BKM_Aksiyon_Takip_Admin();
    }
    
    // Initialize frontend
    new BKM_Aksiyon_Takip_Frontend();
    new BKM_Aksiyon_Takip_Shortcode();
}

add_action( 'plugins_loaded', 'bkm_aksiyon_takip_init' );
