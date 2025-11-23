<?php
// Process CRUD Operations untuk User

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['error_message'])) {
        $_SESSION['error_message'] = '';
    }
    
    if (isset($_POST['tambah'])) {
        $_SESSION['error_message'] = '';
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];
        
        if (strlen($password) < 6) {
            $_SESSION['error_message'] = "Password minimal 6 karakter!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO user (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "User berhasil ditambahkan!";
            } else {
                $_SESSION['error_message'] = "Gagal menambahkan user! Email mungkin sudah digunakan.";
            }
        }
        
        header("Location: ?page=user");
        exit();
        
    } elseif (isset($_POST['edit'])) {
        $_SESSION['error_message'] = '';
        $id = $_POST['id'];
        $nama = $_POST['nama'];
        $email = $_POST['email'];
        $role = $_POST['role'];
        
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                $_SESSION['error_message'] = "Password minimal 6 karakter!";
            } else {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $query = "UPDATE user SET nama=?, email=?, password=?, role=? WHERE id_user=?";
                $stmt = mysqli_prepare($koneksi, $query);
                mysqli_stmt_bind_param($stmt, "ssssi", $nama, $email, $password, $role, $id);
            }
        } else {
            $query = "UPDATE user SET nama=?, email=?, role=? WHERE id_user=?";
            $stmt = mysqli_prepare($koneksi, $query);
            mysqli_stmt_bind_param($stmt, "sssi", $nama, $email, $role, $id);
        }
        
        if (empty($_SESSION['error_message'])) {
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "User berhasil diupdate!";
            } else {
                $_SESSION['error_message'] = "Gagal mengupdate user!";
            }
        }
        
        header("Location: ?page=user");
        exit();
    }
}

// Hapus user
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    if ($id == $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Tidak bisa menghapus akun sendiri!";
    } else {
        $query = "DELETE FROM user WHERE id_user=?";
        $stmt = mysqli_prepare($koneksi, $query);
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success_message'] = "User berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus user!";
        }
    }
    
    header("Location: ?page=user");
    exit();
}
?>
