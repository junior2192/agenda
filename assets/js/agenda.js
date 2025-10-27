// ===============================
// 🕒 JAM REAL-TIME
// ===============================
function updateLiveClock() {
  const now = new Date();
  const jam = String(now.getHours()).padStart(2, "0");
  const menit = String(now.getMinutes()).padStart(2, "0");
  const detik = String(now.getSeconds()).padStart(2, "0");
  document.getElementById("liveClock").textContent = `${jam}:${menit}:${detik}`;
}
setInterval(updateLiveClock, 1000);
updateLiveClock();

// ===============================
// 💬 TOAST KATA HARIAN
// ===============================
let pesanIndex = 0;
let kataList = [];

function tampilkanToast() {
  if (!kataList.length) return;
  const pesan = kataList[pesanIndex];
  $("#toastMessage").html(`<em>"${pesan.kata_hari_ini}"</em>`);
  $("#toastUser").text(pesan.user);
  new bootstrap.Toast(document.getElementById("dailyToast")).show();
  pesanIndex = (pesanIndex + 1) % kataList.length;
}

// ===============================
// 📅 AGENDA HARI INI
// ===============================
function loadAgenda() {
  $.getJSON("api/agenda.php", function (data) {
    //console.log("Agenda:", data.agenda);

    $("#hari").text(data.hari);
    $("#tanggalLengkap").text(data.tanggal);
    $("#runningMarquee").html(data.running_text);

    const agenda = data.agenda;
    const tampilan = data.tampilan_agenda || "carousel";
    let htmlAgenda = "";

    if (!agenda.length) {
      $("#agendaContainer").html(`
        <div class="alert alert-light text-dark">Tidak ada agenda hari ini.</div>
        <a href="data.php" class="btn btn-success btn-lg mt-3">Tambah Agenda</a>
      `);
      return;
    }

    // =========================
    // 🎠 MODE CAROUSEL
    // =========================
    if (tampilan === "carousel") {
      htmlAgenda += `
        <div id="agendaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000">
          <div class="carousel-inner" style="min-height: 350px;">`;

      agenda.forEach((item, i) => {
        const active = i === 0 ? "active" : "";
        const now = new Date();
        const today = now.toISOString().split("T")[0];

        let mulai = new Date(`${item.tanggal}T${item.waktu_mulai}:00`);
        let selesai;

        const isWaktuSelesaiValid = /^\d{2}:\d{2}$/.test(item.waktu_selesai);
        if (isWaktuSelesaiValid) {
          selesai = new Date(`${item.tanggal}T${item.waktu_selesai}:00`);
        } else {
          selesai = new Date(mulai.getTime() + 2 * 60 * 60 * 1000);
        }

        const isBerlangsung = item.tanggal === today && now >= mulai && now <= selesai;
        const badge = isBerlangsung ? '<span class="badge bg-danger ms-2">Sedang Berlangsung</span>' : "";

        htmlAgenda += `
          <div class="carousel-item ${active}">
            <div class="card h-100 shadow-lg border-${isBerlangsung ? "primary" : "light"} agenda-card">
              <div class="card-body d-flex flex-column justify-content-between">
                <div>
                  <h2 class="text-primary fw-bold">${item.kegiatan}</h2>
                  <div class="mb-3">
                    <i class="bi bi-clock-fill text-warning me-2"></i>
                    ${item.waktu_mulai} - ${item.waktu_selesai} ${badge}
                  </div>
                  <div class="mb-3">
                    <i class="bi bi-geo-alt-fill text-info me-2"></i>${item.tempat || "-"}
                  </div>
                  <div class="mb-3">
                    <i class="bi bi-person-lines-fill text-success me-2"></i>${item.disposisi || "-"}
                  </div>
                  <div class="mb-3">
                    <i class="bi bi-card-text text-danger me-2"></i>${item.keterangan || "-"}
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
          </button>
          <button class="carousel-control-next" type="button" data-bs-target="#agendaCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
          </button>
        </div>`;

      $("#agendaContainer").html(htmlAgenda);
      const newCarousel = document.querySelector("#agendaCarousel");
      if (newCarousel) {
        new bootstrap.Carousel(newCarousel, { interval: 10000, ride: "carousel" });
      }
    }

    // =========================
    // 📋 MODE TABEL
    // =========================
    // =========================
    // 📋 MODE TABEL (FIXED)
    // =========================
    else if (tampilan === "tabel") {
      htmlAgenda += `
        <div class="table-responsive mt-3 shadow-sm rounded-3" style="max-height: 520px; overflow-y: auto;">
          <table class="table table-sm table-bordered align-middle shadow-sm text-center" style="margin-bottom: 0; font-size: 1rem;">
            <thead class="table-info">
              <tr>
                <th style="width:4%;">No</th>
                <th style="width:20%;">Kegiatan</th>
                <th style="width:12%;">Waktu</th>
                <th style="width:14%;">Tempat</th>
                <th style="width:22%;">Keterangan</th>
                <th style="width:10%;">Status</th>
              </tr>
            </thead>
            <tbody>`;

      agenda.forEach((item, i) => {
        const now = new Date();
        const today = now.toISOString().split("T")[0];

        // --- Waktu mulai
        const mulai = new Date(`${item.tanggal}T${item.waktu_mulai}:00`);

        // --- Jika waktu_selesai bukan format HH:mm, otomatis tambah 2 jam
        const isWaktuSelesaiValid = /^\d{2}:\d{2}$/.test(item.waktu_selesai);
        const selesai = isWaktuSelesaiValid ? new Date(`${item.tanggal}T${item.waktu_selesai}:00`) : new Date(mulai.getTime() + 2 * 60 * 60 * 1000);

        // --- Tentukan status
        const isBerlangsung = item.tanggal === today && now >= mulai && now <= selesai;
        const isSelesai = item.tanggal === today && now > selesai;
        const isBelumMulai = item.tanggal === today && now < mulai;

        let badge = "";
        if (isBerlangsung) {
          badge = '<span class="badge bg-danger">Sedang<br>Berlangsung</span>';
        } else if (isSelesai) {
          badge = " &#9989;";
        } else if (isBelumMulai) {
          badge = '<span class="badge bg-secondary">Belum<br>Mulai</span>';
        }

        htmlAgenda += `
            <tr>
              <td>${i + 1}</td>
             <td class="text-start fw-bold text-primary" style="white-space: pre-line;">${item.kegiatan}</td>

              <td class="text-center">${item.waktu_mulai} - ${item.waktu_selesai}</td>
              <td class="text-center" style="white-space: pre-line;">${item.tempat || "-"}</td>
              <td class="text-start" style="white-space: pre-line;">${item.keterangan || "-"}</td>
              <td>${badge}</td>
            </tr>`;
      });

      htmlAgenda += `
            </tbody>
          </table>
        </div>`;

      $("#agendaContainer").html(htmlAgenda);
    }

    // Simpan daftar kata harian
    kataList = data.kata || [];
  });
}

// Load awal + refresh tiap 1 menit
loadAgenda();
setInterval(loadAgenda, 60000);

// Tampilkan toast kata harian
setTimeout(tampilkanToast, 3000);
setInterval(tampilkanToast, 15000);

// ===============================
// 🌦️ CUACA BMKG
// ===============================
$(function () {
  const kode = "32.79.03.1004"; // ganti dengan kode wilayah
  const url = `https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=${kode}`;

  $.getJSON(url)
    .done((res) => {
      const lokasi = res.lokasi;
      const semuaCuaca = res?.data?.[0]?.cuaca?.flat() ?? [];

      const hariIni = new Date();
      const yyyy = hariIni.getFullYear();
      const mm = String(hariIni.getMonth() + 1).padStart(2, "0");
      const dd = String(hariIni.getDate()).padStart(2, "0");
      const tanggalSekarang = `${yyyy}-${mm}-${dd}`;

      const cuacaHariIni = semuaCuaca.filter((item) => item.local_datetime.startsWith(tanggalSekarang));

      if (!cuacaHariIni.length) {
        $("#weatherInner").html(`
          <div class="carousel-item active">
            <div class="bg-secondary text-white p-4 rounded shadow-sm text-center">
              Cuaca hari ini tidak tersedia.
            </div>
          </div>`);
        return;
      }

      let slides = "";
      cuacaHariIni.forEach((item, i) => {
        const waktu = new Date(item.local_datetime).toLocaleTimeString("id-ID", {
          hour: "2-digit",
          minute: "2-digit",
        });

        slides += `
          <div class="carousel-item ${i === 0 ? "active" : ""}">
            <div class="bg-secondary text-white p-4 rounded shadow-sm" style="font-size:1.1rem; min-height:200px;">
              <div class="text-center mb-5">
                <img src="${item.image}" alt="${item.weather_desc}" style="height:60px; margin-bottom:5px;" />
                <h5 class="mb-3">${item.weather_desc}</h5>
                <div><i class="bi bi-clock me-1"></i><strong>${waktu}</strong></div>
              </div>
              <div class="d-flex justify-content-between text-start" style="gap: 30px;">
                <div style="white-space:nowrap;">
                  <div class="mb-2"><i class="bi bi-thermometer-half me-1"></i> Suhu: ${item.t} °C</div>
                  <div class="mb-2"><i class="bi bi-droplet-fill me-1"></i> Kelembapan: ${item.hu} %</div>
                </div>
                <div style="white-space:nowrap;">
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

      $("#weatherInner").html(slides);
    })
    .fail(() => {
      $("#weatherInner").html(`
        <div class="carousel-item active">
          <div class="bg-secondary text-white p-4 rounded text-center">
            Gagal memuat cuaca.
          </div>
        </div>`);
    });
});
