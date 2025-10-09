<?php
$configFile = __DIR__ . '/config.json';

// Buat config.json jika belum ada
if (!file_exists($configFile)) {
    file_put_contents($configFile, json_encode(['tema_aktif' => 'tema1'], JSON_PRETTY_PRINT));
}

// Ambil tema aktif
$config = json_decode(file_get_contents($configFile), true);
$temaAktif = $config['tema_aktif'] ?? 'tema1';

// Kalau form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $temaBaru = $_POST['tema'] ?? 'tema1';
    $config['tema_aktif'] = $temaBaru;
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    header('Location: datatema.php?sukses=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pengaturan Tema</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css" crossorigin="anonymous" />
  <style>
    body { padding: 20px; background-color: #f8f9fa; }
    .section-title { margin-bottom: 30px; }
    .card { border-radius: 10px; }
    .preview-box {
      text-align: center;
      border: 2px solid #dee2e6;
      border-radius: 10px;
      padding: 10px;
      background: #fff;
      transition: all 0.3s;
      cursor: pointer;
    }
    .preview-box img {
      width: 100%;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    .preview-box:hover {
      transform: scale(1.03);
      border-color: #0d6efd;
    }
    .selected {
      border-color: #0d6efd;
      box-shadow: 0 0 20px rgba(13,110,253,0.5);
    }
    .tema-info {
      border-left: 6px solid #0d6efd;
      background: #e9f3ff;
      padding: 15px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 25px;
    }
    .tema-info i {
      font-size: 1.8rem;
      color: #0d6efd;
    }
  </style>
</head>
<body class="container">
  <div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Home</a>
    <a href="data.php" class="btn btn-outline-secondary btn-sm">Data Agenda</a>
    <a href="marque.php" class="btn btn-outline-secondary btn-sm">Running Text</a>
    <a href="kata.php" class="btn btn-outline-secondary btn-sm">Kata Hari Ini</a>
  </div>

  <h1 class="section-title text-center">🎨 Pengaturan Tema Room Display</h1>

  <?php if (isset($_GET['sukses'])): ?>
    <div class="alert alert-success text-center">Tema berhasil diganti!</div>
  <?php endif; ?>

  <form method="post">
    <div class="row g-4 justify-content-center">
      <?php
      $temaList = [
          'tema1' => 'Kuning Hitam',
          'tema2' => 'Biru Putih',
          'tema3' => 'Neon Futuristik'
      ];
      foreach ($temaList as $key => $nama): ?>
        <div class="col-md-4 text-center">
            <div class="fw-bold mb-2 text-uppercase">🎨 <?= ucfirst($nama) ?></div>
                <label class="preview-box d-block <?= $temaAktif == $key ? 'selected' : '' ?>">
                <input type="radio" name="tema" value="<?= $key ?>" class="form-check-input me-2" <?= $temaAktif == $key ? 'checked' : '' ?> hidden>
                <img src="assets/img/<?= $key ?>.png" alt="<?= $nama ?>">
            </label>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i> Simpan Tema
      </button>
      <a href="index.php" class="btn btn-secondary">
        <i class="fas fa-eye"></i> Lihat Tampilan
      </a>
    </div>
  </form>

  <!-- Info tema aktif -->
  <div class="tema-info mt-4">
    <i class="fas fa-palette"></i>
    <div>
      <div><strong>Tema saat ini:</strong> <?= strtoupper($temaAktif) ?></div>
      <small>
        <?php
        $deskripsi = [
          'tema1' => 'Warna kuning dan hitam — kontras dan energik, cocok untuk tampilan yang mencolok.',
          'tema2' => 'Kombinasi biru dan putih — bersih, profesional, dan menenangkan.',
          'tema3' => 'Gaya neon futuristik — tampilan modern dan berani dengan efek bercahaya.'
        ];
        echo $deskripsi[$temaAktif];
        ?>
      </small>
    </div>
  </div>

  <script>
    // Ubah highlight saat klik gambar
    document.querySelectorAll('.preview-box').forEach(box => {
      box.addEventListener('click', () => {
        document.querySelectorAll('.preview-box').forEach(b => b.classList.remove('selected'));
        box.classList.add('selected');
        box.querySelector('input').checked = true;
      });
    });
  </script>
</body>
</html>
