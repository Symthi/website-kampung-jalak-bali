<?php
// dashboard/pages/crud_pengaturan.php

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_pengaturan'])) {
        foreach ($_POST['pengaturan'] as $kunci => $nilai) {
            $query = "UPDATE pengaturan SET nilai = ? WHERE kunci = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ss", $nilai, $kunci);
            mysqli_stmt_execute($stmt);
        }
        $success_msg = t('settings_updated');
    }
    
    if (isset($_POST['update_bahasa'])) {
        foreach ($_POST['bahasa'] as $id => $terjemahan) {
            $query = "UPDATE language_strings SET terjemahan = ? WHERE id_string = ?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "si", $terjemahan, $id);
            mysqli_stmt_execute($stmt);
        }
        // Refresh language cache in session
        $_SESSION['language_refreshed'] = time();
        $success_msg = t('translation_updated');
    }
    
    if (isset($_POST['tambah_bahasa'])) {
        $string_key = mysqli_real_escape_string($koneksi, $_POST['string_key']);
        $bahasa = mysqli_real_escape_string($koneksi, $_POST['bahasa']);
        $terjemahan = mysqli_real_escape_string($koneksi, $_POST['terjemahan']);
        $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
        
        $query = "INSERT INTO language_strings (string_key, bahasa, terjemahan, kategori) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $string_key, $bahasa, $terjemahan, $kategori);
        if (mysqli_stmt_execute($stmt)) {
            $success_msg = t('language_added');
        } else {
            $success_msg = "Error: " . mysqli_error($koneksi);
        }
    }
}

// Proses hapus bahasa
if (isset($_GET['hapus_bahasa'])) {
    $id = (int)$_GET['hapus_bahasa'];
    $query = "DELETE FROM language_strings WHERE id_string = ?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $success_msg = t('language_deleted');
}

// Pagination settings
$items_per_page = 5;

// Cek apakah sedang di language management
$is_language_management = isset($_GET['search']) || isset($_GET['language']) || isset($_GET['page_id']) || isset($_GET['page_en']);

// Search functionality - HANYA untuk language management
$search_term = '';
$language_filter = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($koneksi, $_GET['search']);
}

if (isset($_GET['language']) && !empty($_GET['language'])) {
    $language_filter = mysqli_real_escape_string($koneksi, $_GET['language']);
}

// Get settings data
$pengaturan = [];
$query = "SELECT * FROM pengaturan ORDER BY kategori, kunci";
$result = mysqli_query($koneksi, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $pengaturan[$row['kategori']][$row['kunci']] = $row;
}

// Get language data dengan pagination yang benar
$bahasa_data = [];
$languages = ['id', 'en'];

foreach ($languages as $lang) {
    // Skip language jika ada filter dan tidak sesuai
    if (!empty($language_filter) && $language_filter != $lang) {
        continue;
    }
    
    $current_page = isset($_GET["page_$lang"]) ? max(1, intval($_GET["page_$lang"])) : 1;
    $offset = ($current_page - 1) * $items_per_page;
    
    // Build query dengan kondisi search
    $where_conditions = ["bahasa = '$lang'"];
    
    if (!empty($search_term)) {
        $where_conditions[] = "(string_key LIKE '%$search_term%' OR terjemahan LIKE '%$search_term%' OR kategori LIKE '%$search_term%')";
    }
    
    $where_clause = implode(' AND ', $where_conditions);
    
    // Query untuk data
    $language_query = "SELECT * FROM language_strings WHERE $where_clause 
                      ORDER BY kategori, string_key 
                      LIMIT $offset, $items_per_page";
    
    // Query untuk total count
    $count_query = "SELECT COUNT(*) as total FROM language_strings WHERE $where_clause";
    
    $count_result = mysqli_query($koneksi, $count_query);
    $total_items = 0;
    if ($count_result) {
        $total_items = mysqli_fetch_assoc($count_result)['total'];
    }
    $total_pages = ceil($total_items / $items_per_page);
    
    // Get paginated data
    $result = mysqli_query($koneksi, $language_query);
    $language_strings = [];
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $language_strings[$row['kategori']][$row['string_key']] = $row;
        }
    }
    
    $bahasa_data[$lang] = [
        'strings' => $language_strings,
        'current_page' => $current_page,
        'total_pages' => $total_pages,
        'total_items' => $total_items,
        'offset' => $offset
    ];
}

$kategori_pengaturan = ['umum', 'kontak', 'sosial', 'hero', 'about', 'struktur', 'wisata', 'galeri', 'produk', 'informasi', 'footer', 'navbar', 'theme'];
?>

<div class="container-fluid">
    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Search Container - Hanya untuk kategori cards -->
    <div class="settings-search-container" id="mainSearchContainer" style="<?php echo $is_language_management ? 'display: none;' : ''; ?>">
        <div class="search-box-settings">
            <div class="search-input-group">
                <i class="fas fa-search"></i>
                <input type="text" id="settingsSearch" placeholder="Cari pengaturan... (umum, kontak, bahasa, dll)" autocomplete="off">
            </div>
            <div class="search-stats" id="searchStats">
                <?php echo count($kategori_pengaturan) + 1; ?> kategori tersedia
            </div>
        </div>
    </div>

    <!-- Cards Container -->
    <div class="settings-cards-container" id="settingsCards" style="<?php echo $is_language_management ? 'display: none;' : ''; ?>">
        <?php foreach ($kategori_pengaturan as $kategori): ?>
            <?php if (isset($pengaturan[$kategori])): ?>
                <div class="settings-card" data-category="<?php echo $kategori; ?>" data-search="<?php echo strtolower($kategori); ?>">
                    <div class="settings-card-icon">
                        <i class="fas fa-<?php echo getCategoryIcon($kategori); ?>"></i>
                    </div>
                    <div class="settings-card-content">
                        <h3 class="settings-card-title"><?php echo ucfirst($kategori); ?></h3>
                        <p class="settings-card-desc"><?php echo count($pengaturan[$kategori]); ?> pengaturan</p>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <!-- Card Bahasa -->
        <div class="settings-card" data-category="bahasa" data-search="bahasa language terjemahan multilingual">
            <div class="settings-card-icon">
                <i class="fas fa-language"></i>
            </div>
            <div class="settings-card-content">
                <h3 class="settings-card-title">Kelola Bahasa</h3>
                <p class="settings-card-desc">Terjemahan & multilingual</p>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="settings-content-area" id="settingsContent" style="<?php echo $is_language_management ? 'display: block;' : 'display: none;'; ?>">
        <?php if ($is_language_management): ?>
            <!-- Tampilkan language management langsung jika ada parameter -->
            <div class="settings-content-header">
                <h3><i class="fas fa-language"></i> Kelola Bahasa</h3>
                <button class="back-to-cards">
                    <i class="fas fa-arrow-left"></i> Kembali ke Daftar
                </button>
            </div>
            <div class="language-management">
                <!-- Language Search -->
                <div class="language-search-container">
                    <form method="GET" id="languageSearchForm">
                        <input type="hidden" name="page" value="pengaturan">
                        <div class="language-search-box">
                            <div class="language-search-input">
                                <i class="fas fa-search"></i>
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" 
                                       placeholder="Cari kata kunci, terjemahan, atau kategori..." autocomplete="off">
                            </div>
                            <div class="language-filter">
                                <select name="language">
                                    <option value="">Semua Bahasa</option>
                                    <option value="id" <?php echo $language_filter == 'id' ? 'selected' : ''; ?>>🇮🇩 Indonesia</option>
                                    <option value="en" <?php echo $language_filter == 'en' ? 'selected' : ''; ?>>🇬🇧 English</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-save" style="min-width: auto; padding: 0.8rem 1.2rem;">
                                <i class="fas fa-search"></i> Cari
                            </button>
                            <?php if (!empty($search_term) || !empty($language_filter)): ?>
                                <a href="?page=pengaturan&language=id" class="btn-cancel">
                                    <i class="fas fa-times"></i> Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <!-- Form Tambah Bahasa -->
                <div class="add-language-card">
                    <div class="header-row">
                        <h4><i class="fas fa-plus-circle"></i> Tambah String Bahasa Baru</h4>
                        <button type="submit" form="addLanguageForm" name="tambah_bahasa" class="btn-save header-button">
                            <i class="fas fa-plus"></i> Tambah String
                        </button>
                    </div>
                    <form method="POST" id="addLanguageForm">
                        <div class="add-language-form-grid">
                            <div class="compact-form-group">
                                <label><i class="fas fa-key"></i> Key String</label>
                                <input type="text" name="string_key" class="form-control" placeholder="welcome_text" required>
                            </div>
                            <div class="compact-form-group">
                                <label><i class="fas fa-globe"></i> Bahasa</label>
                                <select name="bahasa" class="form-control" required>
                                    <option value="">-- Pilih --</option>
                                    <option value="id">🇮🇩 Indonesia</option>
                                    <option value="en">🇬🇧 English</option>
                                </select>
                            </div>
                            <div class="compact-form-group">
                                <label><i class="fas fa-folder"></i> Kategori</label>
                                <input type="text" name="kategori" class="form-control" placeholder="general" value="general" required>
                            </div>
                            <div class="compact-form-group">
                                <label><i class="fas fa-language"></i> Terjemahan</label>
                                <input type="text" name="terjemahan" class="form-control" placeholder="Masukkan terjemahan" required>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Daftar Bahasa Indonesia -->
                <?php 
                $id_data = isset($bahasa_data['id']) ? $bahasa_data['id'] : ['strings' => [], 'current_page' => 1, 'total_pages' => 0, 'total_items' => 0];
                $en_data = isset($bahasa_data['en']) ? $bahasa_data['en'] : ['strings' => [], 'current_page' => 1, 'total_pages' => 0, 'total_items' => 0];
                $total_all_items = $id_data['total_items'] + $en_data['total_items'];
                ?>
                
                <?php if ($total_all_items > 0): ?>
                    <!-- Bahasa Indonesia -->
                    <?php if ($id_data['total_items'] > 0): ?>
                        <div class="language-section" style="margin-bottom: 2rem;">
                            <div class="language-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--tan);">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-flag"></i>
                                    <h4 style="margin: 0; color: var(--dark-green);">🇮🇩 Bahasa Indonesia</h4>
                                    <span style="background: var(--muted-green); color: white; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $id_data['total_items']; ?> string
                                    </span>
                                </div>
                                <div class="pagination-info">
                                    Halaman <?php echo $id_data['current_page']; ?> dari <?php echo $id_data['total_pages']; ?>
                                </div>
                            </div>
                            
                            <form method="POST">
                                <div class="table-responsive">
                                    <table class="language-table-clean">
                                        <thead>
                                            <tr>
                                                <th width="15%"><i class="fas fa-tag"></i> Kategori</th>
                                                <th width="20%"><i class="fas fa-key"></i> Key</th>
                                                <th width="55%"><i class="fas fa-language"></i> Terjemahan</th>
                                                <th width="10%"><i class="fas fa-cogs"></i> Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($id_data['strings'] as $kategori => $strings): ?>
                                                <?php foreach ($strings as $key => $string): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge"><?php echo ucfirst($kategori); ?></span>
                                                        </td>
                                                        <td><code><?php echo $key; ?></code></td>
                                                        <td>
                                                            <input type="text" 
                                                                   name="bahasa[<?php echo $string['id_string']; ?>]" 
                                                                   value="<?php echo htmlspecialchars($string['terjemahan']); ?>" 
                                                                   class="form-control">
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)" 
                                                               onclick="confirmDelete(<?php echo $string['id_string']; ?>)" 
                                                               class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination Indonesia -->
                                <?php if ($id_data['total_pages'] > 1): ?>
                                    <div class="language-pagination">
                                        <a href="<?php echo buildPaginationUrl('id', 1); ?>" class="pagination-btn <?php echo $id_data['current_page'] == 1 ? 'disabled' : ''; ?>">
                                            <i class="fas fa-angle-double-left"></i> First
                                        </a>
                                        
                                        <a href="<?php echo buildPaginationUrl('id', max(1, $id_data['current_page'] - 1)); ?>" class="pagination-btn <?php echo $id_data['current_page'] == 1 ? 'disabled' : ''; ?>">
                                            <i class="fas fa-angle-left"></i> Prev
                                        </a>
                                        
                                        <div class="pagination-numbers">
                                            <?php
                                            $start_page = max(1, $id_data['current_page'] - 2);
                                            $end_page = min($id_data['total_pages'], $id_data['current_page'] + 2);
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <a href="<?php echo buildPaginationUrl('id', $i); ?>" class="page-number <?php echo $i == $id_data['current_page'] ? 'active' : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            <?php endfor; ?>
                                        </div>
                                        
                                        <a href="<?php echo buildPaginationUrl('id', min($id_data['total_pages'], $id_data['current_page'] + 1)); ?>" class="pagination-btn <?php echo $id_data['current_page'] == $id_data['total_pages'] ? 'disabled' : ''; ?>">
                                            Next <i class="fas fa-angle-right"></i>
                                        </a>
                                        
                                        <a href="<?php echo buildPaginationUrl('id', $id_data['total_pages']); ?>" class="pagination-btn <?php echo $id_data['current_page'] == $id_data['total_pages'] ? 'disabled' : ''; ?>">
                                            Last <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="settings-actions-compact">
                                    <button type="submit" name="update_bahasa" class="btn-save">
                                        <i class="fas fa-save"></i> Simpan Bahasa Indonesia
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Bahasa Inggris -->
                    <?php if ($en_data['total_items'] > 0): ?>
                        <div class="language-section" style="margin-bottom: 2rem;">
                            <div class="language-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--tan);">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fas fa-flag"></i>
                                    <h4 style="margin: 0; color: var(--dark-green);">🇬🇧 English</h4>
                                    <span style="background: var(--muted-green); color: white; padding: 0.2rem 0.6rem; border-radius: 12px; font-size: 0.8rem; font-weight: 600;">
                                        <?php echo $en_data['total_items']; ?> strings
                                    </span>
                                </div>
                                <div class="pagination-info">
                                    Page <?php echo $en_data['current_page']; ?> of <?php echo $en_data['total_pages']; ?>
                                </div>
                            </div>
                            
                            <form method="POST">
                                <div class="table-responsive">
                                    <table class="language-table-clean">
                                        <thead>
                                            <tr>
                                                <th width="15%"><i class="fas fa-tag"></i> Category</th>
                                                <th width="20%"><i class="fas fa-key"></i> Key</th>
                                                <th width="55%"><i class="fas fa-language"></i> Translation</th>
                                                <th width="10%"><i class="fas fa-cogs"></i> Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($en_data['strings'] as $kategori => $strings): ?>
                                                <?php foreach ($strings as $key => $string): ?>
                                                    <tr>
                                                        <td>
                                                            <span class="badge"><?php echo ucfirst($kategori); ?></span>
                                                        </td>
                                                        <td><code><?php echo $key; ?></code></td>
                                                        <td>
                                                            <input type="text" 
                                                                   name="bahasa[<?php echo $string['id_string']; ?>]" 
                                                                   value="<?php echo htmlspecialchars($string['terjemahan']); ?>" 
                                                                   class="form-control">
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)" 
                                                               onclick="confirmDelete(<?php echo $string['id_string']; ?>)" 
                                                               class="btn btn-danger btn-sm">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <!-- Pagination English -->
                                <?php if ($en_data['total_pages'] > 1): ?>
                                    <div class="language-pagination">
                                        <a href="<?php echo buildPaginationUrl('en', 1); ?>" class="pagination-btn <?php echo $en_data['current_page'] == 1 ? 'disabled' : ''; ?>">
                                            <i class="fas fa-angle-double-left"></i> First
                                        </a>
                                        
                                        <a href="<?php echo buildPaginationUrl('en', max(1, $en_data['current_page'] - 1)); ?>" class="pagination-btn <?php echo $en_data['current_page'] == 1 ? 'disabled' : ''; ?>">
                                            <i class="fas fa-angle-left"></i> Prev
                                        </a>
                                        
                                        <div class="pagination-numbers">
                                            <?php
                                            $start_page = max(1, $en_data['current_page'] - 2);
                                            $end_page = min($en_data['total_pages'], $en_data['current_page'] + 2);
                                            
                                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                                <a href="<?php echo buildPaginationUrl('en', $i); ?>" class="page-number <?php echo $i == $en_data['current_page'] ? 'active' : ''; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            <?php endfor; ?>
                                        </div>
                                        
                                        <a href="<?php echo buildPaginationUrl('en', min($en_data['total_pages'], $en_data['current_page'] + 1)); ?>" class="pagination-btn <?php echo $en_data['current_page'] == $en_data['total_pages'] ? 'disabled' : ''; ?>">
                                            Next <i class="fas fa-angle-right"></i>
                                        </a>
                                        
                                        <a href="<?php echo buildPaginationUrl('en', $en_data['total_pages']); ?>" class="pagination-btn <?php echo $en_data['current_page'] == $en_data['total_pages'] ? 'disabled' : ''; ?>">
                                            Last <i class="fas fa-angle-double-right"></i>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="settings-actions-compact">
                                    <button type="submit" name="update_bahasa" class="btn-save">
                                        <i class="fas fa-save"></i> Save English
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h4>Tidak ada hasil ditemukan</h4>
                        <p><?php echo !empty($search_term) ? "Untuk pencarian '" . htmlspecialchars($search_term) . "'" : ''; ?></p>
                        <a href="?page=pengaturan" class="btn-save" style="margin-top: 1rem;">
                            <i class="fas fa-times"></i> Reset Pencarian
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Content will be loaded here via JavaScript -->
        <?php endif; ?>
    </div>
</div>

<!-- Template untuk Konten Pengaturan -->
<template id="settingsTemplate">
    <div class="settings-content-header">
        <h3><i class="fas fa-{icon}"></i> {title}</h3>
        <button class="back-to-cards">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar
        </button>
    </div>
    <div class="compact-form-grid">
        {content}
    </div>
    <div class="settings-actions-compact">
        <button type="submit" name="update_pengaturan" class="btn-save">
            <i class="fas fa-save"></i> Simpan Perubahan
        </button>
        <button type="button" class="btn-cancel back-to-cards">
            <i class="fas fa-times"></i> Batal
        </button>
    </div>
</template>

<script>
// Fungsi helper untuk icon kategori
function getCategoryIcon(category) {
    const icons = {
        'umum': 'cog',
        'kontak': 'phone',
        'sosial': 'share-alt',
        'hero': 'image',
        'about': 'info-circle',
        'struktur': 'sitemap',
        'wisata': 'map-marked-alt',
        'galeri': 'images',
        'produk': 'box',
        'informasi': 'newspaper',
        'footer': 'window-restore',
        'navbar': 'bars',
        'theme': 'palette'
    };
    return icons[category] || 'cog';
}

// Fungsi konfirmasi hapus
function confirmDelete(id) {
    if (confirm('Yakin ingin menghapus string bahasa ini?')) {
        window.location.href = `?page=pengaturan&hapus_bahasa=${id}<?php echo buildCurrentParams(); ?>`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const cardsContainer = document.querySelector('.settings-cards-container');
    const contentArea = document.getElementById('settingsContent');
    const settingsTemplate = document.getElementById('settingsTemplate');
    const searchInput = document.getElementById('settingsSearch');
    const searchStats = document.getElementById('searchStats');
    const mainSearchContainer = document.getElementById('mainSearchContainer');
    
    // Data pengaturan dari PHP
    const pengaturanData = <?php echo json_encode($pengaturan); ?>;
    const allCards = Array.from(document.querySelectorAll('.settings-card'));
    const totalCards = allCards.length;
    
    // Search functionality for cards - Hanya untuk tampilan cards
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let visibleCount = 0;
        
        allCards.forEach(card => {
            const searchData = card.dataset.search.toLowerCase();
            const isVisible = searchData.includes(searchTerm);
            
            card.style.display = isVisible ? 'flex' : 'none';
            if (isVisible) visibleCount++;
        });
        
        searchStats.textContent = `${visibleCount} dari ${totalCards} kategori ditemukan`;
    });
    
    // Event listener untuk card
    cardsContainer.addEventListener('click', function(e) {
        const card = e.target.closest('.settings-card');
        if (!card) return;
        
        const category = card.dataset.category;
        
        if (category === 'bahasa') {
            // Redirect ke language management dengan parameter
            window.location.href = '?page=pengaturan&language=id';
        } else {
            showSettingsCategory(category);
        }
    });
    
    // Back to cards
    contentArea.addEventListener('click', function(e) {
        if (e.target.classList.contains('back-to-cards')) {
            // Kembali ke halaman pengaturan tanpa parameter
            window.location.href = '?page=pengaturan';
        }
    });
    
    function showSettingsCategory(category) {
        const categoryData = pengaturanData[category];
        if (!categoryData) return;
        
        let contentHTML = '';
        
        Object.values(categoryData).forEach(item => {
            const isTextarea = item.nilai.length > 100;
            const fieldHTML = isTextarea 
                ? `<textarea name="pengaturan[${item.kunci}]" class="form-control" rows="3">${escapeHtml(item.nilai)}</textarea>`
                : `<input type="text" name="pengaturan[${item.kunci}]" value="${escapeHtml(item.nilai)}" class="form-control">`;
            
            contentHTML += `
                <div class="compact-form-group">
                    <label>
                        <i class="fas fa-tag"></i>
                        ${item.deskripsi}
                    </label>
                    ${fieldHTML}
                    <small>Kunci: ${item.kunci}</small>
                </div>
            `;
        });
        
        const templateHTML = settingsTemplate.innerHTML
            .replace('{icon}', getCategoryIcon(category))
            .replace('{title}', `Pengaturan ${capitalizeFirst(category)}`)
            .replace('{content}', contentHTML);
        
        contentArea.innerHTML = `<form method="POST" enctype="multipart/form-data">${templateHTML}</form>`;
        contentArea.style.display = 'block';
        cardsContainer.style.display = 'none';
        mainSearchContainer.style.display = 'none';
    }
    
    // Helper functions
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
    
    function capitalizeFirst(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Auto-resize textareas
    document.addEventListener('input', function(e) {
        if (e.target.tagName === 'TEXTAREA') {
            e.target.style.height = 'auto';
            e.target.style.height = (e.target.scrollHeight) + 'px';
        }
    });
});
</script>

<?php
// Helper function untuk icon kategori di PHP
function getCategoryIcon($category) {
    $icons = [
        'umum' => 'cog',
        'kontak' => 'phone',
        'sosial' => 'share-alt',
        'hero' => 'image',
        'about' => 'info-circle',
        'struktur' => 'sitemap',
        'wisata' => 'map-marked-alt',
        'galeri' => 'images',
        'produk' => 'box',
        'informasi' => 'newspaper',
        'footer' => 'window-restore',
        'navbar' => 'bars',
        'theme' => 'palette'
    ];
    return $icons[$category] ?? 'cog';
}

// Helper function untuk build pagination URL
function buildPaginationUrl($language, $page) {
    $params = ['page' => 'pengaturan'];
    
    // Tambahkan parameter search jika ada
    if (!empty($_GET['search'])) {
        $params['search'] = $_GET['search'];
    }
    
    // Tambahkan parameter language filter jika ada
    if (!empty($_GET['language'])) {
        $params['language'] = $_GET['language'];
    }
    
    // Set pagination untuk bahasa yang dipilih
    $params["page_$language"] = $page;
    
    // Hapus pagination untuk bahasa lain
    $other_lang = $language === 'id' ? 'en' : 'id';
    unset($params["page_$other_lang"]);
    
    return '?' . http_build_query($params);
}

// Helper function untuk build current parameters
function buildCurrentParams() {
    $params = [];
    
    if (!empty($_GET['search'])) {
        $params[] = 'search=' . urlencode($_GET['search']);
    }
    
    if (!empty($_GET['language'])) {
        $params[] = 'language=' . urlencode($_GET['language']);
    }
    
    if (!empty($_GET['page_id'])) {
        $params[] = 'page_id=' . urlencode($_GET['page_id']);
    }
    
    if (!empty($_GET['page_en'])) {
        $params[] = 'page_en=' . urlencode($_GET['page_en']);
    }
    
    return $params ? '&' . implode('&', $params) : '';
}
?>