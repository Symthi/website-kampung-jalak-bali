<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM galeri");
$total_galeri_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM galeri ORDER BY tanggal_upload DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$galeri_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM galeri WHERE id_galeri=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<style>
  .section-title {
    font-size: 2rem;
    color: var(--dark-green);
    margin-bottom: 2rem;
    font-family: var(--font-heading);
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .section-title i {
    font-size: 1.8rem;
  }

  .gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
  }

  .gallery-card {
    background: var(--white);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(76, 61, 25, 0.1);
    transition: all 0.3s ease;
    border: 1px solid rgba(207, 187, 153, 0.2);
  }

  .gallery-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(76, 61, 25, 0.15);
  }

  .gallery-card-img {
    width: 100%;
    height: 200px;
    object-fit: cover;
  }

  .gallery-card-body {
    padding: 1.5rem;
  }

  .gallery-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark-green);
    margin-bottom: 0.5rem;
  }

  .gallery-card-text {
    font-size: 0.9rem;
    color: var(--muted-text);
    margin-bottom: 0.5rem;
  }

  .gallery-card-date {
    font-size: 0.85rem;
    color: var(--muted-text);
    margin-bottom: 1rem;
  }

  .gallery-card-actions {
    display: flex;
    gap: 0.5rem;
  }
</style>

<h2 class="section-title">
    <i class="fa fa-images"></i> <?php echo t('manage_gallery') ?: 'Kelola Galeri'; ?>
</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
    </div>
<?php endif; ?>

<!-- Form -->
<div class="crud-panel shadow mb-4">
    <div class="panel-title">
        <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
        <?php echo $edit_data ? 'Edit' : 'Upload'; ?> Gambar
    </div>
    <div class="card-body">
        <form method="POST" action="?page=galeri" enctype="multipart/form-data" class="crud-form">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id_galeri']; ?>">
                <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="judul">Judul:</label>
                <input class="form-control" type="text" id="judul" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="keterangan">Keterangan:</label>
                <textarea class="form-control" id="keterangan" name="keterangan" rows="3"><?php echo $edit_data['keterangan'] ?? ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="gambar">Gambar:</label>
                <input class="form-control-file" type="file" id="gambar" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>

                <?php if ($edit_data && $edit_data['gambar']): ?>
                    <div class="mt-2">
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="info-img" style="max-width: 200px;" onerror="this.src='https://source.unsplash.com/random/200x150/?bali'">
                        <p class="small text-muted mt-2">Gambar saat ini</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group" style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                    <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-upload'; ?>"></i>
                    <?php echo $edit_data ? 'Update' : 'Upload'; ?> Gambar
                </button>
                
                <?php if ($edit_data): ?>
                    <a href="?page=galeri" class="btn btn-warning">
                        <i class="fa fa-times"></i> Batal
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Daftar -->
<div class="crud-list shadow mb-4">
    <div class="list-title">
        <i class="fa fa-list"></i> Daftar Galeri
        <span class="badge badge-primary ml-2"><?php echo $total_galeri_all; ?> Gambar</span>
    </div>
    <div class="card-body">
        <?php if (empty($galeri_data)): ?>
            <p class="text-muted">Tidak ada gambar dalam galeri.</p>
        <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($galeri_data as $galeri): ?>
                <div class="gallery-card">
                    <img src="<?php echo $base . '/' . $galeri['gambar']; ?>" class="gallery-card-img" alt="<?php echo htmlspecialchars($galeri['judul']); ?>" onerror="this.src='https://source.unsplash.com/random/300x200/?bali'">
                    <div class="gallery-card-body">
                        <h5 class="gallery-card-title"><?php echo htmlspecialchars($galeri['judul']); ?></h5>
                        <p class="gallery-card-text"><?php echo substr(htmlspecialchars($galeri['keterangan']), 0, 100); ?>...</p>
                        <p class="gallery-card-date">Upload: <?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></p>
                        <div class="gallery-card-actions">
                            <a href="?page=galeri&edit=<?php echo $galeri['id_galeri']; ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <a href="?page=galeri&hapus=<?php echo $galeri['id_galeri']; ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('Yakin?')">
                                <i class="fa fa-trash"></i> Hapus
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php 
            $total_pages = (int)ceil($total_galeri_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination" style="margin-top: 2rem;">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=galeri&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>