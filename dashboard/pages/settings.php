<?php
// Cek apakah tabel settings sudah ada
$check_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'website_settings'");
if (mysqli_num_rows($check_table) == 0) {
    ?>
    <div style="padding: 2rem; background: #fff3cd; border-radius: 8px; margin-bottom: 2rem;">
        <h3 style="color: #856404; margin-top: 0;">
            <i class="fa fa-exclamation-triangle"></i> Setup Diperlukan
        </h3>
        <p style="color: #856404;">Tabel pengaturan belum dibuat. Silakan jalankan setup terlebih dahulu:</p>
        <p>
            <a href="<?php echo $base; ?>/config/execute_settings_schema.php?token=setup_settings_20251123" 
               class="btn btn-primary" style="display: inline-block; padding: 10px 20px; background: #354024; color: white; text-decoration: none; border-radius: 4px;">
                <i class="fa fa-database"></i> Jalankan Setup Database
            </a>
        </p>
        <p style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
            Setelah setup selesai, refresh halaman ini.
        </p>
    </div>
    <?php
    return;
}

// Ambil data general settings
$general_settings = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM website_settings WHERE id = 1"));

// Ambil data menu navbar
$menu_navbar = mysqli_query($koneksi, "SELECT * FROM menu_navbar ORDER BY urutan ASC");
$menu_navbar_data = mysqli_fetch_all($menu_navbar, MYSQLI_ASSOC);

// Ambil data sidebar menu
$sidebar_menu = mysqli_query($koneksi, "SELECT * FROM sidebar_menu ORDER BY urutan ASC");
$sidebar_menu_data = mysqli_fetch_all($sidebar_menu, MYSQLI_ASSOC);

// Ambil data display settings
$display_settings = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengaturan_tampilan WHERE id = 1"));

// Ambil data homepage sections
$homepage_sections = mysqli_query($koneksi, "SELECT * FROM homepage_sections ORDER BY urutan ASC");
$homepage_sections_data = mysqli_fetch_all($homepage_sections, MYSQLI_ASSOC);

$current_tab = $_GET['tab'] ?? 'general';
?>

<style>
.settings-container {
    max-width: 1200px;
}

.nav-tabs .nav-link {
    color: #666;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    color: var(--dark-green);
    border-bottom-color: var(--dark-green);
}

.nav-tabs .nav-link.active {
    color: var(--dark-green);
    border-bottom-color: var(--dark-green);
    background: none;
}

.tab-content {
    padding-top: 2rem;
}

.section-title {
    font-size: 1.5rem;
    color: var(--dark-green);
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-section {
    background: #fff;
    padding: 2rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="url"],
.form-group input[type="tel"],
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: inherit;
    font-size: 0.95rem;
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.form-group small {
    display: block;
    margin-top: 0.3rem;
    color: #666;
    font-size: 0.85rem;
}

.form-check {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 1rem 0;
}

.form-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.form-check label {
    margin: 0;
    cursor: pointer;
    font-weight: normal;
}

.table-menu {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}

.table-menu thead {
    background: #f8f9fa;
}

.table-menu th,
.table-menu td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.table-menu th {
    font-weight: 600;
    color: var(--dark-green);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
}

.btn-success {
    background: var(--dark-green);
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.75rem 1.5rem;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-success:hover {
    background: #2a3219;
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.btn-primary {
    background: #0066cc;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-primary:hover {
    background: #0052a3;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.9rem;
}

.btn-danger:hover {
    background: #c82333;
}

.color-preview {
    display: inline-block;
    width: 40px;
    height: 40px;
    border-radius: 4px;
    margin-left: 0.5rem;
    border: 2px solid #ddd;
}

.alert {
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.menu-item-row {
    background: #f9f9f9;
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 4px;
    border-left: 4px solid var(--dark-green);
}

.menu-item-form {
    display: grid;
    grid-template-columns: 1fr 2fr 1fr auto;
    gap: 1rem;
    align-items: end;
}

@media (max-width: 768px) {
    .menu-item-form {
        grid-template-columns: 1fr;
    }
    
    .nav-tabs {
        flex-wrap: wrap;
    }
    
    .nav-tabs .nav-link {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
}
</style>

<div class="settings-container">
    <h2 class="section-title">
        <i class="fa fa-cog"></i> Pengaturan Website
    </h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <!-- TABS NAVIGATION -->
    <ul class="nav nav-tabs" role="tablist" style="border-bottom: 2px solid #e9ecef; margin-bottom: 0;">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab == 'general' ? 'active' : ''; ?>" 
               href="?page=settings&tab=general">
                <i class="fa fa-info-circle"></i> Informasi Umum
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab == 'navbar' ? 'active' : ''; ?>" 
               href="?page=settings&tab=navbar">
                <i class="fa fa-bars"></i> Menu Navbar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab == 'sidebar' ? 'active' : ''; ?>" 
               href="?page=settings&tab=sidebar">
                <i class="fa fa-list"></i> Menu Sidebar
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab == 'display' ? 'active' : ''; ?>" 
               href="?page=settings&tab=display">
                <i class="fa fa-palette"></i> Tampilan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_tab == 'homepage' ? 'active' : ''; ?>" 
               href="?page=settings&tab=homepage">
                <i class="fa fa-home"></i> Homepage
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- TAB: GENERAL SETTINGS -->
        <?php if ($current_tab == 'general'): ?>
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">
                <i class="fa fa-globe"></i> Informasi Umum Website
            </h3>
            
            <form method="POST" action="?page=settings" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nama_website"><i class="fa fa-heading"></i> Nama Website</label>
                    <input type="text" id="nama_website" name="nama_website" 
                           value="<?php echo htmlspecialchars($general_settings['nama_website']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi_website"><i class="fa fa-align-left"></i> Deskripsi Website</label>
                    <textarea id="deskripsi_website" name="deskripsi_website"><?php echo htmlspecialchars($general_settings['deskripsi_website']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="email_kontak"><i class="fa fa-envelope"></i> Email Kontak</label>
                    <input type="email" id="email_kontak" name="email_kontak" 
                           value="<?php echo htmlspecialchars($general_settings['email_kontak']); ?>">
                </div>

                <div class="form-group">
                    <label for="telepon"><i class="fa fa-phone"></i> Nomor Telepon</label>
                    <input type="tel" id="telepon" name="telepon" 
                           value="<?php echo htmlspecialchars($general_settings['telepon']); ?>">
                </div>

                <div class="form-group">
                    <label for="alamat"><i class="fa fa-map-marker"></i> Alamat</label>
                    <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($general_settings['alamat']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="jam_kerja"><i class="fa fa-clock"></i> Jam Kerja</label>
                    <input type="text" id="jam_kerja" name="jam_kerja" placeholder="Contoh: Senin-Jumat 08:00-17:00"
                           value="<?php echo htmlspecialchars($general_settings['jam_kerja']); ?>">
                </div>

                <hr style="margin: 2rem 0;">

                <h4 style="margin: 1.5rem 0 1rem 0; color: var(--dark-green);">
                    <i class="fa fa-share-alt"></i> Media Sosial & Link
                </h4>

                <div class="form-group">
                    <label for="link_whatsapp"><i class="fab fa-whatsapp"></i> Link WhatsApp</label>
                    <input type="url" id="link_whatsapp" name="link_whatsapp" 
                           placeholder="https://wa.me/6281234567890"
                           value="<?php echo htmlspecialchars($general_settings['link_whatsapp']); ?>">
                </div>

                <div class="form-group">
                    <label for="link_facebook"><i class="fab fa-facebook"></i> Link Facebook</label>
                    <input type="url" id="link_facebook" name="link_facebook" 
                           placeholder="https://facebook.com/..."
                           value="<?php echo htmlspecialchars($general_settings['link_facebook']); ?>">
                </div>

                <div class="form-group">
                    <label for="link_instagram"><i class="fab fa-instagram"></i> Link Instagram</label>
                    <input type="url" id="link_instagram" name="link_instagram" 
                           placeholder="https://instagram.com/..."
                           value="<?php echo htmlspecialchars($general_settings['link_instagram']); ?>">
                </div>

                <div class="form-group">
                    <label for="link_youtube"><i class="fab fa-youtube"></i> Link YouTube</label>
                    <input type="url" id="link_youtube" name="link_youtube" 
                           placeholder="https://youtube.com/..."
                           value="<?php echo htmlspecialchars($general_settings['link_youtube']); ?>">
                </div>

                <div class="form-group">
                    <label for="link_tiktok"><i class="fab fa-tiktok"></i> Link TikTok</label>
                    <input type="url" id="link_tiktok" name="link_tiktok" 
                           placeholder="https://tiktok.com/..."
                           value="<?php echo htmlspecialchars($general_settings['link_tiktok']); ?>">
                </div>

                <button type="submit" name="update_general_settings" class="btn-success">
                    <i class="fa fa-save"></i> Simpan Pengaturan
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- TAB: NAVBAR MENU -->
        <?php if ($current_tab == 'navbar'): ?>
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">
                <i class="fa fa-bars"></i> Kelola Menu Navbar
            </h3>

            <form method="POST" action="?page=settings">
                <h4 style="margin-bottom: 1rem; color: #555;">Tambah Menu Baru</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label for="label">Nama Menu</label>
                        <input type="text" id="label" name="label" placeholder="Contoh: Tentang Kami" required>
                    </div>
                    <div class="form-group">
                        <label for="url">URL / Link</label>
                        <input type="text" id="url" name="url" placeholder="Contoh: about.php atau index.php#about" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label for="icon">Icon (FontAwesome)</label>
                        <input type="text" id="icon" name="icon" placeholder="Contoh: fa-info-circle">
                    </div>
                    <div class="form-group">
                        <label for="tipe_menu">Tipe Menu</label>
                        <select name="tipe_menu">
                            <option value="halaman_internal">Halaman Internal</option>
                            <option value="link_eksternal">Link Eksternal</option>
                            <option value="kategori">Kategori</option>
                        </select>
                    </div>
                </div>

                <button type="submit" name="tambah_menu_navbar" class="btn-success">
                    <i class="fa fa-plus"></i> Tambah Menu
                </button>
            </form>

            <hr style="margin: 2rem 0;">

            <h4 style="margin-bottom: 1rem; color: #555;">Daftar Menu</h4>

            <table class="table-menu">
                <thead>
                    <tr>
                        <th width="30">#</th>
                        <th>Nama Menu</th>
                        <th>Link</th>
                        <th>Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menu_navbar_data as $index => $menu): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td>
                            <form method="POST" action="?page=settings" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                                <input type="text" name="label" value="<?php echo htmlspecialchars($menu['label']); ?>" 
                                       style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        </td>
                        <td>
                                <input type="text" name="url" value="<?php echo htmlspecialchars($menu['url']); ?>" 
                                       style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        </td>
                        <td style="text-align: center;">
                            <select name="aktif" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                                <option value="1" <?php echo $menu['aktif'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$menu['aktif'] ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </td>
                        <td>
                            <input type="hidden" name="icon" value="<?php echo htmlspecialchars($menu['icon']); ?>">
                            <input type="hidden" name="tipe_menu" value="<?php echo htmlspecialchars($menu['tipe_menu']); ?>">
                            <button type="submit" name="update_menu_navbar" class="btn-primary" style="margin-right: 0.25rem;">
                                <i class="fa fa-save"></i> Update
                            </button>
                            </form>
                            <a href="?page=settings&tab=navbar&delete_navbar=<?php echo $menu['id']; ?>" 
                               class="btn-danger" onclick="return confirm('Yakin hapus menu ini?')">
                                <i class="fa fa-trash"></i> Hapus
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- TAB: SIDEBAR MENU -->
        <?php if ($current_tab == 'sidebar'): ?>
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">
                <i class="fa fa-list"></i> Kelola Menu Sidebar Admin
            </h3>

            <p style="color: #666; margin-bottom: 1.5rem;">
                Edit label menu pada sidebar admin. Menu akan ditampilkan sesuai urutan dan status aktif.
            </p>

            <?php foreach ($sidebar_menu_data as $menu): ?>
            <div class="menu-item-row">
                <form method="POST" action="?page=settings">
                    <div class="menu-item-form">
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: #666; margin-bottom: 0.3rem;">Label Menu</label>
                            <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                            <input type="text" name="label" value="<?php echo htmlspecialchars($menu['label']); ?>" 
                                   style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; color: #666; margin-bottom: 0.3rem;">Status</label>
                            <select name="aktif" style="padding: 0.6rem; border: 1px solid #ddd; border-radius: 4px; width: 100%;">
                                <option value="1" <?php echo $menu['aktif'] ? 'selected' : ''; ?>>Aktif</option>
                                <option value="0" <?php echo !$menu['aktif'] ? 'selected' : ''; ?>>Nonaktif</option>
                            </select>
                        </div>
                        <button type="submit" name="update_sidebar_menu" class="btn-primary">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- TAB: DISPLAY SETTINGS -->
        <?php if ($current_tab == 'display'): ?>
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">
                <i class="fa fa-palette"></i> Pengaturan Tampilan Website
            </h3>

            <form method="POST" action="?page=settings">
                <h4 style="margin-bottom: 1rem; color: #555;">Warna & Font</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                    <div class="form-group">
                        <label for="warna_utama">
                            <i class="fa fa-palette"></i> Warna Utama
                        </label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <input type="color" id="warna_utama" name="warna_utama" 
                                   value="<?php echo htmlspecialchars($display_settings['warna_utama']); ?>"
                                   style="width: 100px; height: 50px; border: none; border-radius: 4px; cursor: pointer;">
                            <code><?php echo htmlspecialchars($display_settings['warna_utama']); ?></code>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="warna_sekunder">
                            <i class="fa fa-palette"></i> Warna Sekunder
                        </label>
                        <div style="display: flex; align-items: center; gap: 1rem;">
                            <input type="color" id="warna_sekunder" name="warna_sekunder" 
                                   value="<?php echo htmlspecialchars($display_settings['warna_sekunder']); ?>"
                                   style="width: 100px; height: 50px; border: none; border-radius: 4px; cursor: pointer;">
                            <code><?php echo htmlspecialchars($display_settings['warna_sekunder']); ?></code>
                        </div>
                    </div>
                </div>

                <hr style="margin: 2rem 0;">

                <h4 style="margin-bottom: 1rem; color: #555;">Tampilan Komponen</h4>

                <div class="form-check">
                    <input type="checkbox" id="tampilkan_breadcrumb" name="tampilkan_breadcrumb" 
                           <?php echo $display_settings['tampilkan_breadcrumb'] ? 'checked' : ''; ?>>
                    <label for="tampilkan_breadcrumb">Tampilkan Breadcrumb di halaman</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="tampilkan_search_bar" name="tampilkan_search_bar" 
                           <?php echo $display_settings['tampilkan_search_bar'] ? 'checked' : ''; ?>>
                    <label for="tampilkan_search_bar">Tampilkan Search Bar di header</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" id="tampilkan_footer_newsletter" name="tampilkan_footer_newsletter" 
                           <?php echo $display_settings['tampilkan_footer_newsletter'] ? 'checked' : ''; ?>>
                    <label for="tampilkan_footer_newsletter">Tampilkan Newsletter Subscribe di footer</label>
                </div>

                <hr style="margin: 2rem 0;">

                <h4 style="margin-bottom: 1rem; color: #555;">Pengaturan Konten</h4>

                <div class="form-group">
                    <label for="items_per_page">Items per Halaman (Pagination)</label>
                    <input type="number" id="items_per_page" name="items_per_page" min="1" max="50"
                           value="<?php echo $display_settings['items_per_page']; ?>">
                </div>

                <button type="submit" name="update_display_settings" class="btn-success">
                    <i class="fa fa-save"></i> Simpan Pengaturan Tampilan
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- TAB: HOMEPAGE SECTIONS -->
        <?php if ($current_tab == 'homepage'): ?>
        <div class="form-section">
            <h3 style="margin-bottom: 1.5rem; color: var(--dark-green);">
                <i class="fa fa-home"></i> Kelola Section Homepage
            </h3>

            <p style="color: #666; margin-bottom: 1.5rem;">
                Pilih section mana saja yang ingin ditampilkan di halaman utama website.
            </p>

            <form method="POST" action="?page=settings">
                <?php foreach ($homepage_sections_data as $section): ?>
                <div class="menu-item-row">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <input type="checkbox" name="section_aktif[<?php echo $section['id']; ?>]" 
                               value="1" 
                               <?php echo $section['aktif'] ? 'checked' : ''; ?>
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0; color: var(--dark-green);">
                                <i class="fa fa-layers"></i> <?php echo htmlspecialchars($section['nama_section']); ?>
                            </h5>
                            <p style="margin: 0.3rem 0 0 0; color: #666; font-size: 0.9rem;">
                                <?php echo htmlspecialchars($section['judul'] ?? ''); ?>
                            </p>
                        </div>
                        <span style="padding: 0.4rem 0.8rem; background: <?php echo $section['aktif'] ? '#d4edda' : '#f8d7da'; ?>; 
                                     color: <?php echo $section['aktif'] ? '#155724' : '#721c24'; ?>; 
                                     border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                            <?php echo $section['aktif'] ? 'Aktif' : 'Nonaktif'; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>

                <button type="submit" name="update_homepage_sections" class="btn-success" style="margin-top: 1.5rem;">
                    <i class="fa fa-save"></i> Simpan Pengaturan Homepage
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</div>
