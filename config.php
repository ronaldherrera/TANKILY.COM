<?php
$host = 'localhost';
$dbname = 'u656964704_aquanote';
$user = 'u656964704_root';
$pass = 'd3&b#$#U$9Hn';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
