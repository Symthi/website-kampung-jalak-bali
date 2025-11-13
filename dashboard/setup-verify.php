<?php
/**
 * Dashboard Setup Verification Script
 * Run this file to verify all dashboard components are working correctly
 */

session_start();
include __DIR__ . '/../config/koneksi.php';

$checks = array();
$errors = array();

// Check 1: Database connection
if ($koneksi) {
    $checks['database'] = '✓ Database connected';
} else {
    $errors['database'] = '✗ Database connection failed';
}

// Check 2: Session
if (isset($_SESSION['user_id'])) {
    $checks['session'] = '✓ Session active';
} else {
    $checks['session'] = '⚠ Session not active (normal if not logged in)';
}

// Check 3: File existence
$files_to_check = array(
    'index.php' => __DIR__ . '/index.php',
    'css/sb-admin-2.min.css' => __DIR__ . '/css/sb-admin-2.min.css',
    'css/responsive-custom.css' => __DIR__ . '/css/responsive-custom.css',
    'js/sb-admin-2.min.js' => __DIR__ . '/js/sb-admin-2.min.js',
    'js/demo/chart-area-demo.js' => __DIR__ . '/js/demo/chart-area-demo.js',
    'js/demo/chart-pie-demo.js' => __DIR__ . '/js/demo/chart-pie-demo.js',
);

foreach ($files_to_check as $name => $path) {
    if (file_exists($path)) {
        $checks["file_$name"] = "✓ $name exists";
    } else {
        $errors["file_$name"] = "✗ $name missing";
    }
}

// Check 4: Database tables
$tables_to_check = array('user', 'wisata', 'komentar', 'pesan', 'produk', 'informasi', 'galeri');
foreach ($tables_to_check as $table) {
    $result = mysqli_query($koneksi, "SHOW TABLES LIKE '$table'");
    if (mysqli_num_rows($result) > 0) {
        $checks["table_$table"] = "✓ Table '$table' exists";
    } else {
        $errors["table_$table"] = "✗ Table '$table' missing";
    }
}

// Check 5: Language file
if (file_exists(__DIR__ . '/../config/language.php')) {
    $checks['language'] = '✓ Language file exists';
} else {
    $errors['language'] = '✗ Language file missing';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Setup Verification</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
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
            border-bottom: 3px solid #4e73df;
            padding-bottom: 10px;
        }
        .checks, .errors {
            margin: 20px 0;
        }
        .check-item {
            padding: 10px;
            margin: 8px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        .check-item.success {
            background: #e8f5e9;
            border-left-color: #4caf50;
            color: #2e7d32;
        }
        .check-item.warning {
            background: #fff3e0;
            border-left-color: #ff9800;
            color: #e65100;
        }
        .check-item.error {
            background: #ffebee;
            border-left-color: #f44336;
            color: #c62828;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 4px;
        }
        .status-ok {
            color: #4caf50;
            font-weight: bold;
        }
        .status-error {
            color: #f44336;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Dashboard Setup Verification</h1>
        
        <div class="checks">
            <h2>✓ Passed Checks</h2>
            <?php foreach ($checks as $check): ?>
            <div class="check-item success"><?php echo htmlspecialchars($check); ?></div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errors)): ?>
        <div class="errors">
            <h2>✗ Failed Checks</h2>
            <?php foreach ($errors as $error): ?>
            <div class="check-item error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="summary">
            <h3>Summary</h3>
            <?php 
            $total = count($checks) + count($errors);
            $passed = count($checks);
            $percentage = ($passed / $total) * 100;
            ?>
            <p>Passed: <span class="status-ok"><?php echo $passed; ?>/<?php echo $total; ?></span></p>
            <p>Success Rate: <span class="status-ok"><?php echo round($percentage, 1); ?>%</span></p>
            
            <?php if (empty($errors)): ?>
            <p style="color: #4caf50; font-size: 18px;">✓ All checks passed! Dashboard is ready to use.</p>
            <p><a href="index.php" style="color: #4e73df; text-decoration: none;">→ Go to Dashboard</a></p>
            <?php else: ?>
            <p style="color: #f44336; font-size: 18px;">✗ Some checks failed. Please fix the issues above.</p>
            <?php endif; ?>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; font-size: 12px;">
            <p>Dashboard Version: 2.0</p>
            <p>Last Check: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
</body>
</html>
