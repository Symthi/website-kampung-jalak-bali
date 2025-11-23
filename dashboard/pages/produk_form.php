<?php
$edit_data = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM produk WHERE id_produk=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>

<a href="?page=produk" class="back-link"><i class="fa fa-arrow-left"></i> Kembali ke Daftar Produk</a>

<div class="form-wrapper">
    <div class="form-header">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit Produk' : 'Tambah Produk Baru'; ?>
        </h2>
        <div class="form-info">
            <i class="fa fa-info-circle"></i> Lengkapi detail produk dengan benar
        </div>
    </div>

    <form method="POST" action="?page=produk" enctype="multipart/form-data" class="form-grid">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_produk']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <div class="form-group full-width" style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 1rem;">
            <div>
                <label for="nama"><i class="fa fa-tag"></i> Nama Produk</label>
                <input type="text" id="nama" name="nama" 
                       value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" 
                       placeholder="Nama produk" required>
            </div>
            
            <div>
                <label for="harga"><i class="fa fa-money-bill"></i> Harga (Rp)</label>
                <input type="number" id="harga" name="harga" 
                       value="<?php echo htmlspecialchars($edit_data['harga'] ?? ''); ?>" 
                       step="1000" min="0" placeholder="0" required>
            </div>
            
            <div>
                <label for="stok"><i class="fa fa-boxes"></i> Stok</label>
                <input type="number" id="stok" name="stok" 
                       value="<?php echo htmlspecialchars($edit_data['stok'] ?? '0'); ?>" 
                       min="0" placeholder="0" required>
            </div>
        </div>

        <div class="form-group full-width">
            <label for="deskripsi"><i class="fa fa-align-left"></i> Deskripsi Produk</label>
            <textarea id="deskripsi" name="deskripsi" 
                      placeholder="Jelaskan spesifikasi dan keunggulan produk..."
                      required><?php echo htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
        </div>

        <div class="form-group full-width image-upload-section">
            <div class="upload-controls">
                <label for="gambar"><i class="fa fa-image"></i> Gambar Produk</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" 
                       <?php echo !$edit_data ? 'required' : ''; ?> onchange="previewImage(this)">
                <small>Format: JPG, PNG, GIF | Maksimal 2MB</small>
            </div>
            
            <div class="preview-container">
                <div class="current-image">
                    <p><i class="fa fa-image"></i> Gambar Saat Ini</p>
                    <?php if ($edit_data && $edit_data['gambar']): ?>
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="img-preview" 
                             onerror="this.src='https://source.unsplash.com/random/160x120/?product'">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fa fa-shopping-bag"></i>
                            <span>Belum ada gambar</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="new-preview">
                    <p><i class="fa fa-eye"></i> Preview Baru</p>
                    <div id="preview-box">
                        <div class="placeholder-image">
                            <i class="fa fa-upload"></i>
                            <span>Pilih file</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update Produk' : 'Tambah Produk'; ?>
            </button>
            <a href="?page=produk" class="btn btn-secondary">
                <i class="fa fa-times"></i> Batalkan
            </a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const box = document.getElementById('preview-box');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.innerHTML = `<img src="${e.target.result}" class="img-preview" alt="Preview Gambar Baru">`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        box.innerHTML = '<div class="placeholder-image"><i class="fa fa-upload"></i><span>Pilih file</span></div>';
    }
}

// Auto-resize textarea
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('deskripsi');
    if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = (textarea.scrollHeight) + 'px';
    }
});
</script>