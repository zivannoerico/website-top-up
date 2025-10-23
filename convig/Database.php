<?php
$servername = "localhost";
$username = "root";
$password = ""; // kosongkan jika pakai Laragon atau XAMPP default
$dbname = "vansstore";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
