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

<div class="form-wrapper-enhanced">
    <div class="form-header-enhanced">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit Galeri' : 'Tambah Galeri'; ?>
        </h2>
        <a href="?page=galeri" class="back-link-enhanced"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>

    <form method="POST" action="?page=galeri" enctype="multipart/form-data" class="form-grid-enhanced">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_galeri']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <div class="form-group-enhanced full-width">
            <label for="judul"><i class="fa fa-heading"></i> Judul</label>
            <input type="text" id="judul" name="judul" 
                   value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" 
                   placeholder="Judul gambar" required>
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
                             onerror="this.src='https://source.unsplash.com/random/150x110/?gallery,blur'">
                    <?php else: ?>
                        <div class="placeholder-image-enhanced">
                            <i class="fa fa-images"></i>
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
            <a href="?page=galeri" class="btn-secondary-enhanced">
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