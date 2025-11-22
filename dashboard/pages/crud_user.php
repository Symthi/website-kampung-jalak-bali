<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;

$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM user");
$total_user_all = mysqli_fetch_assoc($total_q)['cnt'];

$query = "SELECT * FROM user ORDER BY tanggal_daftar DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$user_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Ambil data untuk edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $query = "SELECT * FROM user WHERE id_user=?";
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
    flex: 1;
  }

  .stat-box.admin {
    background: linear-gradient(135deg, #28a745, #20c997);
  }

  .stat-box.regular {
    background: linear-gradient(135deg, #007bff, #0056b3);
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
    <i class="fas fa-users"></i> <?php echo t('manage_users') ?: 'Kelola User'; ?>
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
    <div class="col-md-4">
        <div class="stat-box">
            <div class="stat-label"><i class="fa fa-users"></i> Total User</div>
            <div class="stat-value"><?php echo $total_user_all; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box admin">
            <div class="stat-label"><i class="fa fa-shield-alt"></i> Admin</div>
            <div class="stat-value">
                <?php 
                $admin_count = 0;
                foreach ($user_data as $u) {
                    if ($u['role'] === 'admin') $admin_count++;
                }
                echo $admin_count;
                ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box regular">
            <div class="stat-label"><i class="fa fa-user"></i> User Regular</div>
            <div class="stat-value">
                <?php 
                $regular_count = 0;
                foreach ($user_data as $u) {
                    if ($u['role'] !== 'admin') $regular_count++;
                }
                echo $regular_count;
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Form -->
<div class="crud-panel shadow mb-4">
    <div class="panel-title">
        <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
        <?php echo $edit_data ? 'Edit' : 'Tambah'; ?> User
    </div>
    <div class="card-body">
        <form method="POST" action="?page=user" class="crud-form">
            <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nama">Nama:</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo $edit_data['nama'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo $edit_data['email'] ?? ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" <?php echo $edit_data ? 'placeholder="Kosongkan jika tidak ingin mengubah"' : 'required'; ?>>
                <small class="form-text text-muted"><?php echo $edit_data ? 'Kosongkan password jika tidak ingin mengubah' : 'Minimal 6 karakter'; ?></small>
            </div>

            <div class="form-group">
                <label for="role">Role:</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="user" <?php echo ($edit_data['role'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo ($edit_data['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>

            <div class="form-group" style="display: flex; gap: 1rem; align-items: center; margin-top: 1.5rem;">
                <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                    <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $edit_data ? 'Update' : 'Tambah'; ?> User
                </button>
                
                <?php if ($edit_data): ?>
                    <a href="?page=user" class="btn btn-warning">
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
        <i class="fa fa-list"></i> Daftar User
    </div>
    <div class="card-body">
        <?php if (empty($user_data)): ?>
            <p class="text-muted">Tidak ada user.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover crud-table">
                    <thead class="bg-light">
                        <tr>
                            <th width="40">No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th width="80">Role</th>
                            <th width="100">Tanggal</th>
                            <th width="150">Aksi</th>
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
                            <td>
                                <span class="status-badge <?php echo $user['role'] === 'admin' ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d M Y', strtotime($user['tanggal_daftar'])); ?></td>
                            <td>
                                <a href="?page=user&edit=<?php echo $user['id_user']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                
                                <?php if ($user['id_user'] != $_SESSION['user_id']): ?>
                                    <a href="?page=user&hapus=<?php echo $user['id_user']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Yakin hapus user <?php echo htmlspecialchars($user['nama']); ?>?')">
                                        <i class="fa fa-trash"></i> Hapus
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $total_pages = (int)ceil($total_user_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=user&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>