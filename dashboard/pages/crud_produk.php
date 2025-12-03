<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';

$where = $search ? "WHERE nama LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM produk $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM produk");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "s", $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_produk_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT * FROM produk $where ORDER BY tanggal_ditambahkan DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "s", $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$produk_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success">
        <i class="fa fa-check-circle"></i>
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="close">&times;</button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle"></i>
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="close">&times;</button>
    </div>
<?php endif; ?>

<div class="crud-list">
    <div class="list-header">
        <h3><i class="fa fa-box"></i> Daftar Produk (<?php echo $total_produk_all; ?>)</h3>
        <a href="?page=produk&action=add" class="btn btn-success">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    
    <form method="POST" action="?page=produk" class="search-box">
        <input type="text" name="cari" placeholder="Cari nama produk..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
        <?php if ($search): ?>
            <a href="?page=produk" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($produk_data)): ?>
        <div class="empty-state">
            <i class="fa fa-box"></i>
            <p>Tidak ada data produk</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th width="60">Gambar</th>
                        <th>Nama Produk</th>
                        <th width="90">Harga</th>
                        <th width="50">Stok</th>
                        <th width="90">Tanggal</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produk_data as $index => $produk): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <?php if ($produk['gambar']): ?>
                                <img src="<?php echo $base . '/' . $produk['gambar']; ?>" class="thumb-img" 
                                     onerror="this.src='https://source.unsplash.com/random/50x50/?merchandise'"
                                     alt="<?php echo htmlspecialchars($produk['nama']); ?>">
                            <?php else: ?>
                                <div class="thumb-img" style="background: var(--cream); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($produk['nama']); ?></strong>
                        </td>
                        <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $produk['stok'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $produk['stok']; ?>
                            </span>
                        </td>
                        <td><?php echo date('d M Y', strtotime($produk['tanggal_ditambahkan'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="?page=produk&action=edit&id=<?php echo $produk['id_produk']; ?>" 
                                   class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="?page=produk&action=delete&id=<?php echo $produk['id_produk']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus produk ini?')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_produk_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=produk&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>">
                                <?php echo $p; ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.alert .close').forEach(function(closeBtn) {
        closeBtn.addEventListener('click', function() {
            this.closest('.alert').remove();
        });
    });
});
</script>