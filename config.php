<?php
$host = 'localhost';
$dbname = 'u656964704_tankily';
$user = 'u656964704_admin';
$pass = 'h+P54LJefiWx';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar con la base de datos: " . $e->getMessage());
}
?>
