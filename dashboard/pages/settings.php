<?php
// dashboard/pages/settings.php
// HALAMAN PENGATURAN WEBSITE LENGKAP - FIXED & ERROR FREE

if (!isset($_SESSION)) {
    session_start();
}

if (!isAdmin()) {
    $_SESSION['error_message'] = "Akses ditolak! Hanya admin yang dapat mengakses.";
    header("Location: ?page=dashboard");
    exit();
}

$current_tab = isset($_GET['tab']) ? basename($_GET['tab']) : 'general';
$allowed_tabs = ['general', 'navbar', 'sidebar', 'display', 'homepage', 'kontak'];

if (!in_array($current_tab, $allowed_tabs)) {
    $current_tab = 'general';
}

// Cek tabel settings
$tables_exist = mysqli_query($koneksi, "SHOW TABLES LIKE 'website_settings'");
if (mysqli_num_rows($tables_exist) == 0) {
    $setup_file = __DIR__ . '/../../config/settings_schema.sql';
    if (file_exists($setup_file)) {
        $sql = file_get_contents($setup_file);
        if (strlen($sql) > 0) {
            mysqli_multi_query($koneksi, $sql);
            while (mysqli_more_results($koneksi)) {
                mysqli_next_result($koneksi);
            }
        }
    }
}

// Ambil data
$general_settings = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM website_settings WHERE id = 1")) ?: [
    'id' => 1, 'nama_website' => 'Kampung Jalak Bali', 'deskripsi_website' => '',
    'email_kontak' => '', 'telepon' => '', 'alamat' => '', 'jam_kerja' => '',
    'link_whatsapp' => '', 'link_facebook' => '', 'link_instagram' => '',
    'link_youtube' => '', 'link_tiktok' => ''
];

$display_settings = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pengaturan_tampilan WHERE id = 1")) ?: [
    'id' => 1, 'warna_utama' => '#354024', 'warna_sekunder' => '#cfbb99',
    'tampilkan_breadcrumb' => 1, 'tampilkan_search_bar' => 1,
    'tampilkan_footer_newsletter' => 1, 'items_per_page' => 6
];

$menu_navbar = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM menu_navbar ORDER BY urutan ASC"), MYSQLI_ASSOC) ?: [];
$sidebar_menu = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM sidebar_menu ORDER BY urutan ASC"), MYSQLI_ASSOC) ?: [];
$homepage_sections = mysqli_fetch_all(mysqli_query($koneksi, "SELECT * FROM homepage_sections ORDER BY urutan ASC"), MYSQLI_ASSOC) ?: [];
?>

<style>
.settings-header { margin-bottom: 1.5rem; }
.settings-header h1 { font-size: 1.8rem; color: var(--dark-green); font-weight: 700; margin-bottom: 0.3rem; }
.settings-header p { margin: 0; }

.tabs-nav {
    display: flex;
    gap: 0;
    border-bottom: 2px solid var(--tan);
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    background: var(--white);
    padding: 0 1rem;
    border-radius: 8px 8px 0 0;
}

.tab-btn {
    padding: 0.9rem 1.2rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    color: var(--muted-text);
    border-bottom: 3px solid transparent;
    transition: var(--transition);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    white-space: nowrap;
}

.tab-btn:hover { color: var(--dark-green); border-bottom-color: var(--muted-green); }
.tab-btn.active { color: var(--dark-green); border-bottom-color: var(--dark-green); font-weight: 700; }

.tab-content { background: var(--white); padding: 1.5rem; border-radius: 0 0 8px 8px; box-shadow: var(--shadow-sm); border: 1px solid var(--border); border-top: none; }

.settings-section { margin-bottom: 1.5rem; }
.settings-section h3 { font-size: 1rem; color: var(--dark-green); font-weight: 700; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--tan); display: flex; align-items: center; gap: 0.5rem; }
.settings-section h3 i { font-size: 1.1rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem; }
.form-row.full { grid-template-columns: 1fr; }

.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: 600; margin-bottom: 0.4rem; color: var(--text); font-size: 0.9rem; }
.form-group input, .form-group textarea, .form-group select { padding: 0.7rem; border: 1px solid var(--border); border-radius: 4px; font-size: 0.9rem; font-family: var(--font-body); transition: var(--transition); }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--muted-green); box-shadow: 0 0 0 3px rgba(136, 144, 99, 0.1); }
.form-group textarea { min-height: 90px; resize: vertical; }
.form-group small { font-size: 0.8rem; color: var(--muted-text); margin-top: 0.2rem; }

.color-input-group { display: flex; align-items: center; gap: 0.8rem; }
.color-input-group input[type="color"] { width: 55px; height: 40px; border: 2px solid var(--border); border-radius: 4px; cursor: pointer; }
.color-code { font-family: monospace; font-size: 0.85rem; color: var(--text); font-weight: 600; }

.checkbox-group { display: flex; align-items: center; gap: 0.6rem; padding: 0.6rem; background: #f9f8f6; border-radius: 4px; margin-bottom: 0.6rem; }
.checkbox-group input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; accent-color: var(--dark-green); }
.checkbox-group label { margin: 0; cursor: pointer; font-weight: normal; flex: 1; font-size: 0.9rem; }

.menu-table { width: 100%; border-collapse: collapse; margin-top: 1rem; font-size: 0.9rem; }
.menu-table th { background: linear-gradient(135deg, var(--muted-green) 0%, #7a7e56 100%); color: white; padding: 0.7rem; text-align: left; font-weight: 600; }
.menu-table td { padding: 0.7rem; border-bottom: 1px solid var(--border); }
.menu-table tbody tr:hover { background: #f9f8f6; }

.menu-form-row { background: #fafaf9; padding: 1rem; border-radius: 4px; margin-bottom: 0.8rem; border-left: 4px solid var(--dark-green); }
.menu-form-row input, .menu-form-row select { padding: 0.6rem; border: 1px solid var(--border); border-radius: 4px; font-size: 0.9rem; width: 100%; margin-bottom: 0.6rem; }

.btn-save { background: linear-gradient(135deg, var(--dark-green) 0%, #2a3219 100%); color: white; padding: 0.7rem 1.5rem; border: none; border-radius: 4px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: var(--transition); display: flex; align-items: center; gap: 0.5rem; }
.btn-save:hover { background: linear-gradient(135deg, #2a3219 0%, #1f2512 100%); transform: translateY(-2px); box-shadow: 0 4px 12px rgba(53, 64, 36, 0.3); }

.btn-delete { background: #dc3545; color: white; padding: 0.4rem 0.7rem; border: none; border-radius: 4px; font-size: 0.8rem; cursor: pointer; transition: var(--transition); }
.btn-delete:hover { background: #c82333; transform: translateY(-1px); }

@media (max-width: 768px) {
    .form-row { grid-template-columns: 1fr; }
    .tab-content { padding: 1rem; }
    .tabs-nav { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .tab-btn { padding: 0.7rem 1rem; font-size: 0.8rem; }
    .menu-form-row { padding: 0.75rem; }
}
</style>

<div class="settings-header">
    <h1><i class="fas fa-cog"></i> Pengaturan Website</h1>
    <p style="color: var(--muted-text);">Kelola semua aspek website dari sini</p>
</div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success" style="margin-bottom: 1.5rem;"><i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger" style="margin-bottom: 1.5rem;"><i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="tabs-nav">
    <button class="tab-btn <?php echo $current_tab == 'general' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=general'"><i class="fas fa-info-circle"></i> Umum</button>
    <button class="tab-btn <?php echo $current_tab == 'navbar' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=navbar'"><i class="fas fa-bars"></i> Menu Navbar</button>
    <button class="tab-btn <?php echo $current_tab == 'sidebar' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=sidebar'"><i class="fas fa-list"></i> Menu Sidebar</button>
    <button class="tab-btn <?php echo $current_tab == 'display' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=display'"><i class="fas fa-palette"></i> Tampilan</button>
    <button class="tab-btn <?php echo $current_tab == 'homepage' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=homepage'"><i class="fas fa-home"></i> Homepage</button>
    <button class="tab-btn <?php echo $current_tab == 'kontak' ? 'active' : ''; ?>" onclick="location.href='?page=settings&tab=kontak'"><i class="fas fa-envelope"></i> Kontak</button>
</div>

<div class="tab-content">

    <?php if ($current_tab == 'general'): ?>
    <form method="POST" action="?page=settings">
        <div class="settings-section">
            <h3><i class="fas fa-globe"></i> Informasi Website</h3>
            <div class="form-row full"><div class="form-group"><label>Nama Website *</label><input type="text" name="nama_website" value="<?php echo htmlspecialchars($general_settings['nama_website']); ?>" required></div></div>
            <div class="form-row full"><div class="form-group"><label>Deskripsi Website</label><textarea name="deskripsi_website"><?php echo htmlspecialchars($general_settings['deskripsi_website']); ?></textarea></div></div>
            <div class="form-row"><div class="form-group"><label>Email Kontak</label><input type="email" name="email_kontak" value="<?php echo htmlspecialchars($general_settings['email_kontak']); ?>"></div><div class="form-group"><label>Telepon</label><input type="tel" name="telepon" value="<?php echo htmlspecialchars($general_settings['telepon']); ?>"></div></div>
            <div class="form-row full"><div class="form-group"><label>Alamat</label><textarea name="alamat"><?php echo htmlspecialchars($general_settings['alamat']); ?></textarea></div></div>
            <div class="form-row full"><div class="form-group"><label>Jam Kerja</label><input type="text" name="jam_kerja" placeholder="Senin-Jumat 08:00-17:00" value="<?php echo htmlspecialchars($general_settings['jam_kerja']); ?>"></div></div>
        </div>

        <div class="settings-section">
            <h3><i class="fas fa-share-alt"></i> Media Sosial</h3>
            <div class="form-row"><div class="form-group"><label>WhatsApp</label><input type="url" name="link_whatsapp" placeholder="https://wa.me/628xxxx" value="<?php echo htmlspecialchars($general_settings['link_whatsapp']); ?>"></div><div class="form-group"><label>Facebook</label><input type="url" name="link_facebook" placeholder="https://facebook.com/..." value="<?php echo htmlspecialchars($general_settings['link_facebook']); ?>"></div></div>
            <div class="form-row"><div class="form-group"><label>Instagram</label><input type="url" name="link_instagram" placeholder="https://instagram.com/..." value="<?php echo htmlspecialchars($general_settings['link_instagram']); ?>"></div><div class="form-group"><label>YouTube</label><input type="url" name="link_youtube" placeholder="https://youtube.com/..." value="<?php echo htmlspecialchars($general_settings['link_youtube']); ?>"></div></div>
            <div class="form-row"><div class="form-group"><label>TikTok</label><input type="url" name="link_tiktok" placeholder="https://tiktok.com/@..." value="<?php echo htmlspecialchars($general_settings['link_tiktok']); ?>"></div></div>
        </div>

        <button type="submit" name="save_general" class="btn-save"><i class="fas fa-save"></i> Simpan Pengaturan Umum</button>
    </form>

    <?php elseif ($current_tab == 'navbar'): ?>
    <div class="settings-section">
        <h3><i class="fas fa-bars"></i> Menu Navigasi</h3>
        <form method="POST" action="?page=settings">
            <div class="menu-form-row">
                <h4 style="margin-top: 0; color: var(--dark-green); margin-bottom: 1rem;">Tambah Menu Baru</h4>
                <div class="form-row"><div class="form-group"><label>Label Menu *</label><input type="text" name="navbar_label" placeholder="Contoh: Tentang Kami" required></div><div class="form-group"><label>URL *</label><input type="text" name="navbar_url" placeholder="about.php atau index.php#about" required></div></div>
                <div class="form-row"><div class="form-group"><label>Icon</label><input type="text" name="navbar_icon" placeholder="fa-home, fa-info-circle"></div><div class="form-group"><label>Tipe Menu</label><select name="navbar_tipe"><option value="halaman_internal">Halaman Internal</option><option value="link_eksternal">Link Eksternal</option><option value="kategori">Kategori</option></select></div></div>
                <button type="submit" name="add_navbar" class="btn-save"><i class="fas fa-plus"></i> Tambah Menu</button>
            </div>
        </form>

        <h4 style="color: var(--dark-green); margin: 1.5rem 0 1rem 0;">Daftar Menu Navbar</h4>
        <?php if (!empty($menu_navbar)): ?>
        <table class="menu-table">
            <thead><tr><th width="30">#</th><th>Label</th><th>URL</th><th width="70">Status</th><th width="80">Aksi</th></tr></thead>
            <tbody>
                <?php foreach ($menu_navbar as $idx => $menu): ?>
                <tr><td><?php echo $idx + 1; ?></td><td><?php echo htmlspecialchars($menu['label']); ?></td><td><code><?php echo htmlspecialchars($menu['url']); ?></code></td><td><span class="badge <?php echo $menu['aktif'] ? 'badge-success' : 'badge-danger'; ?>"><?php echo $menu['aktif'] ? 'Aktif' : 'Nonaktif'; ?></span></td><td><form method="POST" style="display:inline;"><input type="hidden" name="menu_id" value="<?php echo $menu['id']; ?>"><select name="menu_aktif" onchange="this.form.submit()" style="padding:0.4rem; font-size:0.8rem; border:1px solid var(--border);"><option value="1" <?php echo $menu['aktif'] ? 'selected' : ''; ?>>Aktif</option><option value="0" <?php echo !$menu['aktif'] ? 'selected' : ''; ?>>Nonaktif</option></select></form> <a href="?page=settings&tab=navbar&delete_menu=<?php echo $menu['id']; ?>" class="btn-delete" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center; color:var(--muted-text);">Belum ada menu navbar.</p>
        <?php endif; ?>
    </div>

    <?php elseif ($current_tab == 'sidebar'): ?>
    <div class="settings-section">
        <h3><i class="fas fa-list"></i> Menu Sidebar Admin</h3>
        <form method="POST" action="?page=settings">
            <?php foreach ($sidebar_menu as $menu): ?>
            <div class="menu-form-row">
                <div class="form-group"><label>Label Menu</label><input type="text" name="sidebar_label[<?php echo $menu['id']; ?>]" value="<?php echo htmlspecialchars($menu['label']); ?>"></div>
                <div class="form-group"><label>Status</label><select name="sidebar_aktif[<?php echo $menu['id']; ?>]"><option value="1" <?php echo $menu['aktif'] ? 'selected' : ''; ?>>Aktif</option><option value="0" <?php echo !$menu['aktif'] ? 'selected' : ''; ?>>Nonaktif</option></select></div>
                <input type="hidden" name="sidebar_id[<?php echo $menu['id']; ?>]" value="<?php echo $menu['id']; ?>">
            </div>
            <?php endforeach; ?>
            <button type="submit" name="save_sidebar" class="btn-save"><i class="fas fa-save"></i> Simpan Menu Sidebar</button>
        </form>
    </div>

    <?php elseif ($current_tab == 'display'): ?>
    <form method="POST" action="?page=settings">
        <div class="settings-section">
            <h3><i class="fas fa-palette"></i> Warna & Tampilan</h3>
            <div class="form-row"><div class="form-group"><label>Warna Utama</label><div class="color-input-group"><input type="color" name="warna_utama" value="<?php echo htmlspecialchars($display_settings['warna_utama']); ?>"><span class="color-code" id="warna_utama_code"><?php echo htmlspecialchars($display_settings['warna_utama']); ?></span></div></div><div class="form-group"><label>Warna Sekunder</label><div class="color-input-group"><input type="color" name="warna_sekunder" value="<?php echo htmlspecialchars($display_settings['warna_sekunder']); ?>"><span class="color-code" id="warna_sekunder_code"><?php echo htmlspecialchars($display_settings['warna_sekunder']); ?></span></div></div></div>
        </div>

        <div class="settings-section">
            <h3><i class="fas fa-cogs"></i> Komponen</h3>
            <div class="checkbox-group"><input type="checkbox" id="breadcrumb" name="tampilkan_breadcrumb" value="1" <?php echo $display_settings['tampilkan_breadcrumb'] ? 'checked' : ''; ?>><label for="breadcrumb">Tampilkan Breadcrumb</label></div>
            <div class="checkbox-group"><input type="checkbox" id="search_bar" name="tampilkan_search_bar" value="1" <?php echo $display_settings['tampilkan_search_bar'] ? 'checked' : ''; ?>><label for="search_bar">Tampilkan Search Bar</label></div>
            <div class="checkbox-group"><input type="checkbox" id="newsletter" name="tampilkan_footer_newsletter" value="1" <?php echo $display_settings['tampilkan_footer_newsletter'] ? 'checked' : ''; ?>><label for="newsletter">Tampilkan Newsletter Footer</label></div>
        </div>

        <div class="settings-section">
            <h3><i class="fas fa-th-list"></i> Pagination</h3>
            <div class="form-row full"><div class="form-group"><label>Item per Halaman</label><input type="number" name="items_per_page" min="1" max="50" value="<?php echo $display_settings['items_per_page']; ?>"></div></div>
        </div>

        <button type="submit" name="save_display" class="btn-save"><i class="fas fa-save"></i> Simpan Pengaturan Tampilan</button>
    </form>

    <?php elseif ($current_tab == 'homepage'): ?>
    <div class="settings-section">
        <h3><i class="fas fa-home"></i> Section Homepage</h3>
        <form method="POST" action="?page=settings">
            <?php foreach ($homepage_sections as $section): ?>
            <div class="checkbox-group">
                <input type="checkbox" id="section_<?php echo $section['id']; ?>" name="section_aktif[<?php echo $section['id']; ?>]" value="1" <?php echo $section['aktif'] ? 'checked' : ''; ?>>
                <label for="section_<?php echo $section['id']; ?>"><strong><?php echo htmlspecialchars($section['nama_section']); ?></strong></label>
            </div>
            <?php endforeach; ?>
            <button type="submit" name="save_homepage" class="btn-save"><i class="fas fa-save"></i> Simpan Homepage</button>
        </form>
    </div>

    <?php elseif ($current_tab == 'kontak'): ?>
    <div class="settings-section">
        <h3><i class="fas fa-envelope"></i> Informasi Kontak</h3>
        <form method="POST" action="?page=settings">
            <div class="form-row full"><div class="form-group"><label>Email Kontak *</label><input type="email" name="email_kontak" value="<?php echo htmlspecialchars($general_settings['email_kontak']); ?>" required></div></div>
            <div class="form-row"><div class="form-group"><label>Telepon *</label><input type="tel" name="telepon" value="<?php echo htmlspecialchars($general_settings['telepon']); ?>" required></div><div class="form-group"><label>WhatsApp</label><input type="url" name="link_whatsapp" placeholder="https://wa.me/628xxxx" value="<?php echo htmlspecialchars($general_settings['link_whatsapp']); ?>"></div></div>
            <div class="form-row full"><div class="form-group"><label>Alamat *</label><textarea name="alamat" required><?php echo htmlspecialchars($general_settings['alamat']); ?></textarea></div></div>
            <div class="form-row full"><div class="form-group"><label>Jam Kerja *</label><input type="text" name="jam_kerja" placeholder="Senin-Jumat 08:00-17:00" value="<?php echo htmlspecialchars($general_settings['jam_kerja']); ?>" required></div></div>
            <button type="submit" name="save_kontak" class="btn-save"><i class="fas fa-save"></i> Simpan Kontak</button>
        </form>
    </div>

    <?php endif; ?>
</div>

<script>
document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.name === 'warna_utama') {
            document.getElementById('warna_utama_code').textContent = this.value.toUpperCase();
        } else if (this.name === 'warna_sekunder') {
            document.getElementById('warna_sekunder_code').textContent = this.value.toUpperCase();
        }
    });
});
</script>