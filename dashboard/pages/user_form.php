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

<div class="form-wrapper-enhanced">
    <div class="form-header-enhanced">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit User' : 'Tambah User'; ?>
        </h2>
        <a href="?page=user" class="back-link-enhanced"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>

    <form method="POST" action="?page=user" class="form-grid-enhanced two-column">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
        <?php endif; ?>

        <div class="form-group-enhanced">
            <label for="nama"><i class="fa fa-user"></i> Nama</label>
            <input type="text" id="nama" name="nama" 
                   value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" 
                   placeholder="Nama lengkap" required>
        </div>

        <div class="form-group-enhanced">
            <label for="email"><i class="fa fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" 
                   value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>" 
                   placeholder="email@contoh.com" required>
        </div>

        <div class="form-group-enhanced">
            <label for="password"><i class="fa fa-lock"></i> Password</label>
            <input type="password" id="password" name="password" 
                   placeholder="<?php echo $edit_data ? 'Kosongkan jika tidak ubah' : 'Password minimal 6 karakter'; ?>" 
                   <?php echo !$edit_data ? 'required' : ''; ?>>
            <?php if ($edit_data): ?>
                <small>Biarkan kosong untuk tidak mengubah password</small>
            <?php endif; ?>
        </div>

        <div class="form-group-enhanced">
            <label for="role"><i class="fa fa-user-tag"></i> Role</label>
            <select id="role" name="role" required>
                <option value="">Pilih role</option>
                <option value="admin" <?php echo ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo ($edit_data && $edit_data['role'] === 'user') ? 'selected' : ''; ?>>User</option>
            </select>
        </div>

        <div class="form-actions-enhanced">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn-primary-enhanced">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update User' : 'Tambah User'; ?>
            </button>
            <a href="?page=user" class="btn-secondary-enhanced">
                <i class="fa fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validasi password saat submit
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const isEdit = document.querySelector('input[name="id"]') !== null;
            
            // Validasi minimal 6 karakter jika password diisi
            if (password && password.length < 6) {
                e.preventDefault();
                alert('Password harus minimal 6 karakter!');
                return false;
            }
            
            // Untuk tambah baru, password wajib diisi
            if (!isEdit && !password) {
                e.preventDefault();
                alert('Password harus diisi untuk user baru!');
                return false;
            }
        });
    }
});
</script>