<?php
require_once '../config.php';
require_once '../inc/auth.php';
include '../inc/header.php';

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nombre'] ?? '');
    $nuevo_correo = trim($_POST['correo'] ?? '');
    $contrasena_actual = $_POST['contrasena_actual'] ?? '';
    $nueva_contrasena = $_POST['nueva_contrasena'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasena'] ?? '';

    if (!empty($nuevo_nombre) && !empty($nuevo_correo)) {
        $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, correo = ? WHERE id = ?");
        $stmt->execute([$nuevo_nombre, $nuevo_correo, $_SESSION['usuario_id']]);

        if (!empty($nueva_contrasena)) {
            if (!password_verify($contrasena_actual, $usuario['contrasena'])) {
                $error = 'La contraseña actual es incorrecta.';
            } elseif ($nueva_contrasena !== $confirmar_contrasena) {
                $error = 'La nueva contraseña no coincide con la confirmación.';
            } else {
                $hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET contrasena = ? WHERE id = ?");
                $stmt->execute([$hash, $_SESSION['usuario_id']]);

                header("Location: ../index.php");
                exit;
            }
        } else {
            header("Location: ../index.php");
            exit;
        }
    }
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="/proyectos/aquanote/css/style.css">
</head>
<body class="mi-cuenta">
<main class="mi-cuenta">
    <a href="../index.php" class="boton-secundario" style="margin-left: 1em;">Volver sin actualizar</a>
    <h1>Mi Cuenta</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">⚠️ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" id="form-cuenta" action="../index.php" style="display:inline;">
        <label>Nombre:
            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required readonly>
            <button type="button" class="editar" onclick="habilitarCampo(this)">✏️</button>
        </label>

        <label>Correo electrónico:
            <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required readonly>
            <button type="button" class="editar" onclick="habilitarCampo(this)">✏️</button>
        </label>

        <div id="cambio-contrasena" style="display: none;">
            <label>Contraseña actual:
                <input type="password" name="contrasena_actual" required>
            </label>

            <label>Nueva contraseña:
                <input type="password" name="nueva_contrasena" required>
            </label>

            <label>Confirmar nueva contraseña:
                <input type="password" name="confirmar_contrasena" required>
            </label>
        </div>

        <button type="button" onclick="mostrarCamposContrasena(this)">🔒 Cambiar contraseña</button>
        <button type="submit" action="../index.php" style="display:inline;">Actualizar datos</button>
    </form>

    <hr>
    <form method="POST" action="../logout.php" style="display:inline;">
        <button type="submit">Cerrar sesión</button>
    </form>

    <form method="POST" action="eliminar_cuenta.php" onsubmit="return confirm('¿Seguro que quieres eliminar tu cuenta? Esta acción no se puede deshacer.');" style="display:inline; margin-left: 10px;">
        <button type="submit" style="background:red;color:white;">Eliminar cuenta</button>
    </form>
</main>
</body>
</html>

<script>
function habilitarCampo(boton) {
    const input = boton.previousElementSibling;
    input.removeAttribute('readonly');
    input.focus();
    boton.style.display = 'none';
}

function mostrarCamposContrasena(boton) {
    document.getElementById('cambio-contrasena').style.display = 'block';
    boton.style.display = 'none';
}
</script>

<?php include '../inc/footer.php'; ?>
