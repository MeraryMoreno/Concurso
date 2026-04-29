<?php

require_once 'includes/auth.php';
validarRol(['administrador']);
require_once 'includes/ui.php';

require_once 'config/database.php';

$conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

$mensaje = '';
$tipo_mensaje = 'success';
$usuario_editar = null;

function existeUsuarioRegistrado($conexion, $usuario, $id_excluir = 0)
{
    $sql = "SELECT id FROM usuarios WHERE usuario = ?";

    if ($id_excluir > 0) {
        $sql .= " AND id != ?";
    }

    $stmt = $conexion->prepare($sql);

    if ($id_excluir > 0) {
        $stmt->bind_param('si', $usuario, $id_excluir);
    } else {
        $stmt->bind_param('s', $usuario);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows > 0;
    $stmt->close();

    return $existe;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' || $accion === 'actualizar') {
        $nombre_completo = trim($_POST['nombre_completo'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $id_rol = (int) ($_POST['id_rol'] ?? 0);
        $estado = trim($_POST['estado'] ?? '');

        if ($nombre_completo === '' || $usuario === '' || $id_rol <= 0 || $estado === '') {
            $mensaje = 'Completa todos los campos obligatorios.';
            $tipo_mensaje = 'danger';
        } else {
            if ($accion === 'guardar') {
                if ($password === '') {
                    $mensaje = 'La contrasena es obligatoria para un usuario nuevo.';
                    $tipo_mensaje = 'danger';
                } elseif (existeUsuarioRegistrado($conexion, $usuario)) {
                    $mensaje = 'El nombre de usuario ya existe.';
                    $tipo_mensaje = 'danger';
                } else {
                    $password_segura = password_hash($password, PASSWORD_DEFAULT);
                    $sql_guardar = "INSERT INTO usuarios (nombre_completo, usuario, password, id_rol, estado)
                                    VALUES (?, ?, ?, ?, ?)";
                    $stmt_guardar = $conexion->prepare($sql_guardar);
                    $stmt_guardar->bind_param('sssis', $nombre_completo, $usuario, $password_segura, $id_rol, $estado);

                    if ($stmt_guardar->execute()) {
                        header('Location: usuarios.php?mensaje=guardado');
                        exit;
                    }

                    $mensaje = 'No fue posible guardar el usuario.';
                    $tipo_mensaje = 'danger';
                    $stmt_guardar->close();
                }
            }

            if ($accion === 'actualizar') {
                $id = (int) ($_POST['id'] ?? 0);

                if (existeUsuarioRegistrado($conexion, $usuario, $id)) {
                    $mensaje = 'El nombre de usuario ya existe.';
                    $tipo_mensaje = 'danger';
                } else {
                    if ($password !== '') {
                        $password_segura = password_hash($password, PASSWORD_DEFAULT);
                        $sql_actualizar = "UPDATE usuarios
                                           SET nombre_completo = ?, usuario = ?, password = ?, id_rol = ?, estado = ?
                                           WHERE id = ?";
                        $stmt_actualizar = $conexion->prepare($sql_actualizar);
                        $stmt_actualizar->bind_param('sssisi', $nombre_completo, $usuario, $password_segura, $id_rol, $estado, $id);
                    } else {
                        $sql_actualizar = "UPDATE usuarios
                                           SET nombre_completo = ?, usuario = ?, id_rol = ?, estado = ?
                                           WHERE id = ?";
                        $stmt_actualizar = $conexion->prepare($sql_actualizar);
                        $stmt_actualizar->bind_param('ssisi', $nombre_completo, $usuario, $id_rol, $estado, $id);
                    }

                    if ($stmt_actualizar->execute()) {
                        header('Location: usuarios.php?mensaje=actualizado');
                        exit;
                    }

                    $mensaje = 'No fue posible actualizar el usuario.';
                    $tipo_mensaje = 'danger';
                    $stmt_actualizar->close();
                }
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id === (int) $_SESSION['id_usuario']) {
            $mensaje = 'No puedes eliminar tu propio usuario mientras tienes sesion iniciada.';
            $tipo_mensaje = 'danger';
        } else {
            $sql_eliminar = "DELETE FROM usuarios WHERE id = ?";
            $stmt_eliminar = $conexion->prepare($sql_eliminar);
            $stmt_eliminar->bind_param('i', $id);

            if ($stmt_eliminar->execute()) {
                header('Location: usuarios.php?mensaje=eliminado');
                exit;
            }

            $mensaje = 'No fue posible eliminar el usuario. Verifica si tiene informacion relacionada.';
            $tipo_mensaje = 'danger';
            $stmt_eliminar->close();
        }
    }
}

if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje = 'Usuario registrado correctamente.';
    }

    if ($_GET['mensaje'] === 'actualizado') {
        $mensaje = 'Usuario actualizado correctamente.';
    }

    if ($_GET['mensaje'] === 'eliminado') {
        $mensaje = 'Usuario eliminado correctamente.';
    }
}

if (isset($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];
    $sql_editar = "SELECT * FROM usuarios WHERE id = ?";
    $stmt_editar = $conexion->prepare($sql_editar);
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $usuario_editar = $resultado_editar->fetch_assoc();
    $stmt_editar->close();
}

$resultado_roles = $conexion->query("SELECT id, nombre FROM roles ORDER BY nombre ASC");

$sql_usuarios = "SELECT usuarios.*, roles.nombre AS nombre_rol
                 FROM usuarios
                 INNER JOIN roles ON usuarios.id_rol = roles.id
                 ORDER BY usuarios.nombre_completo ASC";
$resultado_usuarios = $conexion->query($sql_usuarios);

$titulo_pagina = 'Usuarios';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Gestion de usuarios</h1>
        <p class="text-muted mb-0">Aqui el administrador puede registrar y controlar los accesos del sistema.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>
</div>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>">
        <?php echo htmlspecialchars($mensaje); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $usuario_editar ? 'Editar usuario' : 'Nuevo usuario'; ?></h2>

                <form method="POST" action="">
                    <input type="hidden" name="accion" value="<?php echo $usuario_editar ? 'actualizar' : 'guardar'; ?>">

                    <?php if ($usuario_editar): ?>
                        <input type="hidden" name="id" value="<?php echo (int) $usuario_editar['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre_completo" class="form-label">Nombre completo</label>
                        <input type="text" name="nombre_completo" id="nombre_completo" class="form-control" required value="<?php echo htmlspecialchars($usuario_editar['nombre_completo'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" name="usuario" id="usuario" class="form-control" required value="<?php echo htmlspecialchars($usuario_editar['usuario'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contrasena <?php echo $usuario_editar ? '(solo si deseas cambiarla)' : ''; ?></label>
                        <input type="password" name="password" id="password" class="form-control" <?php echo $usuario_editar ? '' : 'required'; ?>>
                    </div>

                    <div class="mb-3">
                        <label for="id_rol" class="form-label">Rol</label>
                        <select name="id_rol" id="id_rol" class="form-select" required>
                            <option value="">Selecciona una opcion</option>
                            <?php if ($resultado_roles->num_rows > 0): ?>
                                <?php while ($rol_item = $resultado_roles->fetch_assoc()): ?>
                                    <option value="<?php echo (int) $rol_item['id']; ?>" <?php echo ((int) ($usuario_editar['id_rol'] ?? 0) === (int) $rol_item['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($rol_item['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="activo" <?php echo (($usuario_editar['estado'] ?? 'activo') === 'activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="inactivo" <?php echo (($usuario_editar['estado'] ?? '') === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $usuario_editar ? 'Actualizar usuario' : 'Guardar usuario'; ?>
                        </button>

                        <?php if ($usuario_editar): ?>
                            <a href="usuarios.php" class="btn btn-outline-secondary">Cancelar edicion</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Lista de usuarios</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_usuarios->num_rows > 0): ?>
                                <?php while ($fila_usuario = $resultado_usuarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($fila_usuario['nombre_completo']); ?></td>
                                        <td><?php echo htmlspecialchars($fila_usuario['usuario']); ?></td>
                                        <td><?php echo badgeEstado($fila_usuario['nombre_rol']); ?></td>
                                        <td><?php echo badgeEstado($fila_usuario['estado']); ?></td>
                                        <td>
                                            <a href="usuarios.php?editar=<?php echo (int) $fila_usuario['id']; ?>" class="btn btn-sm btn-warning">Editar</a>

                                            <?php if ((int) $fila_usuario['id'] !== (int) $_SESSION['id_usuario']): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo (int) $fila_usuario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este usuario?');">Eliminar</button>
                                                </form>
                                            <?php else: ?>
                                                <span class="badge text-bg-secondary">Sesion actual</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No hay usuarios registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conexion->close();
require_once 'includes/footer.php';
?>
