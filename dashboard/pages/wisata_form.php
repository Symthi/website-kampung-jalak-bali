<?php
$edit_data = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM wisata WHERE id_wisata=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>

<a href="?page=wisata" class="back-link"><i class="fa fa-arrow-left"></i> Kembali</a>

<div class="form-wrapper">
    <div class="form-header">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit Wisata' : 'Tambah Wisata'; ?>
        </h2>
    </div>

    <form method="POST" action="?page=wisata" enctype="multipart/form-data" class="form-grid">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_wisata']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <!-- 3 kolom: Judul, Waktu, Jam -->
        <div class="form-group">
            <label for="judul"><i class="fa fa-heading"></i> Judul</label>
            <input type="text" id="judul" name="judul" 
                   value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" 
                   placeholder="Nama wisata" required>
        </div>

        <div class="form-group">
            <label for="waktu"><i class="fa fa-sun"></i> Waktu</label>
            <select id="waktu" name="waktu" required>
                <option value="">Pilih</option>
                <option value="pagi" <?php echo ($edit_data && $edit_data['waktu'] === 'pagi') ? 'selected' : ''; ?>>Pagi</option>
                <option value="siang" <?php echo ($edit_data && $edit_data['waktu'] === 'siang') ? 'selected' : ''; ?>>Siang</option>
                <option value="malam" <?php echo ($edit_data && $edit_data['waktu'] === 'malam') ? 'selected' : ''; ?>>Malam</option>
            </select>
        </div>

        <div class="form-group">
            <label for="jam"><i class="fa fa-clock"></i> Jam</label>
            <input type="time" id="jam" name="jam" 
                   value="<?php echo htmlspecialchars($edit_data['jam'] ?? '08:00'); ?>" required>
        </div>

        <!-- Deskripsi -->
        <div class="form-group full-width">
            <label for="deskripsi"><i class="fa fa-align-left"></i> Deskripsi</label>
            <textarea id="deskripsi" name="deskripsi" 
                      placeholder="Deskripsi singkat wisata..."
                      required><?php echo htmlspecialchars($edit_data['deskripsi'] ?? ''); ?></textarea>
        </div>

        <!-- Gambar -->
        <div class="form-group full-width image-upload-section">
            <div class="upload-controls">
                <label for="gambar"><i class="fa fa-image"></i> Gambar</label>
                <input type="file" id="gambar" name="gambar" accept="image/*" 
                       <?php echo !$edit_data ? 'required' : ''; ?> onchange="previewImage(this)">
                <small>JPG, PNG, GIF (max 2MB)</small>
            </div>
            
            <div class="preview-container">
                <div class="current-image">
                    <p><i class="fa fa-image"></i> Saat Ini</p>
                    <?php if ($edit_data && $edit_data['gambar']): ?>
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="img-preview" 
                             onerror="this.src='https://source.unsplash.com/random/100x75/?bali,blur'">
                    <?php else: ?>
                        <div class="placeholder-image">
                            <i class="fa fa-landmark"></i>
                            <span>No Image</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="new-preview">
                    <p><i class="fa fa-eye"></i> Preview</p>
                    <div id="preview-box">
                        <div class="placeholder-image">
                            <i class="fa fa-upload"></i>
                            <span>Pilih</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol -->
        <div class="form-actions">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update' : 'Tambah'; ?>
            </button>
            <a href="?page=wisata" class="btn btn-secondary">
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
            box.innerHTML = `<img src="${e.target.result}" class="img-preview" alt="Preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        box.innerHTML = '<div class="placeholder-image"><i class="fa fa-upload"></i><span>Pilih</span></div>';
    }
}
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('deskripsi');
    if (textarea) {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 70) + 'px';
    }
});
</script>