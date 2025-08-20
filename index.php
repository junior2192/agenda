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
      <div class="text-end"><h2 class="mb-0">PPK PERENCANAAN & PROGRAM</h2></div>
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
    <marquee behavior="scroll" direction="left" scrollamount="5" id="runningMarquee">
      Memuat teks berjalan...
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

    let pesanIndex = 0;
    let kataList = [];

    function tampilkanToast() {
      if (!kataList.length) return;
      const pesan = kataList[pesanIndex];
      $('#toastMessage').html(`<em>"${pesan.kata_hari_ini}"</em>`);
      $('#toastUser').text(pesan.user);
      new bootstrap.Toast(document.getElementById('dailyToast')).show();
      pesanIndex = (pesanIndex + 1) % kataList.length;
    }

    function loadAgenda() {
      $.getJSON('api/agenda.php', function(data) {
        console.log(data.agenda); // Debug jumlah agenda

        // Update hari & tanggal
        $('#hari').text(data.hari);
        $('#tanggalLengkap').text(data.tanggal);

        // Update running text
        $('#runningMarquee').html(data.running_text);

        // Update agenda
        const agenda = data.agenda;
        let htmlAgenda = '';
        if (agenda.length) {
          htmlAgenda += `
          <div id="agendaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000">
            <div class="carousel-inner" style="min-height: 350px;">`;

            // agenda.forEach((item, i) => {
            //   const active = i === 0 ? 'active' : '';
            //   const now = new Date();
            //   const today = now.toISOString().split('T')[0];

            //   let mulai = new Date(`${item.tanggal}T${item.waktu_mulai}:00`);
            //   let selesai;

            //   // Jika waktu_selesai valid (jam:menit)
            //   if (/^\d{2}:\d{2}$/.test(item.waktu_selesai)) {
            //     selesai = new Date(`${item.tanggal}T${item.waktu_selesai}:00`);
            //   } else {
            //     selesai = new Date(mulai.getTime() + 2 * 60 * 60 * 1000); // default 2 jam
            //   }

            //   const isBerlangsung = item.tanggal === today && now >= mulai && now <= selesai;
            //   const badge = isBerlangsung ? '<span class="badge bg-danger ms-2">Sedang Berlangsung</span>' : '';

            //   htmlAgenda += `
            //     <div class="carousel-item ${active}">
            //       <div class="card h-100 shadow-lg border-${isBerlangsung ? 'primary' : 'light'} agenda-card">
            //         <div class="card-body d-flex flex-column justify-content-between">
            //           <div>
            //             <h2 class="text-primary fw-bold">${item.kegiatan}</h2>
            //             <div class="mb-3">
            //               <i class="bi bi-clock-fill text-warning me-2"></i>
            //               ${item.waktu_mulai} - ${item.waktu_selesai} ${badge}
            //             </div>
            //             <div class="mb-3">
            //               <i class="bi bi-geo-alt-fill text-info me-2"></i>
            //               ${item.tempat || '-'}
            //             </div>
            //             <div class="mb-3">
            //               <i class="bi bi-person-lines-fill text-success me-2"></i>
            //               ${item.disposisi || '-'}
            //             </div>
            //           </div>
            //         </div>
            //       </div>
            //     </div>`;
            // });

            agenda.forEach((item, i) => {
              const active = i === 0 ? 'active' : '';
              const now = new Date();
              const today = now.toISOString().split('T')[0];

              let mulai = new Date(`${item.tanggal}T${item.waktu_mulai}:00`);
              let selesai;

              // Cek apakah waktu_selesai valid jam:menit
              const isWaktuSelesaiValid = /^\d{2}:\d{2}$/.test(item.waktu_selesai);

              if (isWaktuSelesaiValid) {
                selesai = new Date(`${item.tanggal}T${item.waktu_selesai}:00`);
              } else {
                // Jika ada agenda berikutnya di tanggal yang sama
                let waktuAgendaBerikutnya = null;
                for (let j = i + 1; j < agenda.length; j++) {
                  const next = agenda[j];
                  if (next.tanggal === item.tanggal && /^\d{2}:\d{2}$/.test(next.waktu_mulai)) {
                    waktuAgendaBerikutnya = new Date(`${next.tanggal}T${next.waktu_mulai}:00`);
                    break;
                  }
                }
                if (waktuAgendaBerikutnya) {
                  selesai = waktuAgendaBerikutnya;
                } else {
                  selesai = new Date(mulai.getTime() + 2 * 60 * 60 * 1000); // fallback 2 jam
                }
              }

            const isBerlangsung = item.tanggal === today && now >= mulai && now <= selesai;
            const badge = isBerlangsung ? '<span class="badge bg-danger ms-2">Sedang Berlangsung</span>' : '';

            htmlAgenda += `
              <div class="carousel-item ${active}">
                <div class="card h-100 shadow-lg border-${isBerlangsung ? 'primary' : 'light'} agenda-card">
                  <div class="card-body d-flex flex-column justify-content-between">
                    <div>
                      <h2 class="text-primary fw-bold">${item.kegiatan}</h2>
                      <div class="mb-3">
                        <i class="bi bi-clock-fill text-warning me-2"></i>
                        ${item.waktu_mulai} - ${item.waktu_selesai} ${badge}
                      </div>
                      <div class="mb-3">
                        <i class="bi bi-geo-alt-fill text-info me-2"></i>
                        ${item.tempat || '-'}
                      </div>
                      <div class="mb-3">
                        <i class="bi bi-person-lines-fill text-success me-2"></i>
                        ${item.disposisi || '-'}
                      </div>
                    </div>
                  </div>
                </div>
              </div>`;
          });

          htmlAgenda += `
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#agendaCarousel" data-bs-slide="prev">
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#agendaCarousel" data-bs-slide="next">
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Berikutnya</span>
            </button>
          </div>`;
        } else {
          htmlAgenda = '<div class="alert alert-light text-dark">Tidak ada agenda hari ini.</div>';
          htmlAgenda += '<a href="data.php" class="btn btn-success btn-lg mt-3">Tambah Agenda</a>';
        }

        $('#agendaContainer').html(htmlAgenda);

        const newCarousel = document.querySelector('#agendaCarousel');
        if (newCarousel) {
          new bootstrap.Carousel(newCarousel, {
            interval: 10000,
            ride: 'carousel'
          });
        }

        // Update kata harian
        kataList = data.kata || [];
      });
    }

    // Load awal + interval 1 menit
    loadAgenda();
    setInterval(loadAgenda, 60000);

    // Toast interval 20 detik
    setTimeout(tampilkanToast, 3000);
    setInterval(tampilkanToast, 15000);

  // Load cuaca hanya sekali saat awal
  $(function () {
    const kode = '32.79.03.1004';
    const url = `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${kode}`;
    $.getJSON(url).done(res => {
      const lokasi = res.lokasi;
      const semuaCuaca = res?.data?.[0]?.cuaca?.flat() ?? [];
      const hariIni = new Date();
      const yyyy = hariIni.getFullYear();
      const mm = String(hariIni.getMonth() + 1).padStart(2, '0');
      const dd = String(hariIni.getDate()).padStart(2, '0');
      const tanggalSekarang = `${yyyy}-${mm}-${dd}`;
      const cuacaHariIni = semuaCuaca.filter(item => item.local_datetime.startsWith(tanggalSekarang));
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
              <div class="col-6">
                <div class="mb-2"><i class="bi bi-thermometer-half me-1"></i> Suhu: ${item.t} °C</div>
                <div class="mb-2"><i class="bi bi-droplet-fill me-1"></i> Kelembapan: ${item.hu} %</div>
              </div>
              <div class="col-6">
                <div class="mb-2"><i class="bi bi-wind me-1"></i> Angin: ${item.ws} km/j</div>
                <div class="mb-2"><i class="bi bi-compass me-1"></i> Arah: ${item.wd}</div>
              </div>
            </div>
            <div class="mt-3 text-center fw-semibold small">
              <i class="bi bi-geo-alt-fill me-1"></i>${lokasi.desa}, ${lokasi.kecamatan}, ${lokasi.kotkab}, ${lokasi.provinsi}
            </div>
          </div>
        </div>`;
      });
      $('#weatherInner').html(slides);
    }).fail(() => {
      $('#weatherInner').html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded text-center">Gagal memuat cuaca.</div></div>`);
    });
  });
</script>

</body>
</html>
