<?php
// File: api/agenda.php
header('Content-Type: application/json');
date_default_timezone_set('Asia/Jakarta');
$db = new PDO('sqlite:../agenda.db');
$config = json_decode(file_get_contents(__DIR__ . '/../config.json'), true);

$now = new DateTime();
$today = $now->format('Y-m-d');

// Agenda Hari Ini
$stmt = $db->prepare("SELECT * FROM agenda WHERE tanggal = ? ORDER BY waktu_mulai ASC");
$stmt->execute([$today]);
$agendaToday = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($agendaToday as &$item) {
  $tanggalAgenda = $item['tanggal'];
  $start = DateTime::createFromFormat('Y-m-d H:i', "$tanggalAgenda {$item['waktu_mulai']}");
  $end = DateTime::createFromFormat('Y-m-d H:i', "$tanggalAgenda {$item['waktu_selesai']}");
  $item['is_active'] = ($now >= $start && $now <= $end);
}


// Kata-kata Harian
$stmtKata = $db->query("SELECT kata_hari_ini, user FROM kata ORDER BY RANDOM()");
$kataList = $stmtKata->fetchAll(PDO::FETCH_ASSOC);

// Marquee
$stmt = $db->query("SELECT running_text FROM marque ORDER BY id DESC LIMIT 1");
$marqueeTextRow = $stmt->fetch(PDO::FETCH_ASSOC);
$marqueeText = $marqueeTextRow ? implode(' &nbsp; • &nbsp; ', $marqueeTextRow) : '';

$hariIndonesia = [
  'Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa',
  'Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'
];
$hari = $hariIndonesia[$now->format('l')];
$tanggalLengkap = $now->format('d-m-Y');

// Output JSON
echo json_encode([
  'agenda' => $agendaToday,
  'kata' => $kataList,
  'running_text' => $marqueeText,
  'hari' => $hari,
  'tanggal' => $tanggalLengkap,
  'tampilan_agenda' => $config['tampilan_agenda'] ?? 'carousel'
], JSON_UNESCAPED_UNICODE);
