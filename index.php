<?php
date_default_timezone_set('Asia/Jakarta');
$db = new PDO('sqlite:agenda.db');

$now = new DateTime();
$today = $now->format('Y-m-d');

$stmt = $db->prepare("SELECT * FROM agenda WHERE tanggal = ? ORDER BY waktu_mulai ASC");
$stmt->execute([$today]);
$agendaToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil kata-kata dari database
$stmtKata = $db->query("SELECT kata_hari_ini, user FROM kata ORDER BY RANDOM()");
$kataList = $stmtKata->fetchAll(PDO::FETCH_ASSOC);

// Ambil 1 teks marquee acak (atau yang terbaru)
$stmt = $db->query("SELECT running_text FROM marque ORDER BY id DESC LIMIT 1");
$marqueeTextRow = $stmt->fetch(PDO::FETCH_ASSOC);
$marqueeText = implode(' &nbsp; • &nbsp; ', $marqueeTextRow);

$hariIndonesia = [
  'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
  'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];
$hari = $hariIndonesia[$now->format('l')];
$tanggalLengkap = $now->format('d-m-Y');
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
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #1a1a1a;
      color: white;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .live-clock {
      font-size: 2.5rem;
      font-weight: bold;
    }

    .tanggal-box {
      text-align: center;
      background: #2d2d2d;
    }

    .agenda-card .card-body {
      font-size: 1.6rem;
    }

    .running-text {
      position: absolute;
      bottom: 0;
      width: 100%;
      height: 40px; /* Tinggi tetap */
      background-color: #ffc107;
      color: #000;
      font-size: 1.3rem;
      font-weight: bold;
      z-index: 999;
      white-space: nowrap;

      /* Flexbox untuk posisi tengah vertikal */
      display: flex;
      align-items: center;
      justify-content: flex-start;
      padding: 0 10px;
    }

    .running-text marquee {
      width: 100%;
      padding-top: 10px;
    }

  </style>
</head>
<body>
  <div class="container-fluid py-3 bg-dark text-white">
    <div class="d-flex justify-content-between align-items-center">
      <div class="live-clock" id="liveClock"></div>
      <div class="text-end">
        <h2 class="mb-0">PPK PERENCANAAN & PROGRAM</h2>
      </div>
    </div>
  </div>

  <div class="container-fluid flex-fill">
    <div class="row h-100">
      <!-- Agenda Hari Ini -->
      <div class="col-md-8 bg-secondary-subtle p-4">
        <h3 class="mb-4 text-dark"><a href="data.php">🗓️</a> Agenda Hari Ini</h3>
        <?php if ($agendaToday): ?>
        <div id="agendaCarousel" class="carousel slide h-100" data-bs-ride="carousel" data-bs-interval="10000">
          <div class="carousel-inner h-100">
            <?php foreach ($agendaToday as $i => $item):
              $start = DateTime::createFromFormat('H:i', $item['waktu_mulai']);
              $end = DateTime::createFromFormat('H:i', $item['waktu_selesai']);
              $isActive = ($now >= $start && $now <= $end);
            ?>
            <div class="carousel-item <?= $i == 0 ? 'active' : '' ?>">
              <div class="card h-100 shadow-lg border-<?= $isActive ? 'primary' : 'light' ?> agenda-card">
                <div class="card-body d-flex flex-column justify-content-between">
                  <div>
                    <h2 class="text-primary fw-bold"><?= htmlspecialchars($item['kegiatan']) ?></h2>
                    <div class="mb-3">
                      <i class="bi bi-clock-fill text-warning me-2"></i>
                      <?= htmlspecialchars($item['waktu_mulai']) ?> - <?= htmlspecialchars($item['waktu_selesai']) ?>
                      <?php if ($isActive): ?>
                        <span class="badge bg-danger ms-2">Sedang Berlangsung</span>
                      <?php endif; ?>
                    </div>
                    <div class="mb-3">
                      <i class="bi bi-geo-alt-fill text-info me-2"></i>
                      <?= htmlspecialchars($item['tempat'] ?: '-') ?>
                    </div>
                    <div class="mb-3">
                      <i class="bi bi-person-lines-fill text-success me-2"></i>
                      <?= htmlspecialchars($item['disposisi'] ?: '-') ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php else: ?>
        <div class="alert alert-light text-dark">Tidak ada agenda hari ini.</div>
        <a href="data.php" class="btn btn-success btn-lg mt-3">Tambah Agenda</a>
        <?php endif; ?>
      </div>

      <!-- Tanggal & Cuaca -->
      <div class="col-md-4 bg-dark text-white p-4 d-flex flex-column justify-content-center align-items-center">

        <div class="tanggal-box mb-4 p-4 rounded shadow-sm w-100">
          <h1 style="font-size: 4rem;"><?= $hari ?></h1>
          <div style="height: 5px; width: 80px; margin: 10px auto; background: #00d9ff;"></div>
          <h2 style="font-size: 2.5rem;"><?= $tanggalLengkap ?></h2>
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

  <!-- Toast -->
  <div class="position-fixed start-0 p-3" style="bottom: 80px; z-index: 1100">
    <div id="dailyToast" class="toast text-dark bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="8000">
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
    <marquee behavior="scroll" direction="left" scrollamount="5">
       <!-- Selamat datang di ruang PPK Perencanaan & Program. Pastikan semua agenda hari ini berjalan lancar. Tetap semangat dan jaga kesehatan, <span style="color: red; font-weight: bold">ADIOS FORMOSA EL CONTOLE!!!</span> -->
       <?= $marqueeText ?: 'Selamat datang di ruang PPK Perencanaan & Program...' ?>
    </marquee>
  </div>

  <script src="assets/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script>
    function updateLiveClock() {
      const now = new Date();
      const jam = String(now.getHours()).padStart(2, '0');
      const menit = String(now.getMinutes()).padStart(2, '0');
      const detik = String(now.getSeconds()).padStart(2, '0');
      document.getElementById('liveClock').textContent = `${jam}:${menit}:${detik}`;
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();
  </script>

  <!-- Cuaca -->
  <script>
    $(function(){
      const kode = '32.79.03.1004';
      const url = `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${kode}`;

      $.getJSON(url)
        .done(res => {
          const lokasi = res.lokasi;
          const semuaCuaca = res?.data?.[0]?.cuaca?.flat() ?? [];
          const hariIni = new Date();
          const yyyy = hariIni.getFullYear();
          const mm = String(hariIni.getMonth() + 1).padStart(2, '0');
          const dd = String(hariIni.getDate()).padStart(2, '0');
          const tanggalSekarang = `${yyyy}-${mm}-${dd}`;

          const cuacaHariIni = semuaCuaca.filter(item => {
            const dt = item.local_datetime;
            return dt.startsWith(tanggalSekarang);
          });

          if (!cuacaHariIni.length) {
            $('#weatherInner').html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded shadow-sm text-center">Cuaca hari ini tidak tersedia.</div></div>`);
            return;
          }

          let slides = '';
          cuacaHariIni.forEach((item, i) => {
            const waktu = new Date(item.local_datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            slides += `<div class="carousel-item ${i === 0 ? 'active' : ''}">
              <div class="bg-secondary text-white p-4 rounded shadow-sm" style="font-size:1.1rem; min-height: 200px;">
                <div class="text-center mb-5">
                  <img src="${item.image}" alt="${item.weather_desc}" style="height:60px; margin-bottom:5px;" />
                  <h5 class="mb-3">${item.weather_desc}</h5>
                  <div><i class="bi bi-clock me-1"></i><strong>${waktu}</strong></div>
                </div>
                <div class="row text-start">
                  <div class="col-6"><div class="mb-2"><i class="bi bi-thermometer-half me-1"></i> Suhu: ${item.t}°C</div><div class="mb-2"><i class="bi bi-droplet-fill me-1"></i> Kelembapan: ${item.hu}%</div></div>
                  <div class="col-6"><div class="mb-2"><i class="bi bi-wind me-1"></i> Angin: ${item.ws} km/j</div><div class="mb-2"><i class="bi bi-compass me-1"></i> Arah: ${item.wd}</div></div>
                </div>
                <div class="mt-3 text-center fw-semibold small"><i class="bi bi-geo-alt-fill me-1"></i>${lokasi.desa}, ${lokasi.kecamatan}, ${lokasi.kotkab}, ${lokasi.provinsi}</div>
              </div>
            </div>`;
          });

          $('#weatherInner').html(slides);
        })
        .fail(() => {
          $('#weatherInner').html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded text-center">Gagal memuat cuaca.</div></div>`);
        });
    });
  </script>

<!-- Toast Message -->
<script>
   const pesanHarian = <?= json_encode($kataList, JSON_UNESCAPED_UNICODE) ?>;
    let pesanIndex = 0;
    const toast = new bootstrap.Toast(document.getElementById('dailyToast'));

    function tampilkanToast() {
      if (pesanHarian.length === 0) return;
      const pesan = pesanHarian[pesanIndex];
      document.getElementById('toastMessage').innerHTML = `<em>"${pesan.kata_hari_ini}"</em>`;
      document.getElementById('toastUser').textContent = pesan.user;
      toast.show();
      pesanIndex = (pesanIndex + 1) % pesanHarian.length;
    }

    setTimeout(tampilkanToast, 3000);
    setInterval(tampilkanToast, 20000);

    function refreshPage(){
      location.reload();
    }

    setInterval(refreshPage, 60000);
  </script>
</body>
</html>
