<?php
require_once 'config.php';

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pais = trim($_POST['pais'] ?? '');
 
  $fecha_nacimiento = $_POST['edad'] ?? '';
$nacimiento = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);

$edad = (new DateTime())->diff($nacimiento)->y;

    $nombre = trim($_POST['nombre'] ?? '');
    $username = strtolower(trim($_POST['username'] ?? ''));
    $username = preg_replace("/[^a-z0-9_.-]/", "", $username);
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
  

    // Validaciones básicas
    if ($pais === '') $errores[] = 'El país es obligatorio';
    if ($edad < 18) $errores[] = 'Debes tener al menos 18 años para registrarte';
    if ($nombre === '') $errores[] = 'El nombre es obligatorio';
    if (!preg_match("/^[a-z0-9_.-]+$/", $username)) $errores[] = 'El nombre de usuario solo puede contener minúsculas, números, guiones (-), guiones bajos (_) y puntos (.)';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Email no válido';
    if (
    strlen($password) < 6 ||
    !preg_match('/[A-Z]/', $password) ||
    !preg_match('/[a-z]/', $password) ||
    !preg_match('/\d/', $password) ||
    !preg_match('/[^a-zA-Z\d]/', $password)
) {
    $errores[] = 'La contraseña debe tener al menos 6 caracteres e incluir una mayúscula, una minúscula, un número y un carácter especial';
}

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
        $numero = rand(1, 10);
        $avatar = "/img/avatars/avatar_default ($numero).png";


        $stmt = $db->prepare('INSERT INTO usuarios (pais, fecha_nacimiento, nombre, username, email, contrasena, foto_perfil) VALUES (?, ?, ?, ?, ?, ?, ?)');
        try {
            $stmt->execute([$pais, $fecha_nacimiento, $nombre, $username, $email, $contrasena_hash, $avatar]);
            
            session_start();
    $_SESSION['usuario'] = $username;
            
            header('Location: /usuarios/mi_cuenta.php');
            exit;
        } catch (PDOException $e) {
            $errores[] = 'Error al registrar el usuario: ' . $e->getMessage();
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
        
        const paisSelect = document.querySelector('select[name="pais"]');
paisSelect.addEventListener('change', () => {
    const tooltip = paisSelect.parentElement.querySelector('.tooltip-error');

    if (!paisSelect.value) {
        paisSelect.setCustomValidity('El país es obligatorio');
        paisSelect.classList.add('input-error');
        paisSelect.classList.remove('input-success');
        if (tooltip) {
            tooltip.textContent = paisSelect.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        paisSelect.setCustomValidity('');
        paisSelect.classList.remove('input-error');
        paisSelect.classList.add('input-success');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }
});

        
        const edadInput = document.querySelector('input[name="edad"]');
edadInput.addEventListener('input', () => {
    const tooltip = edadInput.parentElement.querySelector('.tooltip-error');
    const nacimiento = new Date(edadInput.value);
    const hoy = new Date();
    const edad = hoy.getFullYear() - nacimiento.getFullYear();
    const mes = hoy.getMonth() - nacimiento.getMonth();

    const esMenor = edad < 18 || (edad === 18 && mes < 0);

    if (isNaN(nacimiento) || esMenor) {
        edadInput.setCustomValidity('Debes ser mayor de edad para registrarte');
        edadInput.classList.add('input-error');
        edadInput.classList.remove('input-success');
        if (tooltip && tooltip.classList.contains('tooltip-error')) {
            tooltip.textContent = edadInput.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        edadInput.setCustomValidity('');
        edadInput.classList.remove('input-error');
        edadInput.classList.add('input-success');
        if (tooltip && tooltip.classList.contains('tooltip-error')) {
            tooltip.style.display = 'none';
        }
    }
});

        
        const nombreInput = document.querySelector('input[name="nombre"]');
nombreInput.addEventListener('input', () => {
    const value = nombreInput.value.trim();
    const tooltip = nombreInput.parentElement.querySelector('.tooltip-error');

    if (!/^[A-Za-zÁÉÍÓÚÑáéíóúñ\s]+$/.test(value)) {
        nombreInput.setCustomValidity('El nombre solo puede contener letras');
        nombreInput.classList.add('input-error');
        nombreInput.classList.remove('input-success');
        if (tooltip) {
            tooltip.textContent = nombreInput.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        nombreInput.setCustomValidity('');
        nombreInput.classList.remove('input-error');
        nombreInput.classList.add('input-success');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }
});

const usernameInput = document.querySelector('input[name="username"]');
usernameInput.addEventListener('input', () => {
    const value = usernameInput.value.trim();
    const tooltip = usernameInput.parentElement.querySelector('.tooltip-error');
    const pattern = /^[a-z0-9_.-]+$/;

    if (!pattern.test(value)) {
        usernameInput.setCustomValidity('Solo minúsculas, números y - _ . sin espacios');
        usernameInput.classList.add('input-error');
        usernameInput.classList.remove('input-success');
        if (tooltip) {
            tooltip.textContent = usernameInput.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        usernameInput.setCustomValidity('');
        usernameInput.classList.remove('input-error');
        usernameInput.classList.add('input-success');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }
});

const emailInput = document.querySelector('input[name="email"]');
emailInput.addEventListener('input', () => {
    const value = emailInput.value.trim();
    const tooltip = emailInput.parentElement.querySelector('.tooltip-error');

    if (!/^\S+@\S+\.\S+$/.test(value)) {
        emailInput.setCustomValidity('Introduce un correo electrónico válido');
        emailInput.classList.add('input-error');
        emailInput.classList.remove('input-success');
        if (tooltip) {
            tooltip.textContent = emailInput.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        emailInput.setCustomValidity('');
        emailInput.classList.remove('input-error');
        emailInput.classList.add('input-success');
        if (tooltip) {
            tooltip.style.display = 'none';
        }
    }
});

        
        
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const input = document.getElementById(btn.dataset.target);
            if (input) {
                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';
                const svgVisible = '<img src="./img/ojo-visible.svg" alt="Mostrar" class="mostrar-boton">';
                const svgHidden = '<img src="./img/ojo-oculto.svg" alt="Ocultar" class="mostrar-boton">';

              
              btn.innerHTML = isPassword ? svgHidden : svgVisible;
            }
        });
    });
    const form = document.querySelector('form');

        const passwordInput = document.querySelector('input[name="password"]');
        passwordInput.addEventListener('input', () => {
    const value = passwordInput.value;
    const tooltip = passwordInput.parentElement.querySelector('.tooltip-error');
    const hasLength = value.length >= 6;
    const hasUpper = /[A-Z]/.test(value);
    const hasLower = /[a-z]/.test(value);
    const hasNumber = /\d/.test(value);
    const hasSpecial = /[^a-zA-Z\d]/.test(value);

    if (!(hasLength && hasUpper && hasLower && hasNumber && hasSpecial)) {
        passwordInput.setCustomValidity('Debe contener al menos 6 caracteres, una mayúscula, una minúscula, un número y un carácter especial');
        passwordInput.classList.add('input-error');
        passwordInput.classList.remove('input-success');
        if (tooltip && tooltip.classList.contains('tooltip-error')) {
            tooltip.textContent = passwordInput.validationMessage;
            tooltip.style.display = 'block';
        }
    } else {
        passwordInput.setCustomValidity('');
        passwordInput.classList.remove('input-error');
        passwordInput.classList.add('input-success');
        if (tooltip && tooltip.classList.contains('tooltip-error')) {
            tooltip.style.display = 'none';
        }
    }
});

        const confirmPasswordInput = document.querySelector('input[name="confirmar_password"]');
        const checkPasswordMatch = () => {
    const passwordValue = passwordInput.value;
    const confirmValue = confirmPasswordInput.value;
    const tooltip = confirmPasswordInput.parentElement.querySelector('.tooltip-error');

    if (confirmValue !== '') {
        if (confirmValue !== passwordValue) {
            confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
            confirmPasswordInput.classList.add('input-error');
            confirmPasswordInput.classList.remove('input-success');
            if (tooltip && tooltip.classList.contains('tooltip-error')) {
                tooltip.textContent = confirmPasswordInput.validationMessage;
                tooltip.style.display = 'block';
            }
        } else {
            confirmPasswordInput.setCustomValidity('');
            confirmPasswordInput.classList.remove('input-error');
            confirmPasswordInput.classList.add('input-success');
            if (tooltip && tooltip.classList.contains('tooltip-error')) {
                tooltip.style.display = 'none';
            }
        }
    } else {
        confirmPasswordInput.setCustomValidity('');
        confirmPasswordInput.classList.remove('input-error');
        confirmPasswordInput.classList.remove('input-success');
        if (tooltip && tooltip.classList.contains('tooltip-error')) {
            tooltip.style.display = 'none';
        }
    }
};
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
        passwordInput.addEventListener('input', checkPasswordMatch);

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
                <span class="tooltip-error"></span>
            </label>
            <label>Edad:
    <input type="date" name="edad" min="18" required autocomplete="off">
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
    <input type="password" name="password" id="password" required autocomplete="new-password">
    <button type="button" class="toggle-password" data-target="password">
      <img src="./img/ojo-visible.svg" alt="Mostrar" class="mostrar-boton">
</button>
    <span class="tooltip-error"></span>
        </label>
        <label>Confirmar contraseña:
    <input type="password" name="confirmar_password" id="confirmar_password" required autocomplete="new-password">

    <button type="button" class="toggle-password" data-target="confirmar_password">
      <img src="./img/ojo-visible.svg" alt="Mostrar" class="mostrar-boton">
</button>
    <span class="tooltip-error"></span>
        </label>
        <button type="submit">Registrarse</button>
    </form>
    <p class="enlace-login">¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</main>
</body>
</html>


<?php include 'inc/footer.php'; ?>


