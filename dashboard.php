<?php

require_once 'includes/auth.php';
validarSesion();
require_once 'includes/ui.php';

$titulo_pagina = 'Panel principal';
require_once 'includes/header.php';

$rol = obtenerRolUsuario();
?>

<div class="dashboard-hero mb-4">
    <div class="row align-items-center g-4">
        <div class="col-lg-8">
            <span class="hero-label">Panel principal</span>
            <h1 class="hero-title mt-2 mb-2">Sistema de control de espacios academicos</h1>
            <p class="hero-text mb-3">
                Administra usuarios, espacios, horarios, incidencias y mantenimiento desde una sola plataforma.
            </p>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="hero-pill"><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></span>
                <?php echo badgeEstado($rol); ?>
            </div>
        </div>
        <div class="col-lg-4 text-lg-end">
            <a href="logout.php" class="btn btn-light btn-panel">Cerrar sesion</a>
        </div>
    </div>
</div>

<div class="section-block mb-4">
    <div class="section-header">
        <h2 class="section-title">Accesos rapidos</h2>
        <p class="section-text">Selecciona el modulo que deseas administrar o consultar.</p>
    </div>

    <div class="row g-3">
        <?php if ($rol === 'administrador'): ?>
            <div class="col-md-6 col-xl-4">
                <a href="usuarios.php" class="quick-card text-decoration-none">
                    <span class="quick-card-title">Usuarios</span>
                    <span class="quick-card-text">Gestion de cuentas y roles.</span>
                </a>
            </div>
            <div class="col-md-6 col-xl-4">
                <a href="espacios.php" class="quick-card text-decoration-none">
                    <span class="quick-card-title">Espacios</span>
                    <span class="quick-card-text">Alta, edicion y control de espacios.</span>
                </a>
            </div>
        <?php endif; ?>

        <div class="col-md-6 col-xl-4">
            <a href="horarios.php" class="quick-card text-decoration-none">
                <span class="quick-card-title">Horarios</span>
                <span class="quick-card-text">Asignacion y consulta de horarios.</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="incidencias.php" class="quick-card text-decoration-none">
                <span class="quick-card-title">Incidencias</span>
                <span class="quick-card-text">Registro de ausencias y fallas.</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="mantenimiento.php" class="quick-card text-decoration-none">
                <span class="quick-card-title">Mantenimiento</span>
                <span class="quick-card-text">Seguimiento de fallas y reparaciones.</span>
            </a>
        </div>
        <div class="col-md-6 col-xl-4">
            <a href="disponibilidad.php" class="quick-card text-decoration-none">
                <span class="quick-card-title">Disponibilidad</span>
                <span class="quick-card-text">Consulta del estado actual de espacios.</span>
            </a>
        </div>
    </div>
</div>

<div class="section-block mb-4">
    <div class="section-header">
        <h2 class="section-title">Roles del sistema</h2>
        <p class="section-text">Cada rol cuenta con permisos definidos para mantener el control del sistema.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card-resumen card-rol">
                <span class="mini-label">Rol principal</span>
                <h2 class="h5">Administrador</h2>
                <p class="mb-0">Gestiona usuarios, espacios, horarios, incidencias y mantenimiento.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-resumen card-rol">
                <span class="mini-label">Rol operativo</span>
                <h2 class="h5">Area academica</h2>
                <p class="mb-0">Registra horarios, consulta espacios y revisa disponibilidad.</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card-resumen card-rol">
                <span class="mini-label">Rol de seguimiento</span>
                <h2 class="h5">Prefecto</h2>
                <p class="mb-0">Consulta horarios, registra incidencias y da seguimiento a mantenimiento.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
