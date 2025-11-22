<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Ambil data wisata dengan pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM wisata");
$total_wisata_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM wisata ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$wisata_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM wisata WHERE id_wisata=?";
    $stmt = mysqli_prepare($koneksi, $query);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_data = mysqli_fetch_assoc($result);
}
?>

<style>
  /* CSS khusus untuk halaman CRUD Wisata */
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
</style>

<h2 class="section-title">
    <i class="fa fa-map-marked-alt"></i> <?php echo t('manage_tourism') ?: 'Kelola Wisata'; ?>
</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- Form Tambah/Edit Wisata -->
<div class="crud-panel shadow mb-4">
    <div class="panel-title">
        <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
        <?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Wisata
    </div>
    <div class="card-body">
        <form method="POST" action="?page=wisata" enctype="multipart/form-data" class="crud-form">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id_wisata']; ?>">
                <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="judul">Judul:</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?php echo $edit_data['judul'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="durasi">Durasi:</label>
                <input type="text" class="form-control" id="durasi" name="durasi" value="<?php echo $edit_data['durasi'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar:</label>
                <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
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
                    <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $edit_data ? 'Update' : 'Tambah'; ?> Wisata
                </button>
                
                <?php if ($edit_data): ?>
                    <a href="?page=wisata" class="btn btn-warning">
                        <i class="fa fa-times"></i> Batal
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Daftar Wisata -->
<div class="crud-list shadow mb-4">
    <div class="list-title">
        <i class="fa fa-list"></i> Daftar Wisata
    </div>
    <div class="card-body">
        <?php if (empty($wisata_data)): ?>
            <p class="text-muted">Tidak ada data wisata.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover crud-table">
                    <thead class="bg-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="80">Gambar</th>
                            <th>Judul</th>
                            <th>Durasi</th>
                            <th width="120">Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wisata_data as $index => $wisata): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php if ($wisata['gambar']): ?>
                                    <img src="<?php echo $base . '/' . $wisata['gambar']; ?>" class="thumb-img" onerror="this.src='https://source.unsplash.com/random/80x80/?bali'">
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($wisata['judul']); ?></td>
                            <td><?php echo htmlspecialchars($wisata['durasi']); ?></td>
                            <td><?php echo date('d M Y', strtotime($wisata['tanggal_ditambahkan'])); ?></td>
                            <td>
                                <a href="?page=wisata&edit=<?php echo $wisata['id_wisata']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="?page=wisata&hapus=<?php echo $wisata['id_wisata']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus wisata ini?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $total_pages = (int)ceil($total_wisata_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=wisata&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>