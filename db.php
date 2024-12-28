<?php
// db.php - Veritabanı bağlantısı

$servername = "localhost";
$username = "root"; // XAMPP için varsayılan kullanıcı adı
$password = ""; // XAMPP için varsayılan şifre
$dbname = "invoice_system"; // Veritabanı adı

// PDO ile veritabanına bağlanma
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Veritabanı bağlantısı sağlanamadı: " . $e->getMessage();
    die();
}
?>