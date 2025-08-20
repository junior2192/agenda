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

// Ambil semua tanggal unik yang ada agenda
$agendaDates = array_values(array_unique(array_column($agenda, 'tanggal')));
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>AGENDA PERENCANAAN</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css" crossorigin="anonymous" />
  <!-- Flatpickr -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
    .section-title { margin-top: 30px; }
    table th, table td { vertical-align: middle; }
    /* Warna hijau untuk tanggal dengan agenda */
    .flatpickr-day.has-agenda {
      background: green !important;
      color: white !important;
      border-radius: 50% !important;
    }
  </style>
</head>
<body class="container">
  <div class="mb-2">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Home</a>
    <a href="kata.php" class="btn btn-outline-secondary btn-sm">Kata-kata hari ini</a>
    <a href="marque.php" class="btn btn-outline-secondary btn-sm">Running Text</a>
  </div>
  <h1 class="section-title text-center">DATA AGENDA</h1>
  <div class="row mt-2">
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
              <?php for ($h = 7; $h <= 18; $h++): foreach ([0, 30] as $m): ?>
                <option value="<?= sprintf('%02d:%02d', $h, $m) ?>">
              <?php endforeach; endfor; ?>
              <option value="Selesai">
              <option value="Sampai selesai">
            </datalist>
          </div>
        </div>
        <div class="mb-3">
          <label for="kegiatan" class="form-label">Agenda</label>
          <textarea name="kegiatan" id="kegiatan" class="form-control" rows="4" placeholder="Masukkan Agenda" required><?= $editAgenda['kegiatan'] ?? '' ?></textarea>
        </div>

        <div class="mb-3">
          <label for="tempat" class="form-label">Tempat</label>
          <textarea name="tempat" id="tempat" class="form-control" rows="2" placeholder="Contoh: Aula"><?= $editAgenda['tempat'] ?? '' ?></textarea>
        </div>

        <div class="mb-3">
          <label for="disposisi" class="form-label">Disposisi</label>
          <input type="text" id="disposisi" name="disposisi" class="form-control" value="<?= $editAgenda['disposisi'] ?? '' ?>" placeholder="Contoh: Sekretaris">
        </div>
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary"> <i class="fas fa-save"></i> <?= $editAgenda ? ' Update' : ' Simpan' ?></button>
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
          <input type="text" id="filter-date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-striped" id="agenda-table">
          <thead class="table-dark text-center">
            <tr>
              <th>No</th>
              <th>Jam</th>
              <th>Agenda</th>
              <th>Tempat</th>
              <th>Disposisi</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $lastTanggal = null;
              $counter = 0;
              foreach ($agenda as $item):
                if ($item['tanggal'] !== $lastTanggal) {
                  $counter = 1;
                  $lastTanggal = $item['tanggal'];
                } else {
                  $counter++;
                }
            ?>
              <tr data-tanggal="<?= htmlspecialchars($item['tanggal']) ?>">
                <td class="text-center"><?= $counter ?></td>
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

  <!-- Script -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
  const agendaDates = <?= json_encode($agendaDates) ?>; // masih array Y-m-d

  flatpickr("#filter-date", {
    altInput: true,
    altFormat: "d-m-Y",   // yang ditampilkan ke user
    dateFormat: "Y-m-d",  // format sebenarnya (hidden value)
    defaultDate: "<?= date('Y-m-d') ?>",

    onDayCreate: function(dObj, dStr, fp, dayElem) {
      const date = fp.formatDate(dayElem.dateObj, "Y-m-d"); // cek tetap pakai Y-m-d
      if (agendaDates.includes(date)) {
        dayElem.classList.add("has-agenda");
      }
    },
    onChange: function(selectedDates, dateStr, instance) {
      filterTable(dateStr); // dateStr masih dalam format Y-m-d
    }
  });

  function filterTable(tanggal) {
    const rows = document.querySelectorAll('#agenda-table tbody tr[data-tanggal]');
    let found = false;
    rows.forEach(row => {
      const tgl = row.getAttribute('data-tanggal');
      if (tgl === tanggal) {
        row.style.display = '';
        found = true;
      } else {
        row.style.display = 'none';
      }
    });

    let noDataRow = document.getElementById('no-agenda-row');
    if (noDataRow) noDataRow.remove();

    if (!found) {
      const tbody = document.querySelector('#agenda-table tbody');
      noDataRow = document.createElement('tr');
      noDataRow.id = 'no-agenda-row';
      noDataRow.innerHTML = `<td colspan="6" class="text-center text-muted">Tidak ada agenda pada tanggal ini.</td>`;
      tbody.appendChild(noDataRow);
    }
  }

  // Jalankan filter awal
  filterTable("<?= date('Y-m-d') ?>");
  document.querySelector("#filter-date").dispatchEvent(new Event("change"));
</script>


</body>
</html>
