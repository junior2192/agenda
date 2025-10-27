<?php
$configFile = __DIR__ . '/../config.json';
$config = json_decode(file_get_contents($configFile), true);
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ROOM DISPLAY - Tema 3</title>

  <!-- Bootstrap & Icons -->
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <script src="assets/jquery.js"></script>

  <style>
    html, body {
      height: 100%;
      margin: 0; padding: 0;
      overflow: hidden;
      font-family: 'Poppins', sans-serif;
      background: radial-gradient(circle at top left, #0d47a1, #1a0033);
      color: #e0e8ff;
    }

    /* Header */
    .header {
      display: flex; justify-content: space-between; align-items: center;
      padding: 20px 40px;
      background: rgba(0, 0, 0, 0.3);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(255,255,255,0.15);
      box-shadow: 0 0 15px rgba(0,0,0,0.3);
    }

    .live-clock {
      font-family: 'Orbitron', sans-serif;
      font-size: 3rem;
      font-weight: 700;
      color: #00ffff;
      text-shadow: 0 0 10px #00eaff;
    }

    .header h2 {
      font-weight: 700;
      color: #ffffff;
      text-shadow: 0 0 10px rgba(0, 255, 255, 0.3);
    }

    /* Agenda Section */
    .agenda-section {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      padding: 25px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.4);
      height: 100%;
    }

    .agenda-title {
      font-size: 1.8rem;
      color: #00eaff;
      font-weight: 600;
      margin-bottom: 20px;
      border-bottom: 2px solid #00eaff;
      padding-bottom: 8px;
    }

    .agenda-card .card-body {
      font-size: 1.4rem;
      color: #000;
    }

   
    /* Date Box */
    .date-box {
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(10px);
      border-radius: 16px;
      padding: 25px;
      text-align: center;
      color: #00eaff;
      box-shadow: 0 0 15px rgba(0,255,255,0.2);
      width: 100%;
    }

    .date-box h1 {
      font-size: 3.8rem;
      font-weight: 700;
      margin-bottom: 10px;
    }

    /* Cuaca */
    #weatherCarousel {
      width: 100%;
      background: rgba(255,255,255,0.1);
      backdrop-filter: blur(8px);
      border-radius: 16px;
      box-shadow: 0 0 10px rgba(0,255,255,0.1);
    }

    /* Running Text */
    .running-text {
      position: absolute;
      bottom: 0;
      width: 100%;
      height: 45px;
      background: linear-gradient(90deg, #00eaff 0%, #5f00ff 100%);
      color: #fff;
      font-weight: 600;
      font-size: 1.2rem;
      display: flex; align-items: center;
      overflow: hidden;
    }

    .running-text marquee {
      width: 100%;
      font-family: 'Orbitron', sans-serif;
      letter-spacing: 1px;
    }

    /* Toast */
    .toast {
      background: rgba(0,0,0,0.6);
      backdrop-filter: blur(8px);
      color: #00eaff;
      border: 1px solid #00eaff;
      border-radius: 12px;
    }

    .toast-header {
      background: #00eaff;
      color: #000;
      font-weight: 600;
    }

    .toast-body {
      font-size: 1rem;
      color: #e0e8ff;
    }

    /* Perbaikan tampilan untuk TV / layar besar */
@media (min-width: 1600px) {
  body {
    zoom: 0.9; /* turunkan sedikit agar proporsional di TV */
  }
}

/* Jika 4K (TV besar), kecilkan lebih jauh */
@media (min-width: 2500px) {
  body {
    zoom: 0.8;
  }
}

  </style>
</head>

<body>
  <!-- Header -->
  <div class="header">
    <div class="live-clock" id="liveClock"></div>
    <div class="text-end">
      <h2 class="mb-0 text-uppercase"><?= htmlspecialchars($config['nama_organisasi'] ?? 'Nama Organisasi') ?></h2>

    </div>
  </div>

  <!-- Main -->
  <div class="container-fluid py-2">
    <div class="row h-100">
      <!-- Agenda -->
      <div class="col-md-8 p-3">
        <div class="agenda-section h-100">
          <h3 class="agenda-title">
            <a href="data.php" class="text-decoration-none text-info">🗓️ Agenda Hari Ini</a>
          </h3>
          <div id="agendaContainer"></div>
        </div>
      </div>

      <!-- Tanggal & Cuaca -->
      <div class="col-md-4 p-3 d-flex flex-column align-items-center justify-content-center">
        <div class="date-box mb-4">
          <h1 id="hari"></h1>
          <div style="height:4px; width:100px; background:#00eaff; margin:10px auto;"></div>
          <h2 id="tanggalLengkap" style="font-size:2rem;"></h2>
        </div>
        <div id="weatherCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
          <div class="carousel-inner" id="weatherInner">
            <div class="carousel-item active">
              <div class="p-4 text-center" style="font-size:1.1rem; color:#00eaff;">
                Memuat cuaca...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast -->
  <div class="position-fixed start-0 p-3" style="bottom: 80px; z-index: 1100">
    <div id="dailyToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
      <div class="toast-header">
        <strong class="me-auto">💬 Kata Hari Ini</strong>
        <small id="toastUser"></small>
        <button type="button" class="btn-close ms-2" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body" id="toastMessage"></div>
    </div>
  </div>

  <!-- Running Text -->
  <div class="running-text">
    <marquee class="pt-3" behavior="scroll" direction="left" scrollamount="5" id="runningMarquee">
      Memuat teks berjalan...
    </marquee>
  </div>

  <!-- Scripts -->
   <script>
(function() {
  const width = window.screen.width;
  const height = window.screen.height;
  const ratio = window.devicePixelRatio || 1;

  let scale = 1;

  // Laptop / Full HD (1920x1080)
  if (width <= 1920) {
    scale = 1;
  }

  // TV / Monitor 2K (2560x1440)
  else if (width <= 2560) {
    scale = 1.25; // perbesar sedikit agar mirip tampilan 1080p
  }

  // TV 4K (3840x2160)
  else if (width >= 3800) {
    scale = 1.6;
  }

  document.body.style.transform = `scale(${scale})`;
  document.body.style.transformOrigin = "top left";
  document.body.style.width = (100 / scale) + "%";
  document.body.style.height = (100 / scale) + "%";

  console.log(`Resolution: ${width}x${height}, DPR: ${ratio}, Scale applied: ${scale}`);
})();
</script>

  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    const TIPE_KONTEN = "<?= htmlspecialchars($config['tipe_konten'] ?? 'slide show') ?>";
  </script>
  <script src="assets/js/agenda.js"></script>
  
</body>
</html>
