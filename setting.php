<?php
date_default_timezone_set('Asia/Jakarta');

// Ambil config.json
$configFile = __DIR__ . '/config.json';
if (!file_exists($configFile)) {
  file_put_contents($configFile, json_encode(['nama_organisasi' => 'PPK PERENCANAAN & PROGRAM'], JSON_PRETTY_PRINT));
}
$config = json_decode(file_get_contents($configFile), true);

// Simpan perubahan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config['nama_organisasi'] = $_POST['nama_organisasi'];
    $config['tampilan_agenda'] = $_POST['tampilan_agenda'];
    file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
    $message = "✅ Pengaturan berhasil disimpan!";
}

// Untuk menu aktif
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pengaturan | AGENDA PERENCANAAN</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css" crossorigin="anonymous" />
  <style>
    body { font-family: sans-serif; background-color: #f8f9fa; padding: 20px; }
    .card { border-radius: 10px; }
  </style>
</head>
<body class="container">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary rounded mb-4 shadow-sm">
    <div class="container-fluid">
      <a class="navbar-brand fw-bold text-white" href="index.php">
        <i class="fas fa-calendar-alt me-2"></i>Agenda Perencanaan
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">

          <li class="nav-item">
            <a class="nav-link <?= $current == 'index.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="index.php">
              <i class="fas fa-home me-1"></i> Home
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $current == 'data.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="data.php">
              <i class="fas fa-list me-1"></i> Data Agenda
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $current == 'kata.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="kata.php">
              <i class="fas fa-quote-left me-1"></i> Kata Hari Ini
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $current == 'marque.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="marque.php">
              <i class="fas fa-newspaper me-1"></i> Running Text
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $current == 'datatema.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="datatema.php">
              <i class="fas fa-palette me-1"></i> Tema
            </a>
          </li>

          <li class="nav-item">
            <a class="nav-link <?= $current == 'setting.php' ? 'active fw-bold bg-white text-primary rounded px-3' : 'text-white'; ?>" href="setting.php">
              <i class="fas fa-cog me-1"></i> Pengaturan
            </a>
          </li>

        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->

  <!-- Konten Pengaturan -->
  <div class="row mt-3">
    <div class="col-md-6 mx-auto">
      <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
          <h5 class="mb-0"><i class="fas fa-cog me-2"></i> Pengaturan Aplikasi</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?= $message ?></div>
          <?php endif; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Nama Organisasi</label>
              <input type="text" name="nama_organisasi" class="form-control text-uppercase" 
                     value="<?= htmlspecialchars($config['nama_organisasi'] ?? '') ?>" required>
              <div class="form-text text-muted"><i>Nama ini akan tampil di bagian header aplikasi.</i></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Tampilan Agenda</label>
                <div class="btn-group w-100" role="group" aria-label="Tipe Tampilan Agenda">

                  <input type="radio" class="btn-check" name="tampilan_agenda" id="tampilCarousel"
                        value="carousel"
                        autocomplete="off"
                        <?= ($config['tampilan_agenda'] ?? '') === 'carousel' ? 'checked' : '' ?>>
                  <label class="btn btn-outline-primary" for="tampilCarousel">
                    <i class="fas fa-sliders-h me-1"></i> Carousel
                  </label>

                  <input type="radio" class="btn-check" name="tampilan_agenda" id="tampilTabel"
                        value="tabel"
                        autocomplete="off"
                        <?= ($config['tampilan_agenda'] ?? '') === 'tabel' ? 'checked' : '' ?>>
                  <label class="btn btn-outline-primary" for="tampilTabel">
                    <i class="fas fa-table me-1"></i> Tabel
                  </label>

                </div>
              <div class="form-text text-muted mt-1"><i>Pilih cara menampilkan agenda di layar utama.</i></div>
            </div>

            <!-- 🖼️ Preview Gambar -->
            <div id="previewTampilan" class="text-center mt-3">
              <img src="assets/img/<?= ($config['tampilan_agenda'] ?? 'carousel') === 'tabel' ? 'tabel.png' : 'carousel.png' ?>" 
                  alt="Preview Tampilan" 
                  id="previewImage"
                  class="img-fluid border rounded shadow-sm"
                  style="max-height: 250px; transition: all 0.3s ease;">
            </div>


            <div class="d-grid gap-2">
              <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> Simpan
              </button>
              <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
              </a>
            </div>
          </form>


          
        </div>
      </div>
    </div>
  </div>

  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
  const inputOrg = document.getElementById('nama_organisasi');
  inputOrg.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
  });
</script>
<script>
  // Ganti preview otomatis saat radio dipilih
  document.querySelectorAll('input[name="tampilan_agenda"]').forEach((radio) => {
    radio.addEventListener('change', function() {
      const img = document.getElementById('previewImage');
      if (this.value === 'tabel') {
        img.src = 'assets/img/tabel.png';
      } else {
        img.src = 'assets/img/carousel.png';
      }
    });
  });
</script>

</body>
</html>
