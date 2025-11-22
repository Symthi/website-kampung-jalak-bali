<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM produk");
$total_produk_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM produk ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$produk_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM produk WHERE id_produk=?";
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

  .stat-box {
    background: linear-gradient(135deg, var(--dark-green), var(--muted-green));
    color: var(--white);
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    box-shadow: 0 4px 12px rgba(53, 64, 36, 0.2);
  }

  .stat-label {
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.9;
  }

  .stat-value {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 0.5rem;
  }
</style>

<h2 class="section-title">
    <i class="fa fa-box"></i> <?php echo t('manage_products') ?: 'Kelola Produk'; ?>
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

<!-- Statistik -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="stat-box">
            <div class="stat-label"><i class="fa fa-box"></i> Total Produk</div>
            <div class="stat-value"><?php echo $total_produk_all; ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="stat-box" style="background: linear-gradient(135deg, #28a745, #20c997);">
            <div class="stat-label"><i class="fa fa-cubes"></i> Total Stok</div>
            <div class="stat-value">
                <?php 
                $total_stok = 0;
                foreach ($produk_data as $p) {
                    $total_stok += $p['stok'];
                }
                echo $total_stok;
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="crud-panel shadow mb-4">
    <div class="panel-title">
        <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
        <?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Produk
    </div>
    <div class="card-body">
        <form method="POST" action="?page=produk" enctype="multipart/form-data" class="crud-form">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id_produk']; ?>">
                <input type="hidden" name="gambar_lama" value="<?php echo $edit_data['gambar']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nama">Nama Produk:</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="deskripsi">Deskripsi:</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="5" required><?php echo $edit_data['deskripsi'] ?? ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="harga">Harga (Rp):</label>
                <input type="number" class="form-control" id="harga" name="harga" value="<?php echo $edit_data['harga'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="stok">Stok:</label>
                <input type="number" class="form-control" id="stok" name="stok" value="<?php echo $edit_data['stok'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="gambar">Gambar:</label>
                <input type="file" class="form-control-file" id="gambar" name="gambar" accept="image/*" <?php echo !$edit_data ? 'required' : ''; ?>>
                <small class="form-text text-muted">Format: JPG, JPEG, PNG, GIF (Max: 2MB)</small>
                
                <?php if ($edit_data && $edit_data['gambar']): ?>
                    <div class="mt-2">
                        <img src="<?php echo $base . '/' . $edit_data['gambar']; ?>" class="info-img" style="max-width: 200px;" onerror="this.src='https://source.unsplash.com/random/200x150/?merchandise'">
                        <p class="small text-muted mt-2">Gambar saat ini</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="form-group" style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                    <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $edit_data ? 'Update' : 'Tambah'; ?> Produk
                </button>
                
                <?php if ($edit_data): ?>
                    <a href="?page=produk" class="btn btn-warning">
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
        <i class="fa fa-list"></i> Daftar Produk
    </div>
    <div class="card-body">
        <?php if (empty($produk_data)): ?>
            <p class="text-muted">Tidak ada data produk.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover crud-table">
                    <thead class="bg-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="80">Gambar</th>
                            <th>Nama</th>
                            <th width="100">Harga</th>
                            <th width="60">Stok</th>
                            <th width="100">Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produk_data as $index => $produk): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <?php if ($produk['gambar']): ?>
                                    <img src="<?php echo $base . '/' . $produk['gambar']; ?>" class="thumb-img" onerror="this.src='https://source.unsplash.com/random/80x80/?merchandise'">
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($produk['nama']); ?></td>
                            <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                            <td><span class="badge badge-<?php echo $produk['stok'] > 0 ? 'success' : 'danger'; ?>"><?php echo $produk['stok']; ?></span></td>
                            <td><?php echo date('d M Y', strtotime($produk['tanggal_ditambahkan'])); ?></td>
                            <td>
                                <a href="?page=produk&edit=<?php echo $produk['id_produk']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="?page=produk&hapus=<?php echo $produk['id_produk']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $total_pages = (int)ceil($total_produk_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=produk&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>