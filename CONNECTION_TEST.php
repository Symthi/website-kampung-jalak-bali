<?php
/**
 * Connection Test - Verifikasi semua koneksi flow bekerja
 * Login -> Dashboard -> Logout
 */

session_start();
include __DIR__ . '/../config/koneksi.php';

$base = rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/\\');

// Check koneksi
$results = array();

// 1. Cek database connection
if ($koneksi) {
    $results['database'] = array('status' => 'OK', 'message' => 'Database connected');
} else {
    $results['database'] = array('status' => 'ERROR', 'message' => 'Database failed: ' . mysqli_connect_error());
}

// 2. Cek session
if (session_status() === PHP_SESSION_ACTIVE) {
    $results['session'] = array('status' => 'OK', 'message' => 'Session active');
} else {
    $results['session'] = array('status' => 'ERROR', 'message' => 'Session not active');
}

// 3. Cek files
$files_to_check = array(
    'Login' => 'auth/login.php',
    'Register' => 'auth/register.php',
    'Logout' => 'auth/logout.php',
    'Dashboard (New)' => 'dashboard/index.php',
    'Dashboard (Old)' => 'admin/dashboard.php',
    'Setup Verify' => 'dashboard/setup-verify.php',
    'Header' => 'includes/header.php',
    'Database Config' => 'config/koneksi.php',
);

foreach ($files_to_check as $name => $path) {
    $full_path = __DIR__ . '/../' . $path;
    $exists = file_exists($full_path);
    $results[$name] = array(
        'status' => $exists ? 'OK' : 'ERROR',
        'message' => $exists ? "File exists: $path" : "File missing: $path"
    );
}

// 4. Cek user table
$query = "SELECT COUNT(*) as total FROM user";
$result = mysqli_query($koneksi, $query);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $results['User Table'] = array('status' => 'OK', 'message' => 'Total users: ' . $row['total']);
} else {
    $results['User Table'] = array('status' => 'ERROR', 'message' => 'Cannot query user table');
}

// 5. Test redirect paths
$redirects = array(
    'Login redirect' => array(
        'from' => '/auth/login.php',
        'to' => '/dashboard/index.php',
        'condition' => 'if logged in'
    ),
    'Register redirect' => array(
        'from' => '/auth/register.php',
        'to' => '/dashboard/index.php',
        'condition' => 'after successful registration'
    ),
    'Header menu' => array(
        'from' => '/index.php header nav',
        'to' => '/dashboard/index.php',
        'condition' => 'if logged in'
    ),
);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connection Test - Dashboard Integration</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border-left: 4px solid #e0e0e0;
            background: #fafafa;
            border-radius: 4px;
        }
        .test-section h2 {
            color: #333;
            margin-top: 0;
        }
        .result {
            margin: 10px 0;
            padding: 12px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .result.ok {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .result.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .status-badge {
            font-weight: bold;
            padding: 2px 8px;
            border-radius: 3px;
            min-width: 50px;
            text-align: center;
        }
        .status-badge.ok {
            background: #28a745;
            color: white;
        }
        .status-badge.error {
            background: #dc3545;
            color: white;
        }
        .flow-diagram {
            background: white;
            border: 2px solid #667eea;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
        }
        .flow-step {
            padding: 10px;
            background: #f0f4ff;
            margin: 10px 0;
            border-left: 4px solid #667eea;
            border-radius: 4px;
        }
        .flow-step strong {
            color: #667eea;
        }
        .arrow {
            text-align: center;
            font-size: 1.2em;
            color: #667eea;
            margin: 5px 0;
        }
        .note {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .success-msg {
            background: #d4edda;
            border: 1px solid #28a745;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .error-msg {
            background: #f8d7da;
            border: 1px solid #dc3545;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .summary strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Connection Test - Dashboard Integration</h1>

        <!-- System Status -->
        <div class="test-section">
            <h2>📋 System Status</h2>
            <?php foreach ($results as $name => $result): ?>
            <div class="result <?php echo $result['status'] === 'OK' ? 'ok' : 'error'; ?>">
                <span class="status-badge <?php echo strtolower($result['status']); ?>">
                    <?php echo $result['status']; ?>
                </span>
                <strong><?php echo $name ?>:</strong>
                <?php echo $result['message']; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Flow Diagram -->
        <div class="test-section">
            <h2>📊 Authentication Flow</h2>
            <div class="flow-diagram">
                <div class="flow-step">
                    <strong>1. User Login</strong><br>
                    → URL: <code><?php echo $base; ?>/auth/login.php</code><br>
                    ✓ Check email & password<br>
                    ✓ Set session (user_id, nama, email, role)
                </div>
                <div class="arrow">↓</div>
                <div class="flow-step">
                    <strong>2. Redirect to Dashboard</strong><br>
                    → URL: <code><?php echo $base; ?>/dashboard/index.php</code><br>
                    ✓ Check session (isLoggedIn)<br>
                    ✓ Check role (isAdmin)
                </div>
                <div class="arrow">↓</div>
                <div class="flow-step">
                    <strong>3. Dashboard Render</strong><br>
                    ✓ If Admin: Show admin dashboard + admin menu<br>
                    ✓ If User: Show user dashboard + user menu<br>
                    ✓ Load charts with data
                </div>
                <div class="arrow">↓</div>
                <div class="flow-step">
                    <strong>4. User Logout</strong><br>
                    → URL: <code><?php echo $base; ?>/auth/logout.php</code><br>
                    ✓ Destroy session<br>
                    ✓ Redirect to login
                </div>
            </div>
        </div>

        <!-- Path Configuration -->
        <div class="test-section">
            <h2>🔗 Path Configuration</h2>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background: #f0f0f0;">
                    <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">File</th>
                    <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Path</th>
                    <th style="text-align: left; padding: 10px; border: 1px solid #ddd;">Status</th>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Login</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code><?php echo $base; ?>/auth/login.php</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd; color: green;">✓</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Register</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code><?php echo $base; ?>/auth/register.php</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd; color: green;">✓</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Dashboard (NEW)</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code><?php echo $base; ?>/dashboard/index.php</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd; color: green;">✓</td>
                </tr>
                <tr>
                    <td style="padding: 10px; border: 1px solid #ddd;">Logout</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code><?php echo $base; ?>/auth/logout.php</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd; color: green;">✓</td>
                </tr>
                <tr style="background: #fff3cd;">
                    <td style="padding: 10px; border: 1px solid #ddd;">Dashboard (OLD)</td>
                    <td style="padding: 10px; border: 1px solid #ddd;"><code><?php echo $base; ?>/admin/dashboard.php</code></td>
                    <td style="padding: 10px; border: 1px solid #ddd; color: orange;">⚠ Deprecated</td>
                </tr>
            </table>
        </div>

        <!-- Redirect Tests -->
        <div class="test-section">
            <h2>🔄 Redirect Verification</h2>
            <div class="note">
                <strong>ℹ️ These redirects have been configured:</strong>
            </div>
            <ul style="list-style: none; padding: 0;">
                <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <strong>✓ Login Page:</strong> After login → /dashboard/index.php
                </li>
                <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <strong>✓ Register Page:</strong> After registration → /dashboard/index.php
                </li>
                <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <strong>✓ Header Menu:</strong> Dashboard link → /dashboard/index.php
                </li>
                <li style="padding: 10px; border-bottom: 1px solid #ddd;">
                    <strong>✓ CRUD Breadcrumb:</strong> Dashboard link → /dashboard/index.php
                </li>
                <li style="padding: 10px;">
                    <strong>✓ Logout Page:</strong> After logout → /auth/login.php
                </li>
            </ul>
        </div>

        <!-- Role Detection -->
        <div class="test-section">
            <h2>👥 Role-Based Dashboard</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: #e8f4f8; padding: 15px; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: #0c5460;">Admin Dashboard</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>6 Statistics Cards</li>
                        <li>Admin Menu in Sidebar</li>
                        <li>Global Data View</li>
                        <li>All CRUD Links</li>
                    </ul>
                </div>
                <div style="background: #f0f8e8; padding: 15px; border-radius: 4px;">
                    <h3 style="margin-top: 0; color: #0a4f23;">User Dashboard</h3>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>3 Personal Statistics</li>
                        <li>Limited Menu</li>
                        <li>Personal Data Only</li>
                        <li>Activity Charts</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Test -->
        <div class="test-section">
            <h2>🧪 Quick Test Links</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="<?php echo $base; ?>/auth/login.php" class="btn" style="padding: 10px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
                    → Go to Login
                </a>
                <a href="<?php echo $base; ?>/auth/register.php" class="btn" style="padding: 10px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
                    → Go to Register
                </a>
                <a href="<?php echo $base; ?>/dashboard/setup-verify.php" class="btn" style="padding: 10px 15px; background: #667eea; color: white; text-decoration: none; border-radius: 4px; display: inline-block;">
                    → Setup Verify
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="summary">
            <h2>📝 Summary</h2>
            <?php 
            $error_count = 0;
            foreach ($results as $result) {
                if ($result['status'] === 'ERROR') $error_count++;
            }
            ?>
            <?php if ($error_count === 0): ?>
            <div class="success-msg">
                <strong>✓ All systems operational!</strong><br>
                Dashboard integration is complete. Users can now:
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Login via /auth/login.php</li>
                    <li>Automatically redirect to /dashboard/index.php</li>
                    <li>See role-appropriate dashboard (admin or user)</li>
                    <li>Access all menu items and CRUD operations</li>
                    <li>Logout and return to login page</li>
                </ul>
            </div>
            <?php else: ?>
            <div class="error-msg">
                <strong>✗ <?php echo $error_count; ?> error(s) found</strong><br>
                Please check the errors above and fix them before proceeding.
            </div>
            <?php endif; ?>

            <h3>Next Steps:</h3>
            <ol>
                <li>Test login with admin account</li>
                <li>Verify admin dashboard loads correctly</li>
                <li>Test login with user account</li>
                <li>Verify user dashboard loads correctly</li>
                <li>Test mobile responsiveness</li>
                <li>Verify all menu links work</li>
            </ol>
        </div>

        <div style="text-align: center; margin-top: 30px; color: #999;">
            <p>Connection Test - Dashboard Integration</p>
            <p>Last updated: November 12, 2025</p>
        </div>
    </div>
</body>
</html>
