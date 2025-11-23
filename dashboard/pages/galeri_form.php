<?php
$edit_data = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM galeri WHERE id_galeri=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>

<a href="?page=galeri" class="back-link"><i class="fa fa-arrow-left"></i> Kembali ke Daftar Galeri</a>

<div class="form-wrapper">
    <div class="form-header">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit Gambar Galeri' : 'Upload Gambar Baru'; ?>
        </h2>
        <div class="form-info">
            <i class="fa fa-images"></i> Pilih gambar terbaik untuk galeri
        </div>
    </div>

    <form method="POST" action="?page=galeri" enctype="multipart/form-data" class="form-grid">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_galeri']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <div class="form-group full-width">
            <label for="judul"><i class="fa fa-heading"></i> Judul Gambar</label>
            <input type="text" id="judul" name="judul" 
                   value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" 
                   placeholder="Judul deskriptif untuk gambar" required>
        </div>

        <div class="form-group full-width image-upload-section">
            <div class="upload-controls">
                <label for="gambar"><i class="fa fa-cloud-upload-alt"></i> Pilih File Gambar</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" 
                       <?php echo !$edit_data ? 'required' : ''; ?> onchange="previewImage(this)">
                <small>Format: JPG, PNG, GIF | Maksimal 2MB | Rasio 4:3 disarankan</small>
            </div>
            
            <div class="preview-container">
                <div class="current-image">
                    <p><i class="fa fa-image"></i> Gambar Saat Ini</p>
                    <?php if ($edit_data && $edit_data['gambar']): ?>
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="img-preview" 
                             onerror="this.src='https://source.unsplash.com/random/160x120/?gallery'">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fa fa-images"></i>
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
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-cloud-upload-alt'; ?>"></i>
                <?php echo $edit_data ? 'Update Gambar' : 'Upload ke Galeri'; ?>
            </button>
            <a href="?page=galeri" class="btn btn-secondary">
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
</script>