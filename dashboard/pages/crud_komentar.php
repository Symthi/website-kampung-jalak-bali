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

<style>
.section-title{font-size:1.8rem;color:var(--dark-green);font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem}
.list-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem}
.search-box{display:flex;gap:0.5rem;margin-bottom:1rem}
.search-box input{flex:1;padding:0.6rem;font-size:0.9rem}
.search-box button{padding:0.6rem 1rem;background:var(--dark-green);color:white;border:none;cursor:pointer;font-size:0.9rem}
table.crud-table{font-size:0.9rem}
table.crud-table th, table.crud-table td{padding:0.6rem 0.5rem!important;vertical-align:middle}
.badge{padding:0.3rem 0.5rem;font-size:0.8rem}
.pagination{margin-top:1rem!important}
</style>

<h2 class="section-title">
    <i class="fa fa-comments"></i> <?php echo t('manage_comments') ?: 'Kelola Komentar'; ?>
</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" style="padding:0.6rem;margin-bottom:0.8rem" role="alert">
        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" style="padding:0.6rem;margin-bottom:0.8rem" role="alert">
        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
<?php endif; ?>

<!-- Daftar Komentar -->
<div class="crud-list shadow mb-4">
    <div class="list-header">
        <h3 style="margin:0;font-size:1.1rem"><i class="fa fa-list"></i> Daftar Komentar (<?php echo $total_komentar_all; ?>)</h3>
    </div>
    
    <form method="POST" action="?page=komentar" class="search-box">
        <input type="text" name="cari" placeholder="Cari komentar, pengguna, atau wisata..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
        <?php if ($search): ?>
            <a href="?page=komentar" class="btn btn-secondary btn-sm" style="padding:0.6rem 1rem;text-decoration:none;color:white">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($komentar_data)): ?>
        <p class="text-muted" style="padding:1rem">Tidak ada komentar.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover crud-table">
                <thead class="bg-light">
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
                        <td><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($komentar['nama_user']); ?></strong><br>
                            <small class="text-muted" style="font-size:0.75rem"><?php echo htmlspecialchars($komentar['email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars(substr($komentar['judul_wisata'], 0, 30)); ?></td>
                        <td><p class="mb-0" style="font-size:0.9rem"><?php echo substr(htmlspecialchars($komentar['isi']), 0, 80); ?></p></td>
                        <td><?php echo date('d M Y', strtotime($komentar['tanggal'])); ?></td>
                        <td>
                            <a href="?page=komentar&action=delete&id=<?php echo $komentar['id_komentar']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="fa fa-trash"></i></a>
                            <a href="<?php echo $base; ?>/detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" class="btn btn-primary btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_komentar_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination" style="text-align:center">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=komentar&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>