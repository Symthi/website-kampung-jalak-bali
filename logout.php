<?php
session_start();

// Hapus semua data session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()
        - $params["lifetime"], $params["path"], 
        $params["domain"], $params["secure"], 
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login
header("Location: login.php");
exit();
?>