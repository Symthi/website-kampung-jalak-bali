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

<div class="form-wrapper-enhanced">
    <div class="form-header-enhanced">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit Produk' : 'Tambah Produk'; ?>
        </h2>
        <a href="?page=produk" class="back-link-enhanced"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>

    <form method="POST" action="?page=produk" enctype="multipart/form-data" class="form-grid-enhanced">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_produk']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <div class="form-group-enhanced">
            <label for="nama"><i class="fa fa-tag"></i> Nama</label>
            <input type="text" id="nama" name="nama" 
                   value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" 
                   placeholder="Nama produk" required>
        </div>

        <div class="form-group-enhanced">
            <label for="harga"><i class="fa fa-money-bill"></i> Harga</label>
            <input type="number" id="harga" name="harga" 
                   value="<?php echo htmlspecialchars($edit_data['harga'] ?? ''); ?>" 
                   step="1000" min="0" placeholder="0" required>
        </div>

        <div class="form-group-enhanced">
            <label for="stok"><i class="fa fa-boxes"></i> Stok</label>
            <input type="number" id="stok" name="stok" 
                   value="<?php echo htmlspecialchars($edit_data['stok'] ?? '0'); ?>" 
                   min="0" placeholder="0" required>
        </div>

        <div class="form-group-enhanced full-width">
            <label for="deskripsi"><i class="fa fa-align-left"></i> Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Deskripsi produk..." required><?php echo htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
        </div>

        <div class="form-group-enhanced full-width image-upload-section-enhanced">
            <div class="upload-controls-enhanced">
                <label for="gambar"><i class="fa fa-image"></i> Gambar</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" 
                       <?php echo !$edit_data ? 'required' : ''; ?> onchange="previewImage(this)">
                <small>JPG, PNG, GIF (max 2MB)</small>
            </div>
            
            <div class="preview-container-enhanced">
                <div class="current-image-enhanced">
                    <p><i class="fa fa-image"></i> Saat Ini</p>
                    <?php if ($edit_data && $edit_data['gambar']): ?>
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="img-preview-enhanced" 
                             onerror="this.src='https://source.unsplash.com/random/150x110/?product,blur'">
                    <?php else: ?>
                        <div class="placeholder-image-enhanced">
                            <i class="fa fa-shopping-bag"></i>
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="new-preview-enhanced">
                    <p><i class="fa fa-eye"></i> Preview</p>
                    <div id="preview-box">
                        <div class="placeholder-image-enhanced">
                            <i class="fa fa-upload"></i>
                            <span>Pilih</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions-enhanced">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn-primary-enhanced">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update' : 'Tambah'; ?>
            </button>
            <a href="?page=produk" class="btn-secondary-enhanced">
                <i class="fa fa-times"></i> Batal
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
            box.innerHTML = `<img src="${e.target.result}" class="img-preview-enhanced" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        box.innerHTML = '<div class="placeholder-image-enhanced"><i class="fa fa-upload"></i><span>Pilih</span></div>';
    }
}
</script>