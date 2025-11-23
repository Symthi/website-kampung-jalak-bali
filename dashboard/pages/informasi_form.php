<?php
$edit_data = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM informasi WHERE id_informasi=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>

<style>
.section-title{font-size:1.8rem;color:var(--dark-green);font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem}
.form-wrapper{max-width:600px;margin:0 auto}
.form-group{margin-bottom:1rem}
.form-group label{display:block;margin-bottom:0.4rem;font-weight:600;font-size:0.95rem}
.form-group input, .form-group textarea, .form-group select{width:100%;padding:0.7rem;font-size:0.9rem;border:1px solid #ddd;border-radius:4px;font-family:inherit}
.form-group textarea{resize:vertical;min-height:100px}
.form-group small{display:block;margin-top:0.3rem;color:#666;font-size:0.85rem}
.img-preview{max-width:200px;max-height:150px;margin:0.8rem 0;border-radius:4px;object-fit:cover}
.form-preview-box{background:#f8f9fa;padding:1rem;border-radius:4px;margin:0.8rem 0}
.btn-group{display:flex;gap:0.8rem;margin-top:1.5rem}
.btn-group .btn{flex:1;padding:0.8rem;font-size:0.95rem}
.back-link{display:inline-block;margin-bottom:1rem;color:var(--dark-green);text-decoration:none;font-size:0.9rem}
.back-link:hover{text-decoration:underline}
</style>

<a href="?page=informasi" class="back-link"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>

<h2 class="section-title">
    <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
    <?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Informasi
</h2>

<div class="form-wrapper">
    <form method="POST" action="?page=informasi" enctype="multipart/form-data">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_informasi']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="judul">Judul:</label>
            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="kategori">Kategori:</label>
            <select id="kategori" name="kategori" required>
                <option value="">-- Pilih Kategori --</option>
                <option value="berita" <?php echo ($edit_data && $edit_data['kategori'] === 'berita') ? 'selected' : ''; ?>>Berita</option>
                <option value="artikel" <?php echo ($edit_data && $edit_data['kategori'] === 'artikel') ? 'selected' : ''; ?>>Artikel</option>
                <option value="pengumuman" <?php echo ($edit_data && $edit_data['kategori'] === 'pengumuman') ? 'selected' : ''; ?>>Pengumuman</option>
                <option value="event" <?php echo ($edit_data && $edit_data['kategori'] === 'event') ? 'selected' : ''; ?>>Event</option>
            </select>
        </div>

        <div class="form-group">
            <label for="isi">Isi Informasi:</label>
            <textarea id="isi" name="isi" required><?php echo htmlspecialchars($edit_data['isi'] ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="gambar">Gambar:</label>
            <input type="file" id="gambar" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?> onchange="previewImage(this)">
            <small>JPG, PNG, GIF (Maksimal 2MB)</small>
            <div id="preview-box"></div>
            <?php if ($edit_data && $edit_data['gambar']): ?>
                <div class="form-preview-box">
                    <p style="margin:0 0 0.5rem 0;font-weight:600;font-size:0.9rem">Gambar Saat Ini:</p>
                    <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="img-preview" onerror="this.src='https://source.unsplash.com/random/200x150/?information'">
                </div>
            <?php endif; ?>
        </div>

        <div class="btn-group">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update' : 'Tambah'; ?> Informasi
            </button>
            <a href="?page=informasi" class="btn btn-secondary">
                <i class="fa fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<script>
function previewImage(input) {
    const box = document.getElementById('preview-box');
    box.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            box.innerHTML = '<div class="form-preview-box"><p style="margin:0 0 0.5rem 0;font-weight:600;font-size:0.9rem">Preview:</p><img src="' + e.target.result + '" class="img-preview"></div>';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
