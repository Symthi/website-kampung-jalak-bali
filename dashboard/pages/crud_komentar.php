<?php
// Hanya untuk menampilkan data - proses sudah dihandle di index.php

// Pagination
$per_page = 5;
$page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
$offset = ($page - 1) * $per_page;

$total_q = mysqli_query($koneksi, "SELECT COUNT(*) as cnt FROM komentar");
$total_komentar_all = mysqli_fetch_assoc($total_q)['cnt'];

$query = "SELECT k.*, u.nama as nama_user, u.email, w.judul as judul_wisata 
          FROM komentar k 
          JOIN user u ON k.id_user = u.id_user 
          JOIN wisata w ON k.id_wisata = w.id_wisata 
          ORDER BY k.tanggal DESC
          LIMIT $per_page OFFSET $offset";
$result = mysqli_query($koneksi, $query);
$komentar_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
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
</style>

<h2 class="section-title">
    <i class="fa fa-comments"></i> <?php echo t('manage_comments') ?: 'Kelola Komentar'; ?>
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
<div class="stat-box" style="max-width: 300px;">
    <div class="stat-label"><i class="fa fa-chart-bar"></i> Total Komentar</div>
    <div class="stat-value"><?php echo $total_komentar_all; ?></div>
</div>

<!-- Daftar Komentar -->
<div class="crud-list shadow mb-4">
    <div class="list-title">
        <i class="fa fa-list"></i> Daftar Komentar
    </div>
    <div class="card-body">
        <?php if (empty($komentar_data)): ?>
            <p class="text-muted">Tidak ada komentar.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover crud-table">
                    <thead class="bg-light">
                        <tr>
                            <th width="40">No</th>
                            <th width="150">User</th>
                            <th width="150">Wisata</th>
                            <th>Komentar</th>
                            <th width="100">Tanggal</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($komentar_data as $index => $komentar): ?>
                        <tr>
                            <td><?php echo $offset + $index + 1; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($komentar['nama_user']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($komentar['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($komentar['judul_wisata']); ?></td>
                            <td>
                                <p class="mb-0"><?php echo substr(htmlspecialchars($komentar['isi']), 0, 100); ?>...</p>
                            </td>
                            <td><?php echo date('d M Y H:i', strtotime($komentar['tanggal'])); ?></td>
                            <td>
                                <a href="?page=komentar&hapus=<?php echo $komentar['id_komentar']; ?>" 
                                   class="btn btn-danger btn-sm" 
                                   onclick="return confirm('Yakin hapus komentar ini?')">
                                    <i class="fa fa-trash"></i> Hapus
                                </a>
                                <a href="<?php echo $base; ?>/detail_wisata.php?id=<?php echo $komentar['id_wisata']; ?>" 
                                   class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fa fa-eye"></i> Lihat
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php 
            $total_pages = (int)ceil($total_komentar_all / $per_page);
            if ($total_pages > 1): ?>
            <nav aria-label="Pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <?php if ($p == $page): ?>
                            <li class="page-item active"><span class="page-link"><?php echo $p; ?></span></li>
                        <?php else: ?>
                            <li class="page-item"><a class="page-link" href="?page=komentar&p=<?php echo $p; ?>"><?php echo $p; ?></a></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>