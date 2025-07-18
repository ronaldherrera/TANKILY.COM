<?php
require_once 'config.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pais = trim($_POST['pais'] ?? '');
    $edad = intval($_POST['edad'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    // Validaciones básicas
    if ($pais === '') $errores[] = 'El país es obligatorio';
    if ($edad <= 0) $errores[] = 'La edad debe ser un número positivo';
    if ($nombre === '') $errores[] = 'El nombre es obligatorio';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    if ($password !== $confirmar_password) $errores[] = 'Las contraseñas no coinciden';

    // Si no hay errores, registrar usuario
    if (empty($errores)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare('INSERT INTO usuarios (pais, edad, nombre, email, contrasena) VALUES (?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$pais, $edad, $nombre, $email, $hash]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'El email ya está registrado';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .form-row {
            display: flex;
            gap: 1rem;
        }
        .form-row label {
            flex: 1;
        }
    </style>
</head>
<body class="registro">
<main class="registro">
    <img src="./img/logo.svg" alt="">
    <h1>Crear cuenta</h1>
    <?php if (!empty($errores)): ?>
        <ul class="errores">
            <?php foreach ($errores as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-row">
            <label>País:
                <select name="pais" required>
                    <option value="">Seleccione un país</option>
                    <?php
$paises = ["Alemania", "Argentina", "Australia", "Austria", "Bélgica", "Bolivia", "Brasil", "Canadá", "Chile",
    "China", "Colombia", "Corea del Sur", "Costa Rica", "Cuba", "Dinamarca", "Ecuador", "Egipto", "El Salvador",
    "Emiratos Árabes Unidos", "España", "Estados Unidos", "Filipinas", "Finlandia", "Francia", "Grecia", "Guatemala",
    "Honduras", "India", "Indonesia", "Irlanda", "Israel", "Italia", "Japón", "Luxemburgo", "México", "Marruecos",
    "Noruega", "Nueva Zelanda", "Países Bajos", "Panamá", "Paraguay", "Perú", "Polonia", "Portugal", "Puerto Rico",
    "Reino Unido", "República Checa", "República Dominicana", "Rumanía", "Rusia", "Suecia", "Suiza", "Tailandia",
    "Turquía", "Ucrania", "Uruguay", "Venezuela", "Vietnam"];
foreach ($paises as $p) {
    echo '<option value="' . htmlspecialchars($p) . '"' . ($pais === $p ? ' selected' : '') . '>' . htmlspecialchars($p) . '</option>';
}
?>

                </select>
            </label>
            <label>Edad:
                <input type="number" name="edad" min="1" required>
            </label>
        </div>
        <label>Nombre:
            <input type="text" name="nombre" required>
        </label>
        <label>Email:
            <input type="email" name="email" required>
        </label>
        <label>Contraseña:
            <input type="password" name="password" required>
        </label>
        <label>Confirmar contraseña:
            <input type="password" name="confirmar_password" required>
        </label>
        <button type="submit">Registrarse</button>
    </form>
    <p class="enlace-login">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</main>
</body>
</html>
<?php include 'inc/footer.php'; ?>
