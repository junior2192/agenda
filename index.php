<?php
date_default_timezone_set('Asia/Jakarta');
// Baca config
$configFile = __DIR__ . '/config.json';
$config = json_decode(file_get_contents($configFile), true);

$tema = $config['tema_aktif'] ?? 'tema1';
$file = __DIR__ . "/tema/{$tema}.php";

if (file_exists($file)) {
    include $file;
} else {
    echo "Tema tidak ditemukan! ($file)";
}
?>
