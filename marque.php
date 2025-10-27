<?php
$db = new PDO('sqlite:agenda.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat tabel jika belum ada
$db->exec("CREATE TABLE IF NOT EXISTS marque (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    running_text TEXT NOT NULL
)");

// Hapus
if (isset($_GET['hapus'])) {
    $stmt = $db->prepare("DELETE FROM marque WHERE id = ?");
    $stmt->execute([$_GET['hapus']]);
    header("Location: marque.php");
    exit;
}

// Ambil data edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM marque WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Simpan
if (isset($_POST['simpan'])) {
    $text = trim($_POST['running_text']);
    $id = $_POST['id'] ?? null;

     // ✅ Validasi: pastikan tidak kosong
    if ($text === '') {
        header("Location: marque.php");
        exit;
    }

    if ($id) {
        $stmt = $db->prepare("UPDATE marque SET running_text = ? WHERE id = ?");
        $stmt->execute([$text, $id]);
    } else {
        $stmt = $db->prepare("INSERT INTO marque (running_text) VALUES (?)");
        $stmt->execute([$text]);
    }
    header("Location: marque.php");
    exit;
}

// Ambil semua
$data = $db->query("SELECT * FROM marque ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

$current = basename($_SERVER['PHP_SELF']);

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Marquee Text</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css" crossorigin="anonymous" />

</head>
<body class="container py-4">
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
            <a class="nav-link text-white" href="setting.php">
              <i class="fas fa-cog me-1"></i> Pengaturan
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <h3 class="mb-4">📰 Kelola Running Text</h3>

  <form method="post" class="mb-4">
    <?php if ($editData): ?>
      <input type="hidden" name="id" value="<?= $editData['id'] ?>">
    <?php endif; ?>
    <div class="mb-3">
      <textarea name="running_text" id="editor"><?= $editData['running_text'] ?? '' ?></textarea>
    </div>
    <button type="submit" name="simpan" class="btn btn-success">💾 Simpan</button>
    <?php if ($editData): ?>
      <a href="marque.php" class="btn btn-secondary">Batal</a>
    <?php endif; ?>
  </form>

  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th width="60">No</th>
        <th>Teks</th>
        <th width="120">Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $i => $row): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><?= htmlspecialchars($row['running_text']) ?></td>
        <td>
          <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
          <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus?')">Hapus</a>
        </td>
      </tr>
      <?php endforeach ?>
      <?php if (!$data): ?>
      <tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>
      <?php endif ?>
    </tbody>
  </table>
 <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
  <script>
    ClassicEditor
      .create(document.querySelector('#editor'), {
        toolbar: [
          'bold', 'italic', 'underline', '|',
          'bulletedList', 'numberedList', '|',
          'link', 'undo', 'redo'
        ],
        height: '200px'
      })
      .catch(error => {
        console.error(error);
      });
  </script>


</body>
</html>
