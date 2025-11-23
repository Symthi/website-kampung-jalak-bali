<?php
$edit_data = null;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM user WHERE id_user=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $edit_data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}
?>

<a href="?page=user" class="back-link"><i class="fa fa-arrow-left"></i> Kembali ke Daftar User</a>

<div class="form-wrapper">
    <div class="form-header">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit User' : 'Tambah User Baru'; ?>
        </h2>
        <div class="form-info">
            <i class="fa fa-user"></i> Kelola data pengguna sistem
        </div>
    </div>

    <form method="POST" action="?page=user" class="form-grid">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
        <?php endif; ?>

        <div class="form-group full-width" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="nama"><i class="fa fa-user"></i> Nama Lengkap</label>
                <input type="text" id="nama" name="nama" 
                       value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" 
                       placeholder="Nama lengkap user" required>
            </div>
            
            <div>
                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>" 
                       placeholder="alamat@email.com" required>
            </div>
        </div>

        <div class="form-group full-width" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="<?php echo $edit_data ? 'Kosongkan jika tidak diubah' : 'Password minimal 6 karakter'; ?>" 
                       <?php echo !$edit_data ? 'required' : ''; ?>>
                <small style="margin-top: 0.3rem; display: block;">
                    <?php echo $edit_data ? 'Biarkan kosong untuk menjaga password saat ini' : ''; ?>
                </small>
            </div>
            
            <div>
                <label for="role"><i class="fa fa-user-tag"></i> Role</label>
                <select id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" <?php echo ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo ($edit_data && $edit_data['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update User' : 'Tambah User'; ?>
            </button>
            <a href="?page=user" class="btn btn-secondary">
                <i class="fa fa-times"></i> Batalkan
            </a>
        </div>
    </form>
</div>