<?php
require_once '../config.php';
require_once '../inc/auth.php';
include '../inc/header.php';
if (isset($_POST['continuar'])) {
    header('Location: /index.php');
    exit;
}

$stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['usuario_id']]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
    $nuevo_nombre = trim($_POST['nombre'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $username = trim($_POST['username'] ?? '');
    
    $web = trim($_POST['web'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $rol = trim($_POST['rol'] ?? '');

    $foto_perfil = $usuario['foto_perfil'];
    if (!empty($_FILES['foto']['name'])) {
    $destino = '../uploads/perfiles/';
    $nombre_archivo = 'perfil_' . $_SESSION['usuario_id'] . '.jpg'; // Fuerza JPG
    $ruta_completa = $destino . $nombre_archivo;

    $tipo = mime_content_type($_FILES['foto']['tmp_name']);

    if ($tipo === 'image/png') {
        $img = imagecreatefrompng($_FILES['foto']['tmp_name']);
    } elseif ($tipo === 'image/jpeg') {
        $img = imagecreatefromjpeg($_FILES['foto']['tmp_name']);
    } elseif ($tipo === 'image/webp') {
        $img = imagecreatefromwebp($_FILES['foto']['tmp_name']);
    } else {
        $error = 'Formato de imagen no soportado.';
        $img = false;
    }

    if ($img) {
        $img_optimizada = imagescale($img, 300); // max 300px ancho
        imageinterlace($img_optimizada, true);
        imagejpeg($img_optimizada, $ruta_completa, 75); // calidad 75%
        imagedestroy($img);
        imagedestroy($img_optimizada);
        $foto_perfil = '/uploads/perfiles/' . $nombre_archivo;
    }
}

    if (!empty($nuevo_nombre) && !empty($nuevo_correo)) {
        $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, genero = ?, fecha_nacimiento = ?, ubicacion = ?, biografia = ?, username = ?, foto_perfil = ?, web = ?, instagram = ?, rol = ? WHERE id = ?");
$stmt->execute([$nuevo_nombre, $genero, $fecha_nacimiento, $ubicacion, $biografia, $username, $foto_perfil, $web, $instagram, $rol, $_SESSION['usuario_id']]);

        header("Location: mi_cuenta.php?edit=0");
        exit;
    }
}
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida</title>
    <link rel="stylesheet" href="/proyectos/aquanote/css/style.css">
    <style>
        .perfil-box { background: #f1f5fb; padding: 20px; border-radius: 10px; margin-bottom: 2em; }
        .perfil-box h3 { margin-top: 0; }
        .form-row { display: flex; flex-direction: column; margin-bottom: 1em; }
        .form-row input, .form-row select, .form-row textarea { padding: 0.5em; font-size: 1em; }
        .perfil-avatar { width: 100px; height: 100px; border-radius: 100%; object-fit: cover; margin-bottom: 1em; }
        .acciones { margin-top: 2em; display: flex; gap: 1em; }
        .acciones button { padding: 0.7em 1.5em; font-size: 1em; }
    </style>
</head>
<body class="mi-cuenta">
<main class="mi-cuenta">
    <h1>¡Hola <?= htmlspecialchars($_SESSION['usuario']) ?> 👋!</h1>
    <p>Revisa tu información antes de empezar. Puedes completarla ahora o hacerlo más tarde.</p>

    <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">⚠️ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <!-- Sección 1: Correo -->
        <div class="perfil-box">
            <p><strong>✉️ Correo:</strong><br><?= htmlspecialchars($usuario['email']) ?> <i style="color:red; font-size: 10px;">(Sin verificar)</i></p>
            <a href="/verificar.php" target="_blank" style="display:inline-block; margin-top:0.5em; padding:0.5em 1em; background:#007bff; color:white; border-radius:5px; text-decoration:none;">Verificar ahora</a>
        </div>

        <!-- Sección 2: Datos del registro -->
        <div class="perfil-box">
            <h3>📝 Datos recogidos en el registro</h3>
            <img src="<?= htmlspecialchars($usuario['foto_perfil'] ?: '/img/default-avatar.webp') ?>" class="perfil-avatar" alt="Foto de perfil">
            <div class="form-row">
                <label>Nombre:
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </label>
                <label>Username:
                    <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>">
                </label>
            </div>
        </div>

        <!-- Sección 3: Datos adicionales -->
        <div class="perfil-box">
            <h3>➕ Información adicional</h3>
            <div class="form-row">
                <label>Género:
                    <select name="genero">
                        <option value="">-- Selecciona --</option>
                        <option value="masculino" <?= $usuario['genero'] === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="femenino" <?= $usuario['genero'] === 'femenino' ? 'selected' : '' ?>>Femenino</option>
                        <option value="otro" <?= $usuario['genero'] === 'otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </label>
                <label>Fecha de nacimiento:
                    <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($usuario['fecha_nacimiento']) ?>">
                </label>
                <label>Ubicación:
                    <input type="text" name="ubicacion" value="<?= htmlspecialchars($usuario['ubicacion']) ?>">
                </label>
                <label>Rol:
                    <select name="rol">
                        <option value="">-- Selecciona --</option>
                        <option value="aficionado" <?= $usuario['rol'] === 'aficionado' ? 'selected' : '' ?>>Acuariófilo</option>
                        <option value="criador" <?= $usuario['rol'] === 'criador' ? 'selected' : '' ?>>Criador</option>
                        <option value="vendedor" <?= $usuario['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                        <option value="divulgador" <?= $usuario['rol'] === 'divulgador' ? 'selected' : '' ?>>Divulgador</option>
                        <option value="profesional" <?= $usuario['rol'] === 'profesional' ? 'selected' : '' ?>>Profesional</option>
                    </select>
                </label>
                <label>Instagram:
                    <input type="url" name="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>">
                </label>
                <label>Sitio web:
                    <input type="url" name="web" value="<?= htmlspecialchars($usuario['web']) ?>">
                </label>
                <label>Biografía:
                    <textarea name="biografia" rows="3"><?= htmlspecialchars($usuario['biografia']) ?></textarea>
                </label>
                <label>Foto de perfil:
                    <input type="file" name="foto">
                </label>
            </div>
        </div>

        <!-- Acciones -->
        <div class="acciones">
            <button type="submit" name="guardar">💾 Guardar y continuar</button>
            <button type="submit" name="continuar">⏭️ Hacerlo más tarde</button>
        </div>
    </form>
</main>
</body>
</html>
<?php include '../inc/footer.php'; ?>
