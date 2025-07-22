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

$foto_bd = $usuario['foto_perfil'] ?? '';
$es_avatar = str_starts_with($foto_bd, '/img/avatars/');
$es_foto_usuario = str_starts_with($foto_bd, '/uploads/perfiles/');
$foto_path = $_SERVER['DOCUMENT_ROOT'] . $foto_bd;
$tiene_foto_fisica = $es_foto_usuario && file_exists($foto_path);

$edad = '';
if (!empty($usuario['fecha_nacimiento'])) {
    $nacimiento = new DateTime($usuario['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($nacimiento)->y;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar_avatar'])) {
    $nuevo_avatar = $_POST['foto_avatar_default'] ?? '';
    if (str_starts_with($nuevo_avatar, '/img/avatars/')) {
        $stmt = $db->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
        $stmt->execute([$nuevo_avatar, $_SESSION['usuario_id']]);
        $usuario['foto_perfil'] = $nuevo_avatar;
    }
}
 elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guardar'])) {
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
    $nombre_archivo = 'perfil_' . $_SESSION['usuario_id'] . '.jpg';
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
        $img_optimizada = imagescale($img, 300);
        imageinterlace($img_optimizada, true);
        imagejpeg($img_optimizada, $ruta_completa, 75);
        imagedestroy($img);
        imagedestroy($img_optimizada);
        // SOLO guarda la ruta física si la imagen se subió bien
        $foto_perfil = '/uploads/perfiles/' . $nombre_archivo;
    }
} elseif (!empty($_POST['foto_avatar_default']) && str_starts_with($_POST['foto_avatar_default'], '/img/avatars/')) {
    // Solo guarda avatar por defecto si NO hay foto física subida
    $foto_perfil = $_POST['foto_avatar_default'];
} else {
    // Si no hay nada, puedes dejarlo vacío o ruta por defecto
    $foto_perfil = '';
}


} 


if (!empty($_POST['foto_avatar_default']) && !str_starts_with($_POST['foto_avatar_default'], '/img/avatars/')) {
    $error = 'Avatar no permitido.';
}

    if (!empty($nuevo_nombre)) {
    $stmt = $db->prepare("UPDATE usuarios SET nombre = ?, genero = ?, fecha_nacimiento = ?, ubicacion = ?, biografia = ?, username = ?, foto_perfil = ?, web = ?, instagram = ?, rol = ? WHERE id = ?");
    $stmt->execute([$nuevo_nombre, $genero, $fecha_nacimiento, $ubicacion, $biografia, $username, $foto_perfil, $web, $instagram, $rol, $_SESSION['usuario_id']]);

    // REFRESCA DATOS DEL USUARIO TRAS GUARDAR
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    header("Location: mi_cuenta.php?edit=0");
    exit;
}

?>


<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenida</title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .contenedor-perfil-box {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }
        .contenedor-perfil-box .perfil-box:first-child {
            flex: 0 0 auto;
            width: 150px;
        }
        .contenedor-perfil-box .perfil-box:last-child {
            flex: 1;
        }
        .perfil-box { background: #f1f5fb; border-radius: 10px; padding:20px; }
        .perfil-box h3 { margin-top: 0; }
        .form-row { display: flex; flex-direction: column; margin-bottom: 1em; }
        .form-row input, .form-row select, .form-row textarea { padding: 0.5em; font-size: 1em; }
        .perfil-avatar { width: 150px; height: 150px; border-radius: 100%; object-fit: cover; margin-bottom: 1em; }
        .acciones { margin-top: 2em; display: flex; gap: 1em; }
        .acciones button { padding: 0.7em 1.5em; font-size: 1em; }
        .bienvenida form{  display:flex; flex-direction: column; gap:20px; }
    </style>
</head>
<body class="bienvenida">
<main class="bienvenida">
    <h1>¡Hola <?= htmlspecialchars($_SESSION['usuario']) ?> 👋!</h1>
    <p>Revisa tu información antes de empezar. Puedes completarla ahora o hacerlo más tarde.</p>

    <?php if (!empty($error)): ?>
        <p style="color:red; font-weight:bold;">⚠️ <?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        
        <!-- Sección 2: Datos del registro -->
        <div class="contenedor-perfil-box">
         <div class="perfil-box">
    <h3>Avatar</h3>
    <img src="<?= htmlspecialchars($usuario['foto_perfil'] ?: '/img/default-avatar.webp') ?>" class="perfil-avatar" alt="Foto de perfil">
    <button type="button" onclick="abrirPopupAvatares()">🖼 Cambiar avatar</button>
    <button type="button" onclick="abrirPopupSubir()">📤 Subir foto</button>
</div>
        </div>
        
        <div class="perfil-box">
            <h3>Datos del usuario</h3>
            <p><strong>Nombre: </strong><?= htmlspecialchars($usuario['nombre']) ?><br><strong>Nombre de usuario: </strong><?= htmlspecialchars($usuario['username']) ?><br><strong> Fecha de nacimiento: </strong><?= htmlspecialchars($usuario['fecha_nacimiento']) ?> (<?= $edad ?> años)<br><strong>País: </strong><?= htmlspecialchars($usuario['pais']) ?></p>
            <p></p>
        </div>   
        </div>
        
        
        <!-- Sección 1: Correo -->
        <div class="perfil-box">
            <h3>✉️ Correo:</h3>
            <p><?= htmlspecialchars($usuario['email']) ?> <i style="color:red; font-size: 10px;">(Sin verificar)</i></p>
            <a href="/verificar.php" target="_blank" style="display:inline-block; margin-top:0.5em; padding:0.5em 1em; background:#007bff; color:white; border-radius:5px; text-decoration:none;">Verificar ahora</a>
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
<div id="popup-avatares" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); justify-content:center; align-items:center;">
  <div style="background:#fff; padding:20px; border-radius:10px; max-width:400px;">
    <h3>Elige tu avatar</h3>
    <div id="previsualizador" style="text-align:center; margin-bottom:1em;">
  <img id="preview-avatar" src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" style="width:100px; height:100px; border-radius:50%; border:3px solid #007bff;">
</div>
<form method="POST">
  <input type="hidden" name="foto_avatar_default" id="input-avatar-def">
  <button type="submit" name="guardar_avatar" style="margin-bottom:1em;">Guardar avatar</button>
</form>
      <img src="/img/avatars/avatar_default (1).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (1).webp')">
      <img src="/img/avatars/avatar_default (2).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (2).webp')">
      <img src="/img/avatars/avatar_default (3).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (3).webp')">
      <img src="/img/avatars/avatar_default (4).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (4).webp')">
      <img src="/img/avatars/avatar_default (5).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (5).webp')">
      <img src="/img/avatars/avatar_default (6).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (6).webp')">
      <img src="/img/avatars/avatar_default (7).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (7).webp')">
      <img src="/img/avatars/avatar_default (8).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (8).webp')">
      <img src="/img/avatars/avatar_default (9).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (9).webp')">
      <img src="/img/avatars/avatar_default (10).webp" style="width:60px; height:60px; border-radius:50%; cursor:pointer;" onclick="seleccionarAvatar('/img/avatars/avatar_default (10).webp')">
    </div>
    <button onclick="cerrarPopupAvatares()" style="margin-top:1em;">Cancelar</button>
  </div>
</div>
  <div id="popup-subirfoto" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.6); justify-content:center; align-items:center; z-index:9999;">
  <div style="background:#fff; padding:20px; border-radius:10px; max-width:400px; text-align:center;">
    <h3>Subir foto de perfil</h3>
    <?php
echo "<!-- Foto BD: $foto_bd | Path: $foto_path | Existe: ".(file_exists($foto_path) ? "SI" : "NO")." -->";
?>
    <div id="preview-subida" style="margin-bottom: 1em; display: flex; align-items: center; justify-content: center;">
  <?php if ($tiene_foto_fisica): ?>
  <img id="img-preview-subida" src="<?= $foto_bd ?>?v=<?= filemtime($foto_path) ?>" style="width:100px; height:100px; border-radius:50%; border:3px solid #007bff; object-fit:cover;">
<?php else: ?>
  <img id="img-preview-subida" src="" style="width:100px; height:100px; border-radius:50%; border:3px solid #007bff; object-fit:cover; display:none;">
  <div id="svg-preview-placeholder" style="width:100px; height:100px; border-radius:50%; border:3px solid #007bff; background: #e5e7eb; display:flex; align-items:center; justify-content:center;">
    <svg width="48" height="48" viewBox="0 0 48 48">
      <circle cx="24" cy="24" r="22" fill="#e5e7eb"/>
      <line x1="24" y1="15" x2="24" y2="33" stroke="#bdbdbd" stroke-width="4" stroke-linecap="round"/>
      <line x1="15" y1="24" x2="33" y2="24" stroke="#bdbdbd" stroke-width="4" stroke-linecap="round"/>
    </svg>
  </div>
<?php endif; ?>

</div>



    <input type="file" id="input-subir-foto" accept="image/*" style="display:none;">
    <button type="button" onclick="document.getElementById('input-subir-foto').click()">Elegir foto</button>
    <button type="button" id="btn-guardar-foto" disabled>Guardar</button>
    <button type="button" onclick="cerrarPopupSubirFoto()">Cancelar</button>
  </div>
</div>

<script>
function abrirPopupAvatares() {
  document.getElementById('popup-avatares').style.display = 'flex';
}
function cerrarPopupAvatares() {
  document.getElementById('popup-avatares').style.display = 'none';
}
function seleccionarAvatar(ruta) {
  document.getElementById('preview-avatar').src = ruta;
  document.getElementById('input-avatar-def').value = ruta;
}

function abrirPopupSubir() {
  document.getElementById('popup-subirfoto').style.display = 'flex';
  document.getElementById('input-subir-foto').value = '';
  // Borra el valor del input-avatar-def para evitar que se mezcle al subir foto
  document.getElementById('input-avatar-def').value = '';

  // Mostrar/ocultar SVG de placeholder si el img está vacío
  var img = document.getElementById('img-preview-subida');
  var svg = document.getElementById('svg-preview-placeholder');
  if (img && img.src && img.src.includes('data:image')) {
    img.style.display = 'inline-block';
    if (svg) svg.style.display = 'none';
    document.getElementById('btn-guardar-foto').disabled = false;
  } else {
    img.style.display = 'none';
    if (svg) svg.style.display = 'flex';
    document.getElementById('btn-guardar-foto').disabled = true;
  }
}


  
function cerrarPopupSubirFoto() {
  document.getElementById('popup-subirfoto').style.display = 'none';
}

// Previsualización de la imagen
document.getElementById('input-subir-foto').addEventListener('change', function(){
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e){
      document.getElementById('img-preview-subida').src = e.target.result;
      document.getElementById('img-preview-subida').style.display = 'inline-block';
      var svg = document.getElementById('svg-preview-placeholder');
      if (svg) svg.style.display = 'none';
      document.getElementById('btn-guardar-foto').disabled = false;
    }
    reader.readAsDataURL(file);
  }
});


// Guardar la foto subida (envía el formulario principal)
document.getElementById('btn-guardar-foto').addEventListener('click', function(){
  // Pasa el archivo al input del form principal
  document.querySelector('input[name="foto"]').files = document.getElementById('input-subir-foto').files;
  cerrarPopupSubirFoto();
  // Simula click en el botón de guardar del form principal
  document.querySelector('button[name="guardar"]').click();
});

</script>
</html>
<?php include '../inc/footer.php'; ?>
