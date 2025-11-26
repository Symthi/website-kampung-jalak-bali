<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isAdmin() ? 'Admin Dashboard' : 'User Dashboard'; ?> | <?php echo get_setting('site_title', 'Kampung Jalak Bali'); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --brown: #4c3d19;
            --dark-green: #354024;
            --muted-green: #889063;
            --tan: #cfbb99;
            --cream: #e5d7c4;
            --text: #2d2a23;
            --muted-text: #6b6458;
            --white: #ffffff;
            --font-heading: "Playfair Display", serif;
            --font-body: "Poppins", sans-serif;
        }

        body {
            background-color: #f8f7f5;
            font-family: var(--font-body);
            color: var(--text);
        }

        /* ============================================ */
        /* DASHBOARD MAIN HEADING */
        /* ============================================ */
        .dashboard-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--tan);
        }

        .dashboard-header h1 {
            font-size: 2.2rem;
            font-family: var(--font-heading);
            color: var(--dark-green);
            font-weight: 700;
            margin: 0;
        }

        .dashboard-header .subtitle {
            font-size: 0.95rem;
            color: var(--muted-text);
            margin-top: 0.3rem;
            font-weight: 500;
        }

        .dashboard-header .subtitle strong {
            color: var(--text);
            font-weight: 600;
        }

        /* ============================================ */
        /* STAT CARDS - DASHBOARD */
        /* ============================================ */
        .stats-container {
            margin-bottom: 1.5rem;
        }

        .dashboard-stat-card {
            background: var(--white);
            border-radius: 12px;
            padding: 1.2rem;
            box-shadow: 0 4px 10px rgba(76, 61, 25, 0.08);
            border-left: 4px solid var(--dark-green);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .dashboard-stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(76, 61, 25, 0.12);
            border-left-color: var(--tan);
        }

        .dashboard-stat-card.wisata {
            border-left-color: var(--dark-green);
        }

        .dashboard-stat-card.komentar {
            border-left-color: var(--muted-green);
        }

        .dashboard-stat-card.pesan {
            border-left-color: var(--tan);
        }

        .dashboard-stat-card.user {
            border-left-color: #889063;
        }

        .dashboard-stat-card.produk {
            border-left-color: var(--dark-green);
        }

        .dashboard-stat-card.informasi {
            border-left-color: var(--muted-green);
        }

        .dashboard-stat-card.galeri {
            border-left-color: var(--tan);
        }

        .dashboard-stat-card.comments {
            border-left-color: var(--dark-green);
        }

        .dashboard-stat-card.messages {
            border-left-color: var(--muted-green);
        }

        .dashboard-stat-card.registered {
            border-left-color: var(--tan);
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--muted-text);
            margin-bottom: 0.5rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-green);
            font-family: var(--font-heading);
            line-height: 1.2;
        }

        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
            text-align: right;
            margin-top: 0.5rem;
        }

        .stat-icon.brown {
            color: var(--brown);
        }

        .stat-icon.dark-green {
            color: var(--dark-green);
        }

        .stat-icon.muted-green {
            color: var(--muted-green);
        }

        .stat-icon.tan {
            color: var(--tan);
        }

        /* Layout khusus untuk 7 kartu admin */
        .stats-row-admin {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -0.5rem;
        }

        .stats-row-admin .col-card {
            padding: 0 0.5rem;
            margin-bottom: 1rem;
            flex: 0 0 25%;
            max-width: 25%;
        }

        /* Container untuk 3 kartu di baris kedua */
        .second-row-container {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .second-row-inner {
            display: flex;
            flex-wrap: wrap;
            width: 75%; /* 3 kartu x 25% = 75% */
            margin: 0 -0.5rem;
        }

        .second-row-inner .col-card {
            padding: 0 0.5rem;
            margin-bottom: 1rem;
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }

        /* ============================================ */
        /* CHART CARDS */
        /* ============================================ */
        .dashboard-chart-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(76, 61, 25, 0.08);
            transition: all 0.3s ease;
            border: 1px solid rgba(207, 187, 153, 0.15);
            margin-bottom: 1.5rem;
            height: 100%;
        }

        .dashboard-chart-card:hover {
            box-shadow: 0 8px 15px rgba(76, 61, 25, 0.12);
        }

        .chart-header {
            background: linear-gradient(135deg, var(--tan), var(--muted-green));
            color: var(--white);
            padding: 1.2rem 1.5rem;
            border-bottom: 2px solid var(--tan);
        }

        .chart-header h6 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            font-family: var(--font-heading);
        }

        .chart-body {
            padding: 1.2rem;
            background: var(--white);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .chart-area {
            width: 100%;
            height: 280px;
            position: relative;
        }

        .chart-pie {
            width: 100%;
            height: 280px;
            position: relative;
            max-width: 280px;
            margin: 0 auto;
        }

        .chart-area canvas,
        .chart-pie canvas {
            max-width: 100% !important;
            height: auto !important;
        }

        .chart-body canvas {
            max-width: 100% !important;
            height: auto !important;
            display: block !important;
        }

        /* ============================================ */
        /* RESPONSIVE ADJUSTMENTS */
        /* ============================================ */
        @media (max-width: 1200px) {
            .dashboard-header h1 {
                font-size: 1.9rem;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
            
            .stat-icon {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 992px) {
            .dashboard-header {
                margin-bottom: 1.2rem;
                padding-bottom: 0.8rem;
            }

            .dashboard-header h1 {
                font-size: 1.7rem;
            }

            .stats-row-admin .col-card,
            .second-row-inner .col-card {
                flex: 0 0 50%;
                max-width: 50%;
            }

            .second-row-container {
                justify-content: flex-start;
            }

            .second-row-inner {
                width: 100%;
            }

            .stat-number {
                font-size: 1.6rem;
            }

            .stat-label {
                font-size: 0.7rem;
            }

            .chart-body {
                min-height: 250px;
            }
            
            .chart-area, .chart-pie {
                height: 240px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-header h1 {
                font-size: 1.5rem;
            }

            .dashboard-header .subtitle {
                font-size: 0.9rem;
            }

            .stats-row-admin .col-card,
            .second-row-inner .col-card {
                flex: 0 0 100%;
                max-width: 100%;
            }

            .second-row-container {
                justify-content: flex-start;
            }

            .second-row-inner {
                width: 100%;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .stat-icon {
                font-size: 1.5rem;
            }

            .chart-header {
                padding: 1rem;
            }

            .chart-header h6 {
                font-size: 1rem;
            }
            
            .chart-body {
                padding: 1rem;
                min-height: 220px;
            }
            
            .chart-area, .chart-pie {
                height: 200px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-header h1 {
                font-size: 1.3rem;
            }

            .dashboard-header .subtitle {
                font-size: 0.85rem;
            }

            .stat-number {
                font-size: 1.4rem;
            }
            
            .dashboard-stat-card {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid p-3 p-md-4">

        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1><?php echo isAdmin() ? 'Dasbor Admin' : 'Dasbor Pengguna'; ?></h1>
            <div class="subtitle">Selamat datang, <strong><?php echo htmlspecialchars($user_nama); ?></strong>!</div>
        </div>

        <!-- Stats Row -->
        <div class="stats-container">
            <?php if (isAdmin()): ?>
                <!-- Admin Stats - 7 cards dengan layout khusus -->
                <div class="stats-row-admin">
                    <!-- Baris Pertama - 4 Kartu -->
                    <!-- Wisata Card -->
                    <div class="col-card">
                        <div class="dashboard-stat-card wisata">
                            <div>
                                <div class="stat-label">Wisata</div>
                                <div class="stat-number"><?php echo $stats['wisata']; ?></div>
                            </div>
                            <div class="stat-icon brown">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Komentar Card -->
                    <div class="col-card">
                        <div class="dashboard-stat-card komentar">
                            <div>
                                <div class="stat-label">Komentar</div>
                                <div class="stat-number"><?php echo $stats['komentar']; ?></div>
                            </div>
                            <div class="stat-icon dark-green">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pesan Card -->
                    <div class="col-card">
                        <div class="dashboard-stat-card pesan">
                            <div>
                                <div class="stat-label">Pesan Baru</div>
                                <div class="stat-number"><?php echo $stats['pesan']; ?></div>
                            </div>
                            <div class="stat-icon tan">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>

                    <!-- User Card -->
                    <div class="col-card">
                        <div class="dashboard-stat-card user">
                            <div>
                                <div class="stat-label">User</div>
                                <div class="stat-number"><?php echo $stats['user']; ?></div>
                            </div>
                            <div class="stat-icon muted-green">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Baris Kedua - 3 Kartu di Tengah -->
                    <div class="second-row-container">
                        <div class="second-row-inner">
                            <!-- Produk Card -->
                            <div class="col-card">
                                <div class="dashboard-stat-card produk">
                                    <div>
                                        <div class="stat-label">Produk</div>
                                        <div class="stat-number"><?php echo $stats['produk']; ?></div>
                                    </div>
                                    <div class="stat-icon brown">
                                        <i class="fas fa-box"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Card -->
                            <div class="col-card">
                                <div class="dashboard-stat-card informasi">
                                    <div>
                                        <div class="stat-label">Informasi</div>
                                        <div class="stat-number"><?php echo $stats['informasi']; ?></div>
                                    </div>
                                    <div class="stat-icon dark-green">
                                        <i class="fas fa-info-circle"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Galeri Card -->
                            <div class="col-card">
                                <div class="dashboard-stat-card galeri">
                                    <div>
                                        <div class="stat-label">Galeri</div>
                                        <div class="stat-number"><?php echo $stats['galeri']; ?></div>
                                    </div>
                                    <div class="stat-icon tan">
                                        <i class="fas fa-images"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- User Stats - 3 cards dengan layout normal -->
                <div class="row">
                    <!-- Comments Card -->
                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="dashboard-stat-card comments">
                            <div>
                                <div class="stat-label">Komentar Saya</div>
                                <div class="stat-number"><?php echo $stats['comments']; ?></div>
                            </div>
                            <div class="stat-icon dark-green">
                                <i class="fas fa-comments"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Messages Card -->
                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="dashboard-stat-card messages">
                            <div>
                                <div class="stat-label">Pesan Saya</div>
                                <div class="stat-number"><?php echo $stats['messages']; ?></div>
                            </div>
                            <div class="stat-icon muted-green">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Card -->
                    <div class="col-xl-4 col-md-4 col-sm-6">
                        <div class="dashboard-stat-card registered">
                            <div>
                                <div class="stat-label">Bergabung</div>
                                <div class="stat-number">
                                    <?php 
                                    if ($stats['registered']) {
                                        $date = new DateTime($stats['registered']);
                                        echo $date->format('d M Y');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="stat-icon tan">
                                <i class="fas fa-calendar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Charts Row -->
        <div class="row mt-3">
            <!-- Area Chart -->
            <div class="col-xl-8 col-lg-7">
                <div class="dashboard-chart-card">
                    <div class="chart-header">
                        <h6><i class="fas fa-chart-line"></i> <?php echo isAdmin() ? 'Ikhtisar Aktivitas' : 'Aktivitas Saya'; ?></h6>
                    </div>
                    <div class="chart-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart -->
            <div class="col-xl-4 col-lg-5">
                <div class="dashboard-chart-card">
                    <div class="chart-header">
                        <h6><i class="fas fa-chart-pie"></i> <?php echo isAdmin() ? 'Kategori Data' : 'Ringkasan'; ?></h6>
                    </div>
                    <div class="chart-body">
                        <div class="chart-pie">
                            <canvas id="myPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script>
        // Chart data from PHP
        const earningsData = <?php echo json_encode(array_values($earnings_data)); ?>;
        const categoryData = <?php echo json_encode($category_data); ?>;
        const isAdminUser = <?php echo isAdmin() ? 'true' : 'false'; ?>;

        // Area Chart
        document.addEventListener('DOMContentLoaded', function() {
            // Area Chart
            const areaCtx = document.getElementById('myAreaChart').getContext('2d');
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            
            const areaChart = new Chart(areaCtx, {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: isAdminUser ? 'Total Aktivitas' : 'Komentar Saya',
                        data: earningsData,
                        backgroundColor: 'rgba(207, 187, 153, 0.2)',
                        borderColor: 'rgba(76, 61, 25, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });

            // Pie Chart
            const pieCtx = document.getElementById('myPieChart').getContext('2d');
            
            const colors = [
                'rgba(76, 61, 25, 0.8)',    // brown
                'rgba(53, 64, 36, 0.8)',    // dark-green
                'rgba(136, 144, 99, 0.8)',  // muted-green
                'rgba(207, 187, 153, 0.8)', // tan
                'rgba(229, 215, 196, 0.8)', // cream
                'rgba(107, 100, 88, 0.8)',  // muted-text
                'rgba(45, 42, 35, 0.8)'     // text
            ];

            const pieChart = new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: categoryData.map(item => item.label),
                    datasets: [{
                        data: categoryData.map(item => item.value),
                        backgroundColor: colors,
                        borderWidth: 1,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 15,
                                usePointStyle: true,
                                boxWidth: 10
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>