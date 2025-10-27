<?php
$configFile = __DIR__ . '/../config.json';
$config = json_decode(file_get_contents($configFile), true);
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ROOM DISPLAY</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script src="assets/jquery.js"></script>
  <style>
    html, body {
      height: 100%; margin: 0; padding: 0; overflow: hidden;
    }
    body {
      font-family: 'Inter', sans-serif;
      background: #1a1a1a; color: white;
      display: flex; flex-direction: column; position: relative;
    }
    .live-clock { font-size: 2.5rem; font-weight: bold; }
    .tanggal-box { text-align: center; background: #2d2d2d; }
    .agenda-card .card-body { font-size: 1.6rem; }
    .running-text {
      position: absolute; bottom: 0; width: 100%; height: 40px;
      background-color: #ffc107; color: #000;
      font-size: 1.3rem; font-weight: bold; z-index: 999;
      white-space: nowrap; display: flex; align-items: center;
      justify-content: flex-start; padding: 0 10px;
    }
    .running-text marquee { width: 100%; padding-top: 10px; }
  </style>
</head>
<body>
  <div class="container-fluid py-3 bg-dark text-white">
    <div class="d-flex justify-content-between align-items-center">
      <div class="live-clock" id="liveClock"></div>
      <div class="text-end">
       <h2 class="mb-0 text-uppercase"><?= htmlspecialchars($config['nama_organisasi'] ?? 'Nama Organisasi') ?></h2>
      </div>
    </div>
  </div>

  <div class="container-fluid flex-fill">
    <div class="row h-100">
      <!-- Agenda Hari Ini -->
      <div class="col-md-8 bg-secondary-subtle p-4">
        <h3 class="mb-4 text-dark"><a href="data.php">🗓️</a> Agenda Hari Ini</h3>
        <div id="agendaContainer"></div>
      </div>

      <!-- Tanggal & Cuaca -->
      <div class="col-md-4 bg-dark text-white p-4 d-flex flex-column justify-content-center align-items-center">
        <div class="tanggal-box mb-4 p-4 rounded shadow-sm w-100">
          <h1 style="font-size: 4rem;" id="hari"></h1>
          <div style="height: 5px; width: 80px; margin: 10px auto; background: #00d9ff;"></div>
          <h2 style="font-size: 2.5rem;" id="tanggalLengkap"></h2>
        </div>
        <div id="weatherCarousel" class="carousel slide w-100" data-bs-ride="carousel" data-bs-interval="8000">
          <div class="carousel-inner" id="weatherInner">
            <div class="carousel-item active">
              <div class="bg-secondary text-white p-4 rounded shadow-sm text-center" style="font-size:1.2rem;">
                Memuat cuaca...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Kata-kata Hari Ini -->
  <div class="position-fixed start-0 p-3" style="bottom: 80px; z-index: 1100">
    <div id="dailyToast" class="toast text-dark bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
      <div class="toast-header bg-success text-white">
        <strong class="me-auto fs-6">✨ Kata-kata Hari Ini</strong>
        <small id="toastUser" class="fs-6"></small>
        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body fw-semibold text-white fs-6" id="toastMessage"></div>
    </div>
  </div>

  <!-- Running Text -->
  <div class="running-text">
    <marquee class="pt-3" behavior="scroll" direction="left" scrollamount="5" id="runningMarquee">
      Memuat teks berjalan...
    </marquee>
  </div>

  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    const TIPE_KONTEN = "<?= htmlspecialchars($config['tipe_konten'] ?? 'slide show') ?>";
  </script>
  <script src="assets/js/agenda.js"></script>
</body>
</html>