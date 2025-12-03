<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';

$where = $search ? "WHERE judul LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM galeri $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM galeri");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "s", $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_galeri_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT * FROM galeri $where ORDER BY tanggal_upload DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "s", $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$galeri_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
        <h3><i class="fa fa-images"></i> Daftar Galeri (<?php echo $total_galeri_all; ?>)</h3>
        <a href="?page=galeri&action=add" class="btn btn-success">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    
    <form method="POST" action="?page=galeri" class="search-box">
        <input type="text" name="cari" placeholder="Cari judul galeri..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
        <?php if ($search): ?>
            <a href="?page=galeri" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($galeri_data)): ?>
        <div class="empty-state">
            <i class="fa fa-images"></i>
            <p>Tidak ada data galeri</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th width="80">Gambar</th>
                        <th>Judul Galeri</th>
                        <th width="120">Tanggal Upload</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($galeri_data as $index => $galeri): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <?php if ($galeri['gambar']): ?>
                                <img src="<?php echo $base . '/' . $galeri['gambar']; ?>" class="img-preview" 
                                     onerror="this.src='https://source.unsplash.com/random/80x60/?gallery'"
                                     alt="<?php echo htmlspecialchars($galeri['judul']); ?>">
                            <?php else: ?>
                                <div class="img-preview" style="background: var(--cream); display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image text-muted"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($galeri['judul']); ?></strong>
                        </td>
                        <td><?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="?page=galeri&action=edit&id=<?php echo $galeri['id_galeri']; ?>" 
                                   class="btn btn-primary btn-sm" title="Edit">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="?page=galeri&action=delete&id=<?php echo $galeri['id_galeri']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus galeri ini?')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_galeri_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=galeri&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>">
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