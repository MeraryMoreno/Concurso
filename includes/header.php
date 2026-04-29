<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$rol_sesion = $_SESSION['rol'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Sistema de Espacios'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Sistema de Espacios</a>
            <?php if (isset($_SESSION['id_usuario'])): ?>
                <div class="d-flex align-items-center gap-2 flex-wrap text-white">
                    <a href="dashboard.php" class="btn btn-sm btn-outline-light">Inicio</a>
                    <?php if ($rol_sesion === 'administrador'): ?>
                        <a href="usuarios.php" class="btn btn-sm btn-outline-light">Usuarios</a>
                        <a href="espacios.php" class="btn btn-sm btn-outline-light">Espacios</a>
                    <?php endif; ?>
                    <?php if (in_array($rol_sesion, ['administrador', 'area_academica', 'prefecto'], true)): ?>
                        <a href="horarios.php" class="btn btn-sm btn-outline-light">Horarios</a>
                        <a href="incidencias.php" class="btn btn-sm btn-outline-light">Incidencias</a>
                        <a href="mantenimiento.php" class="btn btn-sm btn-outline-light">Mantenimiento</a>
                        <a href="disponibilidad.php" class="btn btn-sm btn-outline-light">Disponibilidad</a>
                    <?php endif; ?>
                    <span class="ms-2"><?php echo htmlspecialchars($_SESSION['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <a href="logout.php" class="btn btn-sm btn-light">Cerrar sesion</a>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container py-4">
