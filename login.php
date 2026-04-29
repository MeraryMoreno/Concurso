<?php

session_start();

require_once 'config/database.php';
require_once 'includes/ui.php';

if (isset($_SESSION['id_usuario'])) {
    header('Location: dashboard.php');
    exit;
}

$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($usuario === '' || $password === '') {
        $mensaje_error = 'Completa todos los campos.';
    } else {
        $conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

        $sql = "SELECT usuarios.id, usuarios.nombre_completo, usuarios.usuario, usuarios.password, roles.nombre AS rol
                FROM usuarios
                INNER JOIN roles ON usuarios.id_rol = roles.id
                WHERE usuarios.usuario = ? AND usuarios.estado = 'activo'";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('s', $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $fila = $resultado->fetch_assoc();

            if (password_verify($password, $fila['password'])) {
                $_SESSION['id_usuario'] = $fila['id'];
                $_SESSION['usuario'] = $fila['usuario'];
                $_SESSION['nombre_completo'] = $fila['nombre_completo'];
                $_SESSION['rol'] = $fila['rol'];

                header('Location: dashboard.php');
                exit;
            }
        }

        $mensaje_error = 'Usuario o contrasena incorrectos.';
        $stmt->close();
        $conexion->close();
    }
}

$titulo_pagina = 'Iniciar sesion';
require_once 'includes/header.php';
?>

<div class="login-wrapper">
    <div class="card shadow-sm login-card">
        <div class="row g-0">
            <div class="col-lg-5 login-side">
                <div class="login-side-content">
                    <span class="hero-label">Acceso al sistema</span>
                    <h1 class="login-title">Sistema de Espacios</h1>
                    <p class="login-text">
                        Plataforma para controlar espacios academicos, horarios, incidencias y mantenimiento.
                    </p>

                    <div class="login-demo-box">
                        <strong>Usuarios de prueba</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>admin</strong> / admin123</li>
                            <li><strong>academica</strong> / academica123</li>
                            <li><strong>prefecto</strong> / prefecto123</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="h4 mb-2">Iniciar sesion</h2>
                    <p class="text-muted mb-4">Ingresa tus credenciales para continuar.</p>

                    <?php if ($mensaje_error !== ''): ?>
                        <div class="alert alert-danger"><?php echo h($mensaje_error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" name="usuario" id="usuario" class="form-control form-control-lg" required>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Contrasena</label>
                            <input type="password" name="password" id="password" class="form-control form-control-lg" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Entrar al sistema</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
