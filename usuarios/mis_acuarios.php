<?php
require_once 'config.php';
require_once 'inc/auth.php';
include 'inc/header.php';

// Obtener acuarios del usuario
$stmt = $db->prepare("SELECT * FROM acuarios WHERE usuario_id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$acuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eliminar acuario si se solicita
    if (isset($_POST['eliminar']) && is_numeric($_POST['eliminar'])) {
        $stmt = $db->prepare("DELETE FROM acuarios WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$_POST['eliminar'], $_SESSION['usuario_id']]);
        header("Location: mis_acuarios.php");
        exit;
    }
}
?>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Acuarios</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<main class="mis-acuarios">
    <h1>Mis Acuarios</h1>
    <a href="./config_acuario.php" class="boton">➕ Añadir nuevo acuario</a>
    <ul class="lista-acuarios">
        <?php foreach ($acuarios as $a): ?>
            <li>
                <strong><?= htmlspecialchars($a['nombre']) ?></strong> (<?= ucfirst(str_replace('_', ' ', $a['tipo'])) ?>)
                <a href="configuracion_acuario.php?id=<?= $a['id'] ?>">Editar</a>
                <form method="POST" onsubmit="return confirm('¿Eliminar este acuario?')" style="display:inline">
                    <input type="hidden" name="eliminar" value="<?= $a['id'] ?>">
                    <button type="submit" class="eliminar">🗑️ Eliminar</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
</main>
</body>
</html>
<?php include 'inc/footer.php'; ?>
