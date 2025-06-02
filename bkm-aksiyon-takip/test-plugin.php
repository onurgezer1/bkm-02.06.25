<?php
/**
 * BKM Aksiyon Takip Plugin Test Dosyası
 * 
 * Bu dosya plugin'in syntax hatalarını ve temel yapısını kontrol eder.
 */

// Temel WordPress fonksiyonlarını simulate et
if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://localhost/wp-content/plugins/bkm-aksiyon-takip/';
    }
}

if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action) {
        return true;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action) {
        return 'test_nonce_' . $action;
    }
}

if (!function_exists('current_time')) {
    function current_time($type) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        // Simulate WordPress add_action
        return true;
    }
}

if (!function_exists('add_shortcode')) {
    function add_shortcode($tag, $callback) {
        // Simulate WordPress add_shortcode
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        return true;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        return true;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated, $plugin_rel_path) {
        return true;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path) {
        return 'http://localhost/wp-admin/' . $path;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script($handle, $src, $deps = array(), $ver = false, $in_footer = false) {
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style($handle, $src, $deps = array(), $ver = false, $media = 'all') {
        return true;
    }
}

if (!function_exists('wp_localize_script')) {
    function wp_localize_script($handle, $object_name, $l10n) {
        return true;
    }
}

if (!function_exists('shortcode_atts')) {
    function shortcode_atts($pairs, $atts, $shortcode = '') {
        return array_merge($pairs, (array) $atts);
    }
}

// Test başlangıcı
echo "<h1>BKM Aksiyon Takip Plugin Test Raporu</h1>\n";
echo "<h2>Dosya Kontrolleri</h2>\n";

$test_files = [
    'bkm-aksiyon-takip.php' => 'Ana plugin dosyası',
    'includes/class-activator.php' => 'Aktivatör sınıfı',
    'includes/class-deactivator.php' => 'Deaktivatör sınıfı',
    'includes/class-database.php' => 'Veritabanı sınıfı',
    'includes/class-admin.php' => 'Admin sınıfı',
    'includes/class-frontend.php' => 'Frontend sınıfı',
    'includes/class-shortcode.php' => 'Shortcode sınıfı',
    'admin/css/admin.css' => 'Admin CSS',
    'admin/js/admin.js' => 'Admin JavaScript',
    'public/css/frontend.css' => 'Frontend CSS',
    'public/js/frontend.js' => 'Frontend JavaScript'
];

$all_files_exist = true;

foreach ($test_files as $file => $description) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        echo "✅ <strong>$description</strong>: $file (var)\n<br>";
    } else {
        echo "❌ <strong>$description</strong>: $file (eksik)\n<br>";
        $all_files_exist = false;
    }
}

echo "<h2>Syntax Kontrolleri</h2>\n";

// Ana plugin dosyasını test et
try {
    include_once 'bkm-aksiyon-takip.php';
    echo "✅ <strong>Ana plugin dosyası</strong>: Syntax hatası yok\n<br>";
} catch (ParseError $e) {
    echo "❌ <strong>Ana plugin dosyası</strong>: Syntax hatası - " . $e->getMessage() . "\n<br>";
    $all_files_exist = false;
} catch (Error $e) {
    echo "⚠️ <strong>Ana plugin dosyası</strong>: Runtime hatası (normal) - " . $e->getMessage() . "\n<br>";
}

// Sınıf dosyalarını test et
$class_files = [
    'includes/class-activator.php' => 'BKM_Aksiyon_Takip_Activator',
    'includes/class-deactivator.php' => 'BKM_Aksiyon_Takip_Deactivator',
    'includes/class-database.php' => 'BKM_Aksiyon_Takip_Database',
    'includes/class-admin.php' => 'BKM_Aksiyon_Takip_Admin',
    'includes/class-frontend.php' => 'BKM_Aksiyon_Takip_Frontend',
    'includes/class-shortcode.php' => 'BKM_Aksiyon_Takip_Shortcode'
];

foreach ($class_files as $file => $class_name) {
    try {
        include_once $file;
        if (class_exists($class_name)) {
            echo "✅ <strong>$class_name</strong>: Syntax hatası yok ve sınıf tanımlı\n<br>";
        } else {
            echo "⚠️ <strong>$class_name</strong>: Sınıf bulunamadı\n<br>";
        }
    } catch (ParseError $e) {
        echo "❌ <strong>$class_name</strong>: Syntax hatası - " . $e->getMessage() . "\n<br>";
        $all_files_exist = false;
    } catch (Error $e) {
        echo "⚠️ <strong>$class_name</strong>: Runtime hatası (normal) - " . $e->getMessage() . "\n<br>";
    }
}

echo "<h2>Genel Durum</h2>\n";

if ($all_files_exist) {
    echo "✅ <strong>Plugin hazır durumda!</strong> Tüm dosyalar mevcut ve syntax hataları yok.\n<br>";
    echo "<p>Plugin WordPress ortamında test edilmeye hazır.</p>\n";
} else {
    echo "❌ <strong>Plugin'de eksiklikler var!</strong> Yukarıdaki hataları düzeltip tekrar test edin.\n<br>";
}

echo "\n<h2>Önerilen Test Adımları</h2>\n";
echo "<ol>\n";
echo "<li>Bu plugin dosyalarını WordPress wp-content/plugins/ klasörüne kopyalayın</li>\n";
echo "<li>WordPress admin panelinden plugin'i aktif edin</li>\n";
echo "<li>Admin menüsünde 'BKM Aksiyon Takip' bölümünü kontrol edin</li>\n";
echo "<li>Shortcode'ları bir sayfada test edin: [bkm_aksiyon_dashboard], [bkm_aksiyon_list], vb.</li>\n";
echo "<li>Frontend işlevselliğini test edin</li>\n";
echo "</ol>\n";
?>
