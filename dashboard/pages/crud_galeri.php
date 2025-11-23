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


<h2 class="section-title">
    <i class="fa fa-images"></i> <?php echo t('manage_gallery') ?: 'Kelola Galeri'; ?>
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

<!-- Daftar -->
<div class="crud-list shadow mb-4">
    <div class="list-header">
        <h3 style="margin:0;font-size:1.1rem"><i class="fa fa-list"></i> Daftar Galeri (<?php echo $total_galeri_all; ?>)</h3>
        <a href="?page=galeri&action=add" class="btn btn-success btn-sm"><i class="fa fa-plus"></i> Tambah</a>
    </div>
    
    <form method="POST" action="?page=galeri" class="search-box">
        <input type="text" name="cari" placeholder="Cari judul..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
        <?php if ($search): ?>
            <a href="?page=galeri" class="btn btn-secondary btn-sm" style="padding:0.6rem 1rem;text-decoration:none;color:white">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($galeri_data)): ?>
        <p class="text-muted" style="padding:1rem">Tidak ada data galeri.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover crud-table">
                <thead class="bg-light">
                    <tr>
                        <th width="40">No</th>
                        <th width="100">Gambar</th>
                        <th>Judul</th>
                        <th width="120">Tanggal Upload</th>
                        <th width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($galeri_data as $index => $galeri): ?>
                    <tr>
                        <td><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <?php if ($galeri['gambar']): ?>
                                <img src="<?php echo $base . '/' . $galeri['gambar']; ?>" class="img-preview" onerror="this.src='https://source.unsplash.com/random/100x80/?gallery'">
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($galeri['judul']); ?></td>
                        <td><?php echo date('d M Y', strtotime($galeri['tanggal_upload'])); ?></td>
                        <td>
                            <a href="?page=galeri&action=edit&id=<?php echo $galeri['id_galeri']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                            <a href="?page=galeri&action=delete&id=<?php echo $galeri['id_galeri']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_galeri_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination" style="text-align:center">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=galeri&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>