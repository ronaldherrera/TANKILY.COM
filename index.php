<?php
require_once 'inc/auth.php';
include 'inc/header.php';
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="/proyectos/aquanote/css/style.css">
</head>
<body class="index">
<main class="index">
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?> 👋</h1>
    <p>Este será tu panel de control de Acuario. Aquí verás tus últimos registros y tareas pendientes.</p>
    <a href="logout.php">Cerrar sesión</a>
    <ul>
            <li><a href="usuarios/mi_acuario.php">Configurar acuario</a></li>
            <li><a href="usuarios/mi_cuenta.php">Mi cuenta</a></li>
        </ul>
</main>
</body>
</html>


<?php include 'inc/footer.php'; ?>
