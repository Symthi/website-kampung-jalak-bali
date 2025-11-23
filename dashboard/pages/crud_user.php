<?php
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$search = isset($_POST['cari']) ? trim($_POST['cari']) : '';
$edit_data = null;

$where = $search ? "WHERE nama LIKE ? OR email LIKE ?" : "";
$search_param = $search ? "%$search%" : "";

$count_q = $search ? 
    mysqli_prepare($koneksi, "SELECT COUNT(*) as cnt FROM user $where") :
    mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM user");
    
if ($search) {
    mysqli_stmt_bind_param($count_q, "ss", $search_param, $search_param);
    mysqli_stmt_execute($count_q);
    $count_result = mysqli_stmt_get_result($count_q);
} else {
    $count_result = $count_q;
}
$total_user_all = mysqli_fetch_assoc($count_result)['cnt'];

$query_str = "SELECT * FROM user $where ORDER BY tanggal_daftar DESC LIMIT $per_page OFFSET $offset";
$query = $search ? mysqli_prepare($koneksi, $query_str) : mysqli_query($koneksi, $query_str);
if ($search) {
    mysqli_stmt_bind_param($query, "ss", $search_param, $search_param);
    mysqli_stmt_execute($query);
    $result = mysqli_stmt_get_result($query);
} else {
    $result = $query;
}
$user_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<style>
.section-title{font-size:1.8rem;color:var(--dark-green);font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem}
.list-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:0.8rem}
.search-box{display:flex;gap:0.5rem;margin-bottom:1rem}
.search-box input{flex:1;padding:0.6rem;font-size:0.9rem}
.search-box button{padding:0.6rem 1rem;background:var(--dark-green);color:white;border:none;cursor:pointer;font-size:0.9rem}
.crud-form{margin-bottom:1rem}
.form-group{margin-bottom:0.8rem}
.form-group label{margin-bottom:0.3rem;font-weight:600;font-size:0.95rem}
.form-group input, .form-group select{font-size:0.9rem;padding:0.6rem}
.btn-group{display:flex;gap:0.5rem;margin-top:1rem}
table.crud-table{font-size:0.9rem}
table.crud-table th, table.crud-table td{padding:0.6rem 0.5rem!important;vertical-align:middle}
.status-badge{padding:0.3rem 0.5rem;border-radius:3px;font-size:0.8rem}
.status-badge.admin{background:#28a745;color:white}
.status-badge.user{background:#007bff;color:white}
.pagination{margin-top:1rem!important}
</style>

<h2 class="section-title">
    <i class="fas fa-users"></i> <?php echo t('manage_users') ?: 'Kelola User'; ?>
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
        <h3 style="margin:0;font-size:1.1rem"><i class="fa fa-list"></i> Daftar User (<?php echo $total_user_all; ?>)</h3>
        <a href="?page=user&action=add" class="btn btn-success btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>
    </div>
    
    <form method="POST" action="?page=user" class="search-box">
        <input type="text" name="cari" placeholder="Cari nama atau email..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit">Cari</button>
        <?php if ($search): ?>
            <a href="?page=user" class="btn btn-secondary btn-sm" style="padding:0.6rem 1rem;text-decoration:none;color:white">Reset</a>
        <?php endif; ?>
    </form>
    
    <?php if (empty($user_data)): ?>
        <p class="text-muted" style="padding:1rem">Tidak ada data user.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-hover crud-table">
                <thead class="bg-light">
                    <tr>
                        <th width="40">No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th width="70">Role</th>
                        <th width="90">Tanggal</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($user_data as $index => $user): ?>
                    <tr class="<?php echo $user['id_user'] == $_SESSION['user_id'] ? 'table-light' : ''; ?>">
                        <td><?php echo $offset + $index + 1; ?></td>
                        <td>
                            <?php echo htmlspecialchars($user['nama']); ?>
                            <?php if ($user['id_user'] == $_SESSION['user_id']): ?>
                                <span class="badge badge-primary ml-2">Anda</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><span class="status-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                        <td><?php echo date('d M Y', strtotime($user['tanggal_daftar'])); ?></td>
                        <td>
                            <a href="?page=user&edit=<?php echo $user['id_user']; ?>" class="btn btn-primary btn-sm"><i class="fa fa-edit"></i></a>
                            <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                <a href="?page=user&hapus=<?php echo $user['id_user']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin?')"><i class="fa fa-trash"></i></a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php $total_pages = (int)ceil($total_user_all / $per_page); if ($total_pages > 1): ?>
        <nav aria-label="Pagination" style="text-align:center">
            <ul class="pagination justify-content-center">
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                        <?php if ($p == $page): ?>
                            <span class="page-link"><?php echo $p; ?></span>
                        <?php else: ?>
                            <a class="page-link" href="?page=user&p=<?php echo $p; ?><?php echo $search ? '&cari=' . urlencode($search) : ''; ?>"><?php echo $p; ?></a>
                        <?php endif; ?>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>