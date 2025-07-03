<?php
// Koneksi SQLite
$db = new PDO('sqlite:agenda.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Buat tabel jika belum ada
$db->exec("CREATE TABLE IF NOT EXISTS kata (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    kata_hari_ini TEXT NOT NULL,
    user TEXT NOT NULL
)");

// Hapus kata
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $db->prepare("DELETE FROM kata WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: kata.php");
    exit;
}

// Ambil data untuk edit
$editKata = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM kata WHERE id = ?");
    $stmt->execute([$editId]);
    $editKata = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Simpan kata baru atau update
if (isset($_POST['simpan_kata'])) {
    $id = $_POST['id'] ?? null;
    $kata = trim($_POST['kata_hari_ini'] ?? '');
    $user = trim($_POST['user'] ?? '');
    if ($kata && $user) {
        if ($id) {
            $stmt = $db->prepare("UPDATE kata SET kata_hari_ini = ?, user = ? WHERE id = ?");
            $stmt->execute([$kata, $user, $id]);
        } else {
            $stmt = $db->prepare("INSERT INTO kata (kata_hari_ini, user) VALUES (?, ?)");
            $stmt->execute([$kata, $user]);
        }
        header("Location: kata.php");
        exit;
    }
}

// Ambil semua data kata
$kataList = $db->query("SELECT * FROM kata ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Kata Hari Ini</title>
  <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/fontawesome/css/all.min.css" crossorigin="anonymous" />
  <style>
    body { padding: 20px; background-color: #f8f9fa; }
    .section-title { margin-bottom: 30px; }
    .table td, .table th { vertical-align: middle; }
  </style>
</head>
<body class="container">
  <div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Home</a>
    <a href="data.php" class="btn btn-outline-secondary btn-sm">Data Agenda</a>
    <a href="marque.php" class="btn btn-outline-secondary btn-sm">Running Text</a>
  </div>

  <h1 class="section-title text-center">📝 Kata Hari Ini</h1>

  <div class="row">
    <!-- Form Tambah -->
    <div class="col-md-5">
      <div class="card shadow-sm mb-4">
        <div class="card-body">
          <h5 class="mb-3"><?= $editKata ? '✏️ Edit Kata' : '➕ Tambah Kata Baru' ?></h5>
          <form method="post" class="row g-3">
            <?php if ($editKata): ?>
              <input type="hidden" name="id" value="<?= $editKata['id'] ?>">
            <?php endif; ?>
            <div class="col-12">
              <textarea name="kata_hari_ini" class="form-control" placeholder="Masukkan kata motivasi / harian" required><?= $editKata['kata_hari_ini'] ?? '' ?></textarea>
            </div>
            <div class="col-12">
              <select name="user" class="form-select" required>
                <option value="">-- Pilih Pelopor --</option>
                <?php
                $users = ["PPK", "Peltek", "Ruli", "Arif", "Shilvy", "Hudan", "Revi", "Uci", "Agus", "Ridwan"];
                foreach ($users as $u): ?>
                  <option value="<?= $u ?>" <?= (isset($editKata) && $editKata['user'] === $u) ? 'selected' : '' ?>><?= $u ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 d-grid">
              <button type="submit" name="simpan_kata" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $editKata ? 'Update' : 'Simpan' ?>
              </button>
              <?php if ($editKata): ?>
                <a href="kata.php" class="btn btn-secondary mt-2">Batal</a>
              <?php endif; ?>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Tabel Kata -->
    <div class="col-md-7">
      <div class="table-responsive shadow-sm">
        <table class="table table-bordered table-striped">
          <thead class="table-dark text-center">
            <tr>
              <th width="60">No</th>
              <th>Kata Hari Ini</th>
              <th>Pelopor</th>
              <th width="120">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($kataList): ?>
              <?php foreach ($kataList as $i => $row): ?>
                <tr>
                  <td class="text-center"><?= $i + 1 ?></td>
                  <td><?= htmlspecialchars($row['kata_hari_ini']) ?></td>
                  <td class="text-center"><?= htmlspecialchars($row['user']) ?></td>
                  <td class="text-center">
                    <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-warning me-1" title="Edit">
                      <i class="fas fa-pencil-alt"></i>
                    </a>
                    <a href="?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus kata ini?')">
                      <i class="fas fa-trash-alt"></i>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr><td colspan="4" class="text-center text-muted">Belum ada kata harian.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
