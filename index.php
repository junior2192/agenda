<?php
date_default_timezone_set('Asia/Jakarta');
$db = new PDO('sqlite:agenda.db');

$now = new DateTime();
$today = $now->format('Y-m-d');

$stmt = $db->prepare("SELECT * FROM agenda WHERE tanggal = ? ORDER BY waktu_mulai ASC");
$stmt->execute([$today]);
$agendaToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    html, body {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow: hidden;
    }

    body {
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
      background-color: #ffc107;
      color: #000;
      font-size: 1.2rem;
      font-weight: bold;
      padding: 8px 0;
      z-index: 999;
    }

    .running-text marquee {
      padding: 0 10px;
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
      <!-- Bagian Agenda Hari Ini -->
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

      <!-- Bagian Kanan: Hari, Tanggal, Cuaca -->
      <div class="col-md-4 bg-dark text-white p-4 position-relative">
        <div class="tanggal-box mb-4 p-4 rounded shadow-sm">
          <h1 style="font-size: 4rem;"><?= $hari ?></h1>
          <div style="height: 5px; width: 80px; margin: 10px auto; background: #00d9ff;"></div>
          <h2 style="font-size: 2.5rem;"><?= $tanggalLengkap ?></h2>
        </div>

        <div id="weatherCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
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

  <!-- Running Text -->
  <div class="running-text">
    <marquee behavior="scroll" direction="left" scrollamount="5">
      Selamat datang di ruang PPK Perencanaan & Program. Pastikan semua agenda hari ini berjalan lancar. Tetap semangat dan jaga kesehatan!
    </marquee>
  </div>

  <!-- Jam Realtime -->
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


  <!-- Cuaca BMKG Carousel -->
  <script>
$(function(){
  const kode = '32.79.03.1004';
  const url = `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${kode}`;

  $.getJSON(url)
    .done(res => {
      const lokasi = res.lokasi
      console.log(lokasi);
      
      const semuaCuacaNested = res?.data?.[0]?.cuaca ?? [];
      const semuaCuaca = semuaCuacaNested.flat();

      const hariIni = new Date();
      const yyyy = hariIni.getFullYear();
      const mm = String(hariIni.getMonth() + 1).padStart(2, '0');
      const dd = String(hariIni.getDate()).padStart(2, '0');
      const tanggalSekarang = `${yyyy}-${mm}-${dd}`;

      const cuacaHariIni = semuaCuaca.filter(item => {
        const dt = item.local_datetime;
        if (!dt.startsWith(tanggalSekarang)) return false;
        const jam = new Date(dt).getHours();
        return jam >= 0 && jam <= 22;
      });

      if (cuacaHariIni.length === 0) {
        $('#weatherInner').html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded shadow-sm text-center">Cuaca hari ini tidak tersedia.</div></div>`);
        return;
      }

      let slides = '';
      cuacaHariIni.forEach((item, i) => {
        const waktu = new Date(item.local_datetime).toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit'
        });

        slides += `<div class="carousel-item ${i === 0 ? 'active' : ''}">
          <div class="bg-secondary text-white p-4 rounded shadow-sm" style="font-size:1.1rem; min-height: 200px;">
            <div class="text-center mb-3">
              <img src="${item.image}" alt="${item.weather_desc}" style="height:60px; margin-bottom:5px;" />
              <h5 class="mb-1">${item.weather_desc}</h5>
              <div class=""><i class="bi bi-clock me-1"></i><strong>${waktu}</strong></div>
            </div>
            <div class="row text-start">
              <div class="col-6">
                <div class="mb-2"><i class="bi bi-thermometer-half me-1"></i> Suhu: ${item.t}°C</div>
                <div class="mb-2"><i class="bi bi-droplet-fill me-1"></i> Kelembapan: ${item.hu}%</div>
              </div>
              <div class="col-6">
                <div class="mb-2"><i class="bi bi-wind me-1"></i> Angin: ${item.ws} km/j</div>
                <div class="mb-2"><i class="bi bi-compass me-1"></i> Arah: ${item.wd}</div>
              </div>
            </div>
            <div class="mt-3 text-center fw-semibold small">
              <i class="bi bi-geo-alt-fill me-1"></i>
              ${lokasi.desa}, ${lokasi.kecamatan}, ${lokasi.kotkab}, ${lokasi.provinsi}
            </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
