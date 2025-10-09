<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>ROOM DISPLAY - Tema 2</title>

  <!-- Bootstrap & Icons -->
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="assets/jquery.js"></script>

  <style>
    html, body {
      height: 100%; margin: 0; padding: 0;
      overflow: hidden; background-color: #f5f8ff;
      font-family: 'Poppins', sans-serif;
      color: #1e293b;
    }

    .header {
      background: linear-gradient(90deg, #007bff 0%, #0dcaf0 100%);
      color: white; padding: 15px 40px;
      display: flex; justify-content: space-between; align-items: center;
    }

    .live-clock {
      font-size: 2.8rem;
      font-weight: 600;
      letter-spacing: 2px;
    }

    .header h2 {
      font-weight: 700;
      text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
    }

    .agenda-section {
      background: white;
      padding: 30px;
      border-radius: 16px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.1);
      height: 100%;
    }

    .agenda-card .card-body {
      font-size: 1.5rem;
      color: #0d6efd;
    }

    .date-box {
      background: #e8f0fe;
      color: #0d47a1;
      text-align: center;
      padding: 25px;
      border-radius: 16px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      width: 100%;
    }

    #weatherCarousel {
      width: 100%;
      background: white;
      border-radius: 16px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      overflow: hidden;
    }

    .running-text {
      position: absolute;
      bottom: 0; width: 100%; height: 45px;
      background: #007bff;
      color: #fff; font-weight: 500;
      display: flex; align-items: center;
      justify-content: flex-start;
      padding: 0 10px;
      font-size: 1.2rem;
    }

    .toast {
      backdrop-filter: blur(5px);
    }

    .toast-header {
      background: #0d6efd; color: white;
    }

    .agenda-title {
      color: #007bff;
      font-weight: 600;
      border-bottom: 3px solid #0dcaf0;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <div class="header">
    <div class="live-clock" id="liveClock"></div>
    <div class="text-end">
      <h2 class="mb-0">PPK PERENCANAAN & PROGRAM</h2>
    </div>
  </div>

  <!-- Main Content -->
  <div class="container-fluid py-4">
    <div class="row h-100">
      <!-- Agenda -->
      <div class="col-md-8 p-3">
        <div class="agenda-section h-100">
          <h3 class="agenda-title"><a href="data.php" class="text-decoration-none">🗓️ Agenda Hari Ini</a></h3>
          <div id="agendaContainer"></div>
        </div>
      </div>

      <!-- Tanggal & Cuaca -->
      <div class="col-md-4 p-3 d-flex flex-column align-items-center justify-content-center">
        <div class="date-box mb-4">
          <h1 id="hari" style="font-size:3.5rem; font-weight:700;"></h1>
          <div style="height:4px; width:100px; background:#007bff; margin:10px auto;"></div>
          <h2 id="tanggalLengkap" style="font-size:2.2rem;"></h2>
        </div>
        <div id="weatherCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="9000">
          <div class="carousel-inner" id="weatherInner">
            <div class="carousel-item active">
              <div class="p-4 text-center" style="font-size:1.1rem; color:#0d6efd;">
                Memuat cuaca...
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Toast Kata Hari Ini -->
  <div class="position-fixed start-0 p-3" style="bottom: 80px; z-index: 1100">
    <div id="dailyToast" class="toast bg-light text-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="10000">
      <div class="toast-header">
        <strong class="me-auto">✨ Kata-kata Hari Ini</strong>
        <small id="toastUser"></small>
        <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
      </div>
      <div class="toast-body fw-semibold fs-6" id="toastMessage"></div>
    </div>
  </div>

  <!-- Running Text -->
  <div class="running-text">
    <marquee behavior="scroll" direction="left" scrollamount="5" id="runningMarquee">
      Memuat teks berjalan...
    </marquee>
  </div>

  <!-- Script -->
  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/agenda.js"></script>
</body>
</html>
