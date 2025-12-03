<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';

$where = $search ? "WHERE nama LIKE ? OR email LIKE ? OR subjek LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM pesan $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM pesan");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "sss", $search_param, $search_param, $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_pesan_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT * FROM pesan $where ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "sss", $search_param, $search_param, $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$pesan_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

$belum_dibaca = 0;
$sudah_dibaca = 0;
foreach ($pesan_data as $pesan) {
    if (!$pesan['dibaca']) $belum_dibaca++;
    else $sudah_dibaca++;
}
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
        <h3><i class="fa fa-envelope"></i> Daftar Pesan (<?php echo $total_pesan_all; ?>) | <span class="text-danger">Baru: <?php echo $belum_dibaca; ?></span></h3>
    </div>
    
    <form method="POST" action="?page=pesan" class="search-box">
        <input type="text" name="cari" placeholder="Cari pengirim, email, atau subjek..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit"><i class="fa fa-search"></i> Cari</button>
        <?php if ($search): ?>
            <a href="?page=pesan" class="btn btn-secondary">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($pesan_data)): ?>
        <div class="empty-state">
            <i class="fa fa-envelope"></i>
            <p>Tidak ada data pesan</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="crud-table">
                <thead>
                    <tr>
                        <th width="40">No</th>
                        <th width="120">Pengirim</th>
                        <th width="100">Subjek</th>
                        <th>Pesan</th>
                        <th width="90">Tanggal</th>
                        <th width="60">Status</th>
                        <th width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pesan_data as $index => $pesan): ?>
                    <tr class="<?php echo !$pesan['dibaca'] ? 'row-unread' : ''; ?>">
                        <td class="text-center"><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars($pesan['nama']); ?></strong>
                            <br>
                            <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($pesan['email']); ?></small>
                        </td>
                        <td>
                            <strong style="font-size: 0.9rem;"><?php echo htmlspecialchars(substr($pesan['subjek'], 0, 20)); ?></strong>
                        </td>
                        <td>
                            <p class="mb-0" style="font-size: 0.9rem;"><?php echo substr(htmlspecialchars($pesan['isi']), 0, 70); ?></p>
                        </td>
                        <td><?php echo date('d M Y', strtotime($pesan['tanggal'])); ?></td>
                        <td>
                            <?php if (!$pesan['dibaca']): ?>
                                <span class="badge badge-danger">Baru</span>
                            <?php else: ?>
                                <span class="badge badge-success">Dibaca</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <?php if (!$pesan['dibaca']): ?>
                                    <a href="?page=pesan&baca=<?php echo $pesan['id_pesan']; ?>" 
                                       class="btn btn-primary btn-sm" title="Tandai Sudah Dibaca">
                                        <i class="fa fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="?page=pesan&action=delete&id=<?php echo $pesan['id_pesan']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin ingin menghapus pesan ini?')" title="Hapus">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_pesan_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=pesan&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>">
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