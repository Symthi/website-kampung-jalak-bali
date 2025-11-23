<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';
$edit_data = null;

$where = $search ? "WHERE judul LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM informasi $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM informasi");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "s", $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_informasi_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT * FROM informasi $where ORDER BY tanggal_dibuat DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "s", $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$informasi_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<style>
.section-title{font-size:1.8rem;color:var(--dark-green);font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem}
.list-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem}
.search-box{display:flex;gap:0.5rem;margin-bottom:1rem}
.search-box input{flex:1;padding:0.6rem;font-size:0.9rem}
.search-box button{padding:0.6rem 1rem;background:var(--dark-green);color:white;border:none;cursor:pointer;font-size:0.9rem}
.img-preview{max-width:150px;max-height:120px;margin:0.5rem 0;border-radius:4px;object-fit:cover}
.form-preview-box{background:#f8f9fa;padding:0.8rem;border-radius:4px;margin:0.5rem 0}
.crud-form{margin-bottom:1rem}
.form-group{margin-bottom:0.8rem}
.form-group label{margin-bottom:0.3rem;font-weight:600;font-size:0.95rem}
.form-group input, .form-group textarea, .form-group select{font-size:0.9rem;padding:0.6rem}
.btn-group{display:flex;gap:0.5rem;margin-top:1rem}
table.crud-table{font-size:0.9rem}
table.crud-table th, table.crud-table td{padding:0.6rem 0.5rem!important;vertical-align:middle}
table.crud-table .thumb-img{max-width:50px;max-height:50px;border-radius:4px}
.badge{padding:0.3rem 0.5rem;font-size:0.8rem}
.pagination{margin-top:1rem!important}
</style>

<h2 class="section-title">
    <i class="fa fa-info-circle"></i> <?php echo t('manage_information') ?: 'Kelola Informasi'; ?>
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
        <h3 style="margin:0;font-size:1.1rem"><i class="fa fa-list"></i> Daftar Informasi (<?php echo $total_informasi_all; ?>)</h3>
        <a href="?page=informasi&action=add" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    
    <form method="POST" action="?page=informasi" class="search-box">
        <input type="text" name="cari" placeholder="Cari judul informasi..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
        <?php if ($search): ?>
            <a href="?page=informasi" class="btn btn-secondary btn-sm" style="padding:0.6rem 1rem;text-decoration:none;color:white">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($informasi_data)): ?>
        <p class="text-muted" style="padding:1rem">Tidak ada data informasi.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover crud-table">
                <thead class="bg-light">
                    <tr>
                        <th width="40">No</th>
                        <th width="70">Gambar</th>
                        <th>Judul</th>
                        <th width="70">Kategori</th>
                        <th width="90">Tanggal</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($informasi_data as $index => $info): ?>
                    <tr>
                        <td><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <?php if ($info['gambar']): ?>
                                <img src="<?php echo $base . '/' . $info['gambar']; ?>" class="thumb-img" onerror="this.src='https://source.unsplash.com/random/50x50/?article'">
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($info['judul']); ?></td>
                        <td><span class="badge badge-info"><?php echo ucfirst($info['kategori']); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($info['tanggal_dibuat'])); ?></td>
                        <td>
                            <a href="?page=informasi&action=edit&id=<?php echo $informasi['id_informasi']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                            <a href="?page=informasi&action=delete&id=<?php echo $informasi['id_informasi']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_informasi_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination" style="text-align:center">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=informasi&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>