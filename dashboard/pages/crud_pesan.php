<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;
$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM pesan");
$total_pesan_all = mysqli_fetch_assoc($total_q)['cnt'];
$query = "SELECT * FROM pesan ORDER BY tanggal DESC LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$pesan_data = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Hitung statistik
$belum_dibaca = 0;
$sudah_dibaca = 0;

foreach ($pesan_data as $pesan) {
    if (!$pesan['dibaca']) {
        $belum_dibaca++;
    } else {
        $sudah_dibaca++;
    }
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

  .stat-box.danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
  }

  .stat-box.success {
    background: linear-gradient(135deg, #28a745, #20c997);
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

  .row-unread {
    background-color: var(--cream);
  }
</style>

<h2 class="section-title">
    <i class="fa fa-envelope"></i> <?php echo t('manage_messages') ?: 'Kelola Pesan'; ?>
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
            <div class="stat-label"><i class="fa fa-envelope"></i> Total Pesan</div>
            <div class="stat-value"><?php echo $total_pesan_all; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box danger">
            <div class="stat-label"><i class="fa fa-bell"></i> Belum Dibaca</div>
            <div class="stat-value"><?php echo $belum_dibaca; ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box success">
            <div class="stat-label"><i class="fa fa-check"></i> Sudah Dibaca</div>
            <div class="stat-value"><?php echo $sudah_dibaca; ?></div>
        </div>
    </div>
</div>

<!-- Daftar Pesan -->
<div class="crud-list shadow mb-4">
    <div class="list-title">
        <i class="fa fa-list"></i> Daftar Pesan Kontak
    </div>
    <div class="card-body">
        <?php if (empty($pesan_data)): ?>
            <p class="text-muted">Tidak ada pesan.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover crud-table">
                    <thead class="bg-light">
                        <tr>
                            <th width="40">No</th>
                            <th width="150">Pengirim</th>
                            <th width="120">Subjek</th>
                            <th>Pesan</th>
                            <th width="100">Tanggal</th>
                            <th width="80">Status</th>
                            <th width="200">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pesan_data as $index => $pesan): ?>
                        <tr class="<?php echo !$pesan['dibaca'] ? 'row-unread' : ''; ?>">
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($pesan['nama']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($pesan['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($pesan['subjek']); ?></td>
                            <td>
                                <p class="mb-0"><?php echo substr(htmlspecialchars($pesan['isi']), 0, 80); ?>...</p>
                            </td>
                            <td><?php echo date('d M Y H:i', strtotime($pesan['tanggal'])); ?></td>
                            <td>
                                <?php if (!$pesan['dibaca']): ?>
                                    <span class="badge badge-danger">
                                        <i class="fa fa-envelope"></i> Baru
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success">
                                        <i class="fa fa-check"></i> Dibaca
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$pesan['dibaca']): ?>
                                    <a href="?page=pesan&baca=<?php echo $pesan['id_pesan']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fa fa-envelope-open"></i> Tandai Baca
                                    </a>
                                <?php else: ?>
                                    <span class="btn btn-secondary btn-sm disabled">
                                        <i class="fa fa-check"></i> Sudah Dibaca
                                    </span>
                                <?php endif; ?>
                                <a href="?page=pesan&hapus=<?php echo $pesan['id_pesan']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus pesan ini?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $total_pages = (int)ceil($total_pesan_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=pesan&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>