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
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $biografia = trim($_POST['biografia'] ?? '');
    $username = trim($_POST['username'] ?? '');
    
    $web = trim($_POST['web'] ?? '');
    $instagram = trim($_POST['instagram'] ?? '');
    $rol = trim($_POST['rol'] ?? '');
    
     // procesar imagen de perfil
    $foto_perfil = $usuario['foto_perfil'];
    if (!empty($_FILES['foto']['name'])) {
        $destino = '../uploads/perfiles/';
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'perfil_' . $_SESSION['usuario_id'] . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $destino . $nombre_archivo);
        $foto_perfil = '/uploads/perfiles/' . $nombre_archivo;
    }

    if (!empty($nuevo_nombre) && !empty($nuevo_correo)) {
        $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, correo = ?, genero = ?, fecha_nacimiento = ?, ubicacion = ?, biografia = ?, username = ?, foto_perfil = ?, web = ?, instagram = ?, rol = ? WHERE id = ?");
        $stmt->execute([$nuevo_nombre, $nuevo_correo, $genero, $fecha_nacimiento, $ubicacion, $biografia, $username, $foto_perfil, $web, $instagram, $rol, $_SESSION['usuario_id']]);
        header("Location: mi_cuenta.php?edit=0");
        exit;
    }
}
?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Cuenta</title>
    <link rel="stylesheet" href="/proyectos/aquanote/css/style.css">
    <style>
        .perfil-box { display: flex; background: #f1f5fb; padding: 2em; border-radius: 10px; align-items: center; gap: 2em; position: relative; }
        .perfil-box img { width: 120px; height: 120px; border-radius: 100%; object-fit: cover; }
        .perfil-datos { flex: 1; }
        .perfil-datos p { margin: 0.3em 0; }
        .perfil-datos .label { font-weight: bold; color: #555; }
        .iconos-redes { margin-top: 1em; }
        .iconos-redes a { margin-right: 10px; font-size: 1.2em; color: #555; text-decoration: none; }
        .editar-btn { position: absolute; top: 1em; right: 1em; background: none; border: none; font-size: 1.4em; cursor: pointer; }

        .form-row { display: flex; flex-direction: column; margin-bottom: 1em; }
        .form-row input, .form-row select, .form-row textarea { padding: 0.5em; font-size: 1em; }
    </style>
</head>
<body class="mi-cuenta">
<main class="mi-cuenta">
    <h1>Mi Cuenta</h1>

    <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">⚠️ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

<?php if (!isset($_GET['edit']) || $_GET['edit'] != 1): ?>
    <div class="perfil-box">
        <img src="<?= htmlspecialchars($usuario['foto_perfil'] ?: '/img/default-avatar.png') ?>" alt="Foto de perfil">
        <div class="perfil-datos">
            <h2><?= htmlspecialchars($usuario['nombre']) ?> <small>@<?= htmlspecialchars($usuario['username']) ?></small></h2>
            <p><span class="label">Correo:</span> <?= htmlspecialchars($usuario['correo']) ?></p>
            <p><span class="label">Ubicación:</span> <?= htmlspecialchars($usuario['ubicacion']) ?></p>
            <p><span class="label">Fecha nacimiento:</span> <?= htmlspecialchars($usuario['fecha_nacimiento']) ?></p>
            <p><span class="label">Rol:</span> <?= htmlspecialchars($usuario['rol']) ?></p>
            <p><span class="label">Biografía:</span> <?= nl2br(htmlspecialchars($usuario['biografia'])) ?></p>
            <div class="iconos-redes">
                <?php if ($usuario['instagram']): ?><a href="<?= $usuario['instagram'] ?>" target="_blank">📸</a><?php endif; ?>
                <?php if ($usuario['web']): ?><a href="<?= $usuario['web'] ?>" target="_blank">🌐</a><?php endif; ?>
            </div>
        </div>
        <a href="?edit=1" class="editar-btn" title="Editar">✏️</a>
    </div>
<?php else: ?>
    <form method="POST" id="form-cuenta" action="">
        <fieldset>
            <div class="form-row">
                <label>Nombre:
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required>
                </label>
                <label>Username:
                    <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>">
                </label>
            </div>
            <div class="form-row">
                <label>Correo electrónico:
                    <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required>
                </label>
                <label>Foto de perfil (URL):
                    <input type="url" name="foto_perfil" value="<?= htmlspecialchars($usuario['foto_perfil']) ?>">
                </label>
            </div>
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
            </div>
            <div class="form-row">
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
            </div>
            <div class="form-row">
                <label>Instagram:
                    <input type="url" name="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>">
                </label>
                <label>Sitio web:
                    <input type="url" name="web" value="<?= htmlspecialchars($usuario['web']) ?>">
                </label>
            </div>
            <div class="form-row">
                <label>Biografía:
                    <textarea name="biografia" rows="3"><?= htmlspecialchars($usuario['biografia']) ?></textarea>
                </label>
            </div>
        </fieldset>

        <button type="submit">💾 Guardar cambios</button>
        <a href="mi_cuenta.php" style="margin-left:10px">Cancelar</a>
    </form>
<?php endif; ?>


</main>
</body>
</html>
<?php include '../inc/footer.php'; ?>
