<?php
require_once 'config.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pais = trim($_POST['pais'] ?? '');
    $edad = intval($_POST['edad'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $username = preg_replace("/[^a-z0-9_.-]/", "", $username);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    // Validaciones básicas
    if ($pais === '') $errores[] = 'El país es obligatorio';
    if ($edad <= 0) $errores[] = 'La edad debe ser un número positivo';
    if ($nombre === '') $errores[] = 'El nombre es obligatorio';
    if (!preg_match("/^[a-z0-9_.-]+$/", $username)) $errores[] = 'El nombre de usuario solo puede contener minúsculas, números, guiones (-), guiones bajos (_) y puntos (.)';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido';
    if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres';
    if ($password !== $confirmar_password) $errores[] = 'Las contraseñas no coinciden';

    // Comprobar unicidad de username y email
    if (empty($errores)) {
        $stmt = $db->prepare('SELECT COUNT(*) FROM usuarios WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $errores[] = 'El nombre de usuario o el email ya están registrados';
        }
    }

    // Si no hay errores, registrar usuario
    if (empty($errores)) {
        $contrasena_hash = password_hash($password, PASSWORD_DEFAULT);
        $avatares = ['avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png'];
        $avatar = '/img/avatars/' . $avatares[array_rand($avatares)];

        $stmt = $db->prepare('INSERT INTO usuarios (pais, edad, nombre, username, email, contrasena, avatar) VALUES (?, ?, ?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$pais, $edad, $nombre, $username, $email, $contrasena_hash, $avatar]);
            header('Location: mi-cuenta.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error al registrar el usuario';
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

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach((input) => {
            input.addEventListener('blur', () => {
                if (!input.checkValidity()) {
                    input.classList.add('input-error');
                    input.classList.remove('input-success');
                    let tooltip = input.nextElementSibling;
                    if (tooltip && tooltip.classList.contains('tooltip-error')) {
                        tooltip.textContent = input.validationMessage;
                        tooltip.style.display = 'block';
                    }
                } else {
                    input.classList.remove('input-error');
                    input.classList.add('input-success');
                    let tooltip = input.nextElementSibling;
                    if (tooltip && tooltip.classList.contains('tooltip-error')) {
                        tooltip.style.display = 'none';
                    }
                }
            });
        });

        const usernameInput = form.querySelector('input[name="username"]');
        usernameInput.addEventListener('input', () => {
            const value = usernameInput.value.trim();
            const pattern = /^[a-z0-9_.-]+$/;
            const tooltip = usernameInput.nextElementSibling;
            if (!pattern.test(value)) {
                usernameInput.setCustomValidity('El nombre de usuario solo puede contener minúsculas, números, guiones (-), guiones bajos (_) y puntos (.)');
                usernameInput.classList.add('input-error');
                usernameInput.classList.remove('input-success');
                if (tooltip && tooltip.classList.contains('tooltip-error')) {
                    tooltip.textContent = usernameInput.validationMessage;
                    tooltip.style.display = 'block';
                }
            } else {
                usernameInput.setCustomValidity('');
                usernameInput.classList.remove('input-error');
                usernameInput.classList.add('input-success');
                if (tooltip && tooltip.classList.contains('tooltip-error')) {
                    tooltip.style.display = 'none';
                }
            }
        });

        usernameInput.addEventListener('invalid', (e) => {
            e.preventDefault(); // Evita el tooltip nativo
            usernameInput.classList.add('input-error');
            usernameInput.classList.remove('input-success');
            let tooltip = usernameInput.nextElementSibling;
            if (tooltip && tooltip.classList.contains('tooltip-error')) {
                tooltip.textContent = usernameInput.validationMessage;
                tooltip.style.display = 'block';
            }
        });
    });
</script>
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
    <input type="number" name="edad" min="1" required autocomplete="off">
    <span class="tooltip-error"></span>
            </label>
        </div>
        <label>Nombre:
    <input type="text" name="nombre" required autocomplete="name">
    <span class="tooltip-error"></span>
        </label>
        <label>Nombre de usuario:
    <input type="text" name="username" required autocomplete="off">
    <span class="tooltip-error"></span>
        </label>
        <label>Email:
    <input type="email" name="email" required autocomplete="email">
    <span class="tooltip-error"></span>
        </label>
        <label>Contraseña:
    <input type="password" name="password" required autocomplete="new-password">
    <span class="tooltip-error"></span>
        </label>
        <label>Confirmar contraseña:
    <input type="password" name="confirmar_password" required autocomplete="new-password">
    <span class="tooltip-error"></span>
        </label>
        <button type="submit">Registrarse</button>
    </form>
    <p class="enlace-login">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</main>
</body>
</html>
<?php include 'inc/footer.php'; ?>

