<?php
// Gunakan library phpdotenv jika tersedia, tapi tetap fallback ke nilai default
$autoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;

    if (class_exists(\Dotenv\Dotenv::class)) {
        // Muat file .env di root proyek
        $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__);
        // safeLoad() tidak memicu error jika .env tidak ada
        $dotenv->safeLoad();
    }
}

$server   = getenv('DB_HOST')     ?: 'localhost';
$user     = getenv('DB_USERNAME') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_DATABASE') ?: 'db_arsip';
$port     = getenv('DB_PORT')     ?: '3306';
$charset  = getenv('DB_CHARSET')  ?: 'utf8mb4';

$db = mysqli_connect($server, $user, $password, $database, $port);

if (!$db) {
    die("Gagal terhubung dengan database: " . mysqli_connect_error());
}

// Atur charset koneksi
mysqli_set_charset($db, $charset);

?>
