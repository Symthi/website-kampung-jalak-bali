<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';

$where = $search ? "WHERE k.isi LIKE ? OR u.nama LIKE ? OR w.judul LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM komentar k JOIN user u ON k.id_user = u.id_user JOIN wisata w ON k.id_wisata = w.id_wisata $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM komentar");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "sss", $search_param, $search_param, $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_komentar_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT k.*, u.nama as nama_user, u.email, w.judul as judul_wisata FROM komentar k JOIN user u ON k.id_user = u.id_user JOIN wisata w ON k.id_wisata = w.id_wisata $where ORDER BY k.tanggal DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "sss", $search_param, $search_param, $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$komentar_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
        <h3><i class="fa fa-comments"></i> Daftar Komentar (<?php echo $total_komentar_all; ?>)</h3>
    </div>
    
    <form method="POST" action="?page=komentar" class="search-box">
        <input type="text" name="cari" placeholder="Cari komentar, pengguna, atau wisata..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
        <?php if ($search): ?>
            <a href="?page=komentar" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($komentar_data)): ?>
        <div class="empty-state">
            <i class="fa fa-comments"></i>
            <p>Tidak ada data komentar</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th width="120">Pengguna</th>
                        <th width="120">Wisata</th>
                        <th>Komentar</th>
                        <th width="90">Tanggal</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($komentar_data as $index => $komentar): ?>
                    <tr>
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars($komentar['nama_user']); ?></strong>
                            <br>
                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($komentar['email']); ?></small>
                        </td>
                        <td>
                            <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars(substr($komentar['judul_wisata'], 0, 30)); ?></strong>
                        </td>
                        <td>
                            <p class="mb-0" style="font-size: 0.9rem;"><?php echo substr(htmlspecialchars($komentar['isi']), 0, 80); ?></p>
                        </td>
                        <td><?php echo date('d M Y', strtotime($komentar['tanggal'])); ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="?page=komentar&action=delete&id=<?php echo $komentar['id_komentar']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus komentar ini?')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </a>
                                <a href="<?php echo $base; ?>/detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" 
                                   class="btn btn-primary btn-sm" target="_blank" title="Lihat Wisata">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_komentar_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=komentar&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>">
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