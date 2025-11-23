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

<style>
.section-title{font-size:1.8rem;color:var(--dark-green);font-weight:700;display:flex;align-items:center;gap:0.5rem;margin-bottom:1rem}
.form-wrapper{max-width:600px;margin:0 auto}
.form-group{margin-bottom:1rem}
.form-group label{display:block;margin-bottom:0.4rem;font-weight:600;font-size:0.95rem}
.form-group input, .form-group textarea, .form-group select{width:100%;padding:0.7rem;font-size:0.9rem;border:1px solid #ddd;border-radius:4px;font-family:inherit}
.form-group textarea{resize:vertical;min-height:100px}
.form-group small{display:block;margin-top:0.3rem;color:#666;font-size:0.85rem}
.btn-group{display:flex;gap:0.8rem;margin-top:1.5rem}
.btn-group .btn{flex:1;padding:0.8rem;font-size:0.95rem}
.back-link{display:inline-block;margin-bottom:1rem;color:var(--dark-green);text-decoration:none;font-size:0.9rem}
.back-link:hover{text-decoration:underline}
</style>

<a href="?page=user" class="back-link"><i class="fa fa-arrow-left"></i> Kembali ke Daftar</a>

<h2 class="section-title">
    <i class="fa <?php echo $edit_data ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
    <?php echo $edit_data ? 'Edit' : 'Tambah'; ?> User
</h2>

<div class="form-wrapper">
    <form method="POST" action="?page=user">
        <?php if ($edit_data): ?>
            <input type="hidden" name="id" value="<?php echo $edit_data['id_user']; ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($edit_data['nama'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_data['email'] ?? ''); ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password<?php echo !$edit_data ? '' : ' (Kosongkan jika tidak ingin diubah)'; ?>:</label>
            <input type="password" id="password" name="password" <?php echo !$edit_data ? 'required' : ''; ?>>
        </div>

        <div class="form-group">
            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin" <?php echo ($edit_data && $edit_data['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                <option value="user" <?php echo ($edit_data && $edit_data['role'] === 'user') ? 'selected' : ''; ?>>User</option>
            </select>
        </div>

        <div class="btn-group">
            <button type="submit" name="<?php echo $edit_data ? 'edit' : 'tambah'; ?>" class="btn btn-primary">
                <i class="fa <?php echo $edit_data ? 'fa-save' : 'fa-plus'; ?>"></i>
                <?php echo $edit_data ? 'Update' : 'Tambah'; ?> User
            </button>
            <a href="?page=user" class="btn btn-secondary">
                <i class="fa fa-times"></i> Batal
            </a>
        </div>
    </form>
</div>
