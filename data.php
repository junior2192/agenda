<?php
// Koneksi SQLite
$db = new PDO('sqlite:agenda.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat tabel jika belum ada
$db->exec("CREATE TABLE IF NOT EXISTS agenda (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tanggal TEXT NOT NULL,
    waktu_mulai TEXT NOT NULL,
    waktu_selesai TEXT NOT NULL,
    kegiatan TEXT NOT NULL,
    tempat TEXT,
    disposisi TEXT
)");

// Hapus agenda
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $stmt = $db->prepare("DELETE FROM agenda WHERE id = ?");
  $stmt->execute([$id]);
  header("Location: data.php");
  exit;
}

// Ambil data untuk edit jika ada
$editAgenda = null;
if (isset($_GET['edit'])) {
  $editId = (int)$_GET['edit'];
  $stmt = $db->prepare("SELECT * FROM agenda WHERE id = ?");
  $stmt->execute([$editId]);
  $editAgenda = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Tambah atau Edit agenda
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id            = $_POST['id'] ?? null;
  $tanggal       = $_POST['tanggal'] ?? '';
  $waktu_mulai   = $_POST['waktu_mulai'] ?? '';
  $waktu_selesai = $_POST['waktu_selesai'] ?? '';
  $kegiatan      = $_POST['kegiatan'] ?? '';
  $tempat        = $_POST['tempat'] ?? '';
  $disposisi     = $_POST['disposisi'] ?? '';

  if ($tanggal && $waktu_mulai && $waktu_selesai && $kegiatan) {
    if ($id) {
      $stmt = $db->prepare("UPDATE agenda SET tanggal=?, waktu_mulai=?, waktu_selesai=?, kegiatan=?, tempat=?, disposisi=? WHERE id=?");
      $stmt->execute([$tanggal, $waktu_mulai, $waktu_selesai, $kegiatan, $tempat, $disposisi, $id]);
    } else {
      $stmt = $db->prepare("INSERT INTO agenda (tanggal, waktu_mulai, waktu_selesai, kegiatan, tempat, disposisi)
                            VALUES (?, ?, ?, ?, ?, ?)");
      $stmt->execute([$tanggal, $waktu_mulai, $waktu_selesai, $kegiatan, $tempat, $disposisi]);
    }
    header("Location: data.php");
    exit;
  }
}

$agenda = $db->query("SELECT * FROM agenda ORDER BY tanggal, waktu_mulai")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AGENDA PERENCANAAN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" />
  <style>
    body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
    .section-title { margin-top: 30px; }
    table th, table td { vertical-align: middle; }
  </style>
</head>
<body class="container">
  <div class="mb-2">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Home</a>
  </div>
  <h1 class="section-title text-center">DATA AGENDA</h1>
  <div class="row mt-5">
    <!-- Form Tambah/Edit Agenda -->
    <div class="col-md-4">
      <h5 class="mb-3"><?= $editAgenda ? '✏️ Edit Agenda' : '➕ Tambah Agenda' ?></h5>
      <form method="post" class="card shadow-sm p-3">
        <?php if ($editAgenda): ?>
          <input type="hidden" name="id" value="<?= $editAgenda['id'] ?>">
        <?php endif; ?>
        <div class="mb-3">
          <label for="tanggal" class="form-label">Tanggal</label>
          <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= $editAgenda['tanggal'] ?? date('Y-m-d') ?>" required>
        </div>
        <div class="mb-3 row d-flex align-items-center">
          <label class="form-label">Waktu</label>
          <div class="col-5">
            <input type="time" name="waktu_mulai" class="form-control" value="<?= $editAgenda['waktu_mulai'] ?? '' ?>" required>
          </div>
          <div class="col-1 text-center d-flex align-items-center justify-content-center">-</div>
          <div class="col-5">
  <input list="opsi_waktu_selesai" name="waktu_selesai" class="form-control"
    value="<?= $editAgenda['waktu_selesai'] ?? '' ?>" placeholder="Contoh: 10:00 atau Selesai" required>
  <datalist id="opsi_waktu_selesai">
    <?php
    for ($h = 7; $h <= 18; $h++) {
      foreach ([0, 30] as $m) {
        printf('<option value="%02d:%02d">', $h, $m);
      }
    }
    ?>
    <option value="Selesai">
    <option value="Sampai selesai">
  </datalist>
</div>
        </div>
        <div class="mb-3">
          <label for="kegiatan" class="form-label">Kegiatan</label>
          <input type="text" id="kegiatan" name="kegiatan" class="form-control" value="<?= $editAgenda['kegiatan'] ?? '' ?>" placeholder="Masukkan kegiatan" required>
        </div>
        <div class="mb-3">
          <label for="tempat" class="form-label">Tempat</label>
          <input type="text" id="tempat" name="tempat" class="form-control" value="<?= $editAgenda['tempat'] ?? '' ?>" placeholder="Contoh: Aula">
        </div>
        <div class="mb-3">
          <label for="disposisi" class="form-label">Disposisi</label>
          <input type="text" id="disposisi" name="disposisi" class="form-control" value="<?= $editAgenda['disposisi'] ?? '' ?>" placeholder="Contoh: Sekretaris">
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-success"><?= $editAgenda ? ' Update' : ' Simpan' ?></button>
          <?php if ($editAgenda): ?>
            <a href="data.php" class="btn btn-secondary">Batal</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <!-- Tabel Agenda -->
    <div class="col-md-8">
      <div class="d-flex justify-content-start mb-3">
        <label for="filter-date" class="col-form-label me-2" style="width: 130px;">🔎 Filter Tanggal:</label>
        <div style="flex: 1;">
          <input type="date" id="filter-date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agenda-table">
          <thead class="table-dark text-center">
            <tr>
              <th>No</th>
              <th>Jam</th>
              <th>Kegiatan</th>
              <th>Tempat</th>
              <th>Disposisi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($agenda as $i => $item): ?>
              <tr data-tanggal="<?= htmlspecialchars($item['tanggal']) ?>">
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($item['waktu_mulai']) ?> - <?= htmlspecialchars($item['waktu_selesai']) ?></td>
                <td><?= htmlspecialchars($item['kegiatan']) ?></td>
                <td><?= htmlspecialchars($item['tempat'] ?? '-') ?></td>
                <td><?= htmlspecialchars($item['disposisi'] ?? '-') ?></td>
                <td class="text-center d-flex">
                  <a href="?edit=<?= $item['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                    <i class="fas fa-pencil-alt"></i>
                  </a>
                  <a href="?hapus=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus agenda ini?')" title="Hapus">
                    <i class="fas fa-trash-alt"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($agenda)): ?>
              <tr><td colspan="6" class="text-center text-muted">Belum ada agenda.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('filter-date').addEventListener('change', function () {
      const tanggal = this.value;
      const rows = document.querySelectorAll('#agenda-table tbody tr');
      rows.forEach(row => {
        const tgl = row.getAttribute('data-tanggal');
        row.style.display = (tgl === tanggal) ? '' : 'none';
      });
    });
    document.getElementById('filter-date').dispatchEvent(new Event('change'));
  </script>
</body>
</html>
