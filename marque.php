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
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Marquee Text</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
  <a href="index.php" class="btn btn-sm btn-outline-secondary mb-3">&larr; Home</a>
  <a href="data.php" class="btn btn-sm btn-outline-secondary mb-3">Data Agenda</a>
  <a href="kata.php" class="btn btn-sm btn-outline-secondary mb-3">Kata-kata hari ini</a>
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
  <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

<script>
  CKEDITOR.replace('editor', {
  height: 200,
  toolbar: [
    { name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline' ] },
    { name: 'colors', items: [ 'TextColor', 'BGColor' ] },
    { name: 'paragraph', items: [ 'NumberedList', 'BulletedList' ] },
    { name: 'links', items: [ 'Link', 'Unlink' ] },
    { name: 'clipboard', items: [ 'Undo', 'Redo' ] }
  ]
});

</script>
</body>
</html>
