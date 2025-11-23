<?php
/**
 * Settings Helper Functions
 * File untuk memudahkan mengakses settings dari database
 */

// Cache untuk settings
$_settings_cache = [];

/**
 * Ambil setting dari database dengan cache
 * @param string $key Setting key (atau 'all' untuk semua)
 * @return mixed Setting value atau array
 */
function get_setting($key = 'all') {
    global $koneksi, $_settings_cache;
    
    // Check jika table ada
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'website_settings'");
    if (mysqli_num_rows($check_table) == 0) {
        return null;
    }
    
    // Load semua settings ke cache jika belum ada
    if (empty($_settings_cache)) {
        $result = mysqli_query($koneksi, "SELECT * FROM website_settings WHERE id = 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $_settings_cache['website'] = $row;
        }
        
        $result = mysqli_query($koneksi, "SELECT * FROM pengaturan_tampilan WHERE id = 1");
        if ($result && $row = mysqli_fetch_assoc($result)) {
            $_settings_cache['display'] = $row;
        }
    }
    
    if ($key === 'all') {
        return $_settings_cache;
    }
    
    // Check jika key ada di format 'section.key'
    if (strpos($key, '.') !== false) {
        list($section, $subkey) = explode('.', $key, 2);
        return $_settings_cache[$section][$subkey] ?? null;
    }
    
    // Search di semua section
    foreach ($_settings_cache as $section) {
        if (isset($section[$key])) {
            return $section[$key];
        }
    }
    
    return null;
}

/**
 * Ambil website settings
 * @return array
 */
function get_website_settings() {
    global $koneksi;
    
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'website_settings'");
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $result = mysqli_query($koneksi, "SELECT * FROM website_settings WHERE id = 1");
    return $result ? mysqli_fetch_assoc($result) : [];
}

/**
 * Ambil display settings
 * @return array
 */
function get_display_settings() {
    global $koneksi;
    
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'pengaturan_tampilan'");
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $result = mysqli_query($koneksi, "SELECT * FROM pengaturan_tampilan WHERE id = 1");
    return $result ? mysqli_fetch_assoc($result) : [];
}

/**
 * Ambil menu navbar yang aktif
 * @return array
 */
function get_navbar_menu() {
    global $koneksi;
    
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'menu_navbar'");
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $result = mysqli_query($koneksi, "SELECT * FROM menu_navbar WHERE aktif = 1 ORDER BY urutan ASC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Ambil sidebar menu yang aktif
 * @return array
 */
function get_sidebar_menu() {
    global $koneksi;
    
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'sidebar_menu'");
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $result = mysqli_query($koneksi, "SELECT * FROM sidebar_menu WHERE aktif = 1 ORDER BY urutan ASC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Ambil homepage sections yang aktif
 * @return array
 */
function get_homepage_sections() {
    global $koneksi;
    
    $check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'homepage_sections'");
    if (mysqli_num_rows($check_table) == 0) {
        return [];
    }
    
    $result = mysqli_query($koneksi, "SELECT * FROM homepage_sections WHERE aktif = 1 ORDER BY urutan ASC");
    return $result ? mysqli_fetch_all($result, MYSQLI_ASSOC) : [];
}

/**
 * Render navbar HTML
 * @param string $nav_class CSS class untuk nav
 * @return string HTML navbar
 */
function render_navbar($nav_class = 'nav-menu') {
    $menu_items = get_navbar_menu();
    $html = '<nav class="' . htmlspecialchars($nav_class) . '">' . "\n";
    
    foreach ($menu_items as $item) {
        $active = isset($_GET['page']) && $_GET['page'] === $item['page_id'] ? 'active' : '';
        $icon = $item['icon'] ? '<i class="fa ' . htmlspecialchars($item['icon']) . '"></i>' : '';
        $html .= '<a href="' . htmlspecialchars($item['url']) . '" class="nav-item ' . $active . '">';
        $html .= $icon . '<span>' . htmlspecialchars($item['label']) . '</span></a>' . "\n";
    }
    
    $html .= '</nav>';
    return $html;
}

/**
 * Render sidebar menu HTML
 * @param string $sidebar_class CSS class untuk sidebar
 * @return string HTML sidebar
 */
function render_sidebar_menu($sidebar_class = 'sidebar-menu') {
    $menu_items = get_sidebar_menu();
    $html = '<ul class="' . htmlspecialchars($sidebar_class) . '">' . "\n";
    
    foreach ($menu_items as $item) {
        $icon = $item['icon'] ? '<i class="' . htmlspecialchars($item['icon']) . '"></i>' : '';
        $html .= '<li class="sidebar-item">';
        $html .= $icon . '<span>' . htmlspecialchars($item['label']) . '</span></li>' . "\n";
    }
    
    $html .= '</ul>';
    return $html;
}

/**
 * Update setting value
 * @param string $key Setting key
 * @param mixed $value New value
 * @return bool Success or failed
 */
function update_setting($key, $value) {
    global $koneksi, $_settings_cache;
    
    // Determine which table and field
    $website_fields = ['nama_website', 'deskripsi_website', 'logo', 'favicon', 'email_kontak', 
                       'telepon', 'alamat', 'jam_kerja', 'link_whatsapp', 'link_facebook', 
                       'link_instagram', 'link_youtube', 'link_tiktok'];
    
    $display_fields = ['warna_utama', 'warna_sekunder', 'font_utama', 'tampilkan_breadcrumb', 
                       'tampilkan_search_bar', 'tampilkan_footer_newsletter', 'items_per_page'];
    
    if (in_array($key, $website_fields)) {
        $query = "UPDATE website_settings SET " . $key . " = ? WHERE id = 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $value);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear cache
            unset($_settings_cache['website']);
            return true;
        }
    } elseif (in_array($key, $display_fields)) {
        $query = "UPDATE pengaturan_tampilan SET " . $key . " = ? WHERE id = 1";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "s", $value);
        
        if (mysqli_stmt_execute($stmt)) {
            // Clear cache
            unset($_settings_cache['display']);
            return true;
        }
    }
    
    return false;
}

/**
 * Check jika setting option aktif
 * @param string $option Option name
 * @return bool
 */
function is_setting_enabled($option) {
    return (bool) get_setting($option);
}
?>
