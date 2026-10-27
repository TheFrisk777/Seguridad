<?php
require_once __DIR__.'/bootstrap.php';
require_user();
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi cuenta Bugambilia</title><link rel="stylesheet" href="../styles/seguridad.css"></head>
<body><main class="panel"><h1>Mi cuenta</h1><p>Bienvenido, <?= h($_SESSION['name']) ?>.</p><p>Tu sesión está activa. Se cierra tras 15 minutos de inactividad o una hora de duración.</p>
<p><a href="../index.html">Volver al inicio</a> · <a href="../paginas/privacidad.html">Privacidad</a></p>
<form action="logout.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><button>Cerrar sesión</button></form>
<details><summary>Eliminar mi cuenta</summary><p>Se eliminarán nombre, correo y hash de contraseña de la cuenta de demostración. Confirma con tu contraseña actual.</p>
<form action="eliminar.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><label>Contraseña actual <input type="password" name="contraseña" autocomplete="current-password" required></label><button>Eliminar cuenta</button></form></details>
</main></body></html>
