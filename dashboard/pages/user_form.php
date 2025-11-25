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

<a href="?page=user" class="back-link"><i class="fa fa-arrow-left"></i> Kembali</a>

<div class="form-wrapper">
    <div class="form-header">
        <h2>
            <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
            <?php echo $edit_data ? 'Edit User' : 'Tambah User'; ?>
        </h2>
    </div>

    <form method="POST" action="?page=user">
        <!-- Hidden inputs DI LUAR .form-grid -->
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
        <?php endif; ?>

        <!-- ✅ Grid khusus USER: 2 kolom -->
        <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); gap: 0.6rem;">

            <!-- Baris 1: Nama & Email -->
            <div class="form-group">
                <label for="nama"><i class="fa fa-user"></i> Nama</label>
                <input type="text" id="nama" name="nama" 
                       value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" 
                       placeholder="Nama lengkap" required>
            </div>

            <div class="form-group">
                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>" 
                       placeholder="email@contoh.com" required>
            </div>

            <!-- Baris 2: Password & Role -->
            <div class="form-group">
                <label for="password"><i class="fa fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" 
                       placeholder="<?php echo $edit_data ? 'Kosongkan jika tidak ubah' : 'Password minimal 6 karakter'; ?>" 
                       <?php echo !$edit_data ? 'required' : ''; ?>>
                <?php if ($edit_data): ?>
                    <small style="font-size:0.72rem;">Biarkan kosong untuk tidak mengubah password</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="role"><i class="fa fa-user-tag"></i> Role</label>
                <select id="role" name="role" required>
                    <option value="">Pilih role</option>
                    <option value="admin" <?php echo ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="user" <?php echo ($edit_data && $edit_data['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                </select>
            </div>

            <!-- Tombol — full width -->
            <div class="form-actions" style="grid-column: 1 / -1; margin-top: 0.6rem;">
                <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                    <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                    <?php echo $edit_data ? 'Update User' : 'Tambah User'; ?>
                </button>
                <a href="?page=user" class="btn btn-secondary">
                    <i class="fa fa-times"></i> Batal
                </a>
            </div>

        </div> <!-- /form-grid -->
    </form>
</div>