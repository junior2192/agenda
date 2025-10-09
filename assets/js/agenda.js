function updateLiveClock() {
  const now = new Date();
  const jam = String(now.getHours()).padStart(2, "0");
  const menit = String(now.getMinutes()).padStart(2, "0");
  const detik = String(now.getSeconds()).padStart(2, "0");
  document.getElementById("liveClock").textContent = `${jam}:${menit}:${detik}`;
}
setInterval(updateLiveClock, 1000);
updateLiveClock();

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

function loadAgenda() {
  $.getJSON("api/agenda.php", function (data) {
    console.log(data.agenda); // Debug jumlah agenda

    // Update hari & tanggal
    $("#hari").text(data.hari);
    $("#tanggalLengkap").text(data.tanggal);

    // Update running text
    $("#runningMarquee").html(data.running_text);

    // Update agenda
    const agenda = data.agenda;
    let htmlAgenda = "";
    if (agenda.length) {
      htmlAgenda += `
          <div id="agendaCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="10000">
            <div class="carousel-inner" style="min-height: 350px;">`;

      agenda.forEach((item, i) => {
        const active = i === 0 ? "active" : "";
        const now = new Date();
        const today = now.toISOString().split("T")[0];

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
                        <i class="bi bi-geo-alt-fill text-info me-2"></i>
                        ${item.tempat || "-"}
                      </div>
                      <div class="mb-3">
                        <i class="bi bi-person-lines-fill text-success me-2"></i>
                        ${item.disposisi || "-"}
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

    $("#agendaContainer").html(htmlAgenda);

    const newCarousel = document.querySelector("#agendaCarousel");
    if (newCarousel) {
      new bootstrap.Carousel(newCarousel, {
        interval: 10000,
        ride: "carousel",
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
  const kode = "32.79.03.1004";
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
        $("#weatherInner").html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded shadow-sm text-center">Cuaca hari ini tidak tersedia.</div></div>`);
        return;
      }

      let slides = "";
      cuacaHariIni.forEach((item, i) => {
        const waktu = new Date(item.local_datetime).toLocaleTimeString("id-ID", { hour: "2-digit", minute: "2-digit" });
        slides += `<div class="carousel-item ${i === 0 ? "active" : ""}">
          <div class="bg-secondary text-white p-4 rounded shadow-sm" style="font-size:1.1rem; min-height: 200px;">
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
      $("#weatherInner").html(`<div class="carousel-item active"><div class="bg-secondary text-white p-4 rounded text-center">Gagal memuat cuaca.</div></div>`);
    });
});
