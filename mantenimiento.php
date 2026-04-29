<?php

require_once 'includes/auth.php';
validarRol(['administrador', 'area_academica', 'prefecto']);
require_once 'includes/ui.php';

require_once 'config/database.php';

$conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

$rol = obtenerRolUsuario();
$puede_editar = in_array($rol, ['administrador', 'prefecto'], true);

$mensaje = '';
$tipo_mensaje = 'success';
$mantenimiento_editar = null;

function actualizarEstadoEspacioPorMantenimiento($conexion, $id_espacio)
{
    $nuevo_estado_espacio = 'disponible';

    $sql_revision = "SELECT id
                     FROM mantenimientos
                     WHERE id_espacio = ?
                     AND estado IN ('pendiente', 'en_proceso')
                     LIMIT 1";
    $stmt_revision = $conexion->prepare($sql_revision);
    $stmt_revision->bind_param('i', $id_espacio);
    $stmt_revision->execute();
    $resultado_revision = $stmt_revision->get_result();

    if ($resultado_revision->num_rows > 0) {
        $nuevo_estado_espacio = 'mantenimiento';
    }

    $stmt_revision->close();

    $sql_espacio = "UPDATE espacios SET estado = ? WHERE id = ?";
    $stmt_espacio = $conexion->prepare($sql_espacio);
    $stmt_espacio->bind_param('si', $nuevo_estado_espacio, $id_espacio);
    $stmt_espacio->execute();
    $stmt_espacio->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $puede_editar) {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' || $accion === 'actualizar') {
        $id_espacio = (int) ($_POST['id_espacio'] ?? 0);
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado = trim($_POST['estado'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($id_espacio <= 0 || $descripcion === '' || $estado === '') {
            $mensaje = 'Completa todos los campos obligatorios.';
            $tipo_mensaje = 'danger';
        } else {
            if ($accion === 'guardar') {
                $sql_guardar = "INSERT INTO mantenimientos (id_espacio, descripcion, estado, observaciones, id_usuario_registro)
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_guardar = $conexion->prepare($sql_guardar);
                $stmt_guardar->bind_param('isssi', $id_espacio, $descripcion, $estado, $observaciones, $_SESSION['id_usuario']);

                if ($stmt_guardar->execute()) {
                    actualizarEstadoEspacioPorMantenimiento($conexion, $id_espacio);
                    header('Location: mantenimiento.php?mensaje=guardado');
                    exit;
                }

                $mensaje = 'No fue posible guardar el mantenimiento.';
                $tipo_mensaje = 'danger';
                $stmt_guardar->close();
            }

            if ($accion === 'actualizar') {
                $id = (int) ($_POST['id'] ?? 0);
                $id_espacio_anterior = 0;

                $sql_mantenimiento_actual = "SELECT id_espacio FROM mantenimientos WHERE id = ?";
                $stmt_mantenimiento_actual = $conexion->prepare($sql_mantenimiento_actual);
                $stmt_mantenimiento_actual->bind_param('i', $id);
                $stmt_mantenimiento_actual->execute();
                $resultado_mantenimiento_actual = $stmt_mantenimiento_actual->get_result();
                $mantenimiento_actual = $resultado_mantenimiento_actual->fetch_assoc();
                $stmt_mantenimiento_actual->close();

                if ($mantenimiento_actual) {
                    $id_espacio_anterior = (int) $mantenimiento_actual['id_espacio'];
                }

                $sql_actualizar = "UPDATE mantenimientos
                                   SET id_espacio = ?, descripcion = ?, estado = ?, observaciones = ?
                                   WHERE id = ?";
                $stmt_actualizar = $conexion->prepare($sql_actualizar);
                $stmt_actualizar->bind_param('isssi', $id_espacio, $descripcion, $estado, $observaciones, $id);

                if ($stmt_actualizar->execute()) {
                    if ($id_espacio_anterior > 0 && $id_espacio_anterior !== $id_espacio) {
                        actualizarEstadoEspacioPorMantenimiento($conexion, $id_espacio_anterior);
                    }

                    actualizarEstadoEspacioPorMantenimiento($conexion, $id_espacio);
                    header('Location: mantenimiento.php?mensaje=actualizado');
                    exit;
                }

                $mensaje = 'No fue posible actualizar el mantenimiento.';
                $tipo_mensaje = 'danger';
                $stmt_actualizar->close();
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);

        $sql_buscar = "SELECT id_espacio FROM mantenimientos WHERE id = ?";
        $stmt_buscar = $conexion->prepare($sql_buscar);
        $stmt_buscar->bind_param('i', $id);
        $stmt_buscar->execute();
        $resultado_buscar = $stmt_buscar->get_result();
        $fila_buscar = $resultado_buscar->fetch_assoc();
        $stmt_buscar->close();

        $sql_eliminar = "DELETE FROM mantenimientos WHERE id = ?";
        $stmt_eliminar = $conexion->prepare($sql_eliminar);
        $stmt_eliminar->bind_param('i', $id);

        if ($stmt_eliminar->execute()) {
            if ($fila_buscar) {
                actualizarEstadoEspacioPorMantenimiento($conexion, (int) $fila_buscar['id_espacio']);
            }

            header('Location: mantenimiento.php?mensaje=eliminado');
            exit;
        }

        $mensaje = 'No fue posible eliminar el mantenimiento.';
        $tipo_mensaje = 'danger';
        $stmt_eliminar->close();
    }
}

if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje = 'Mantenimiento registrado correctamente.';
    }

    if ($_GET['mensaje'] === 'actualizado') {
        $mensaje = 'Mantenimiento actualizado correctamente.';
    }

    if ($_GET['mensaje'] === 'eliminado') {
        $mensaje = 'Mantenimiento eliminado correctamente.';
    }
}

if (isset($_GET['editar']) && $puede_editar) {
    $id_editar = (int) $_GET['editar'];
    $sql_editar = "SELECT * FROM mantenimientos WHERE id = ?";
    $stmt_editar = $conexion->prepare($sql_editar);
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $mantenimiento_editar = $resultado_editar->fetch_assoc();
    $stmt_editar->close();
}

$resultado_espacios = $conexion->query("SELECT id, nombre, estado FROM espacios ORDER BY nombre ASC");

$sql_mantenimientos = "SELECT mantenimientos.*, espacios.nombre AS nombre_espacio, usuarios.nombre_completo AS usuario_registro
                       FROM mantenimientos
                       INNER JOIN espacios ON mantenimientos.id_espacio = espacios.id
                       INNER JOIN usuarios ON mantenimientos.id_usuario_registro = usuarios.id
                       ORDER BY mantenimientos.fecha_reporte DESC";

$resultado_mantenimientos = $conexion->query($sql_mantenimientos);

$titulo_pagina = 'Mantenimiento';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Mantenimiento</h1>
        <p class="text-muted mb-0">Aqui puedes registrar y consultar el seguimiento de fallas en los espacios.</p>
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

<?php if (!$puede_editar): ?>
    <div class="alert alert-info">
        Tu rol solo puede consultar los mantenimientos registrados.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if ($puede_editar): ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?php echo $mantenimiento_editar ? 'Editar mantenimiento' : 'Nuevo mantenimiento'; ?></h2>

                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="<?php echo $mantenimiento_editar ? 'actualizar' : 'guardar'; ?>">

                        <?php if ($mantenimiento_editar): ?>
                            <input type="hidden" name="id" value="<?php echo (int) $mantenimiento_editar['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="id_espacio" class="form-label">Espacio</label>
                            <select name="id_espacio" id="id_espacio" class="form-select" required>
                                <option value="">Selecciona una opcion</option>
                                <?php if ($resultado_espacios->num_rows > 0): ?>
                                    <?php while ($espacio = $resultado_espacios->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $espacio['id']; ?>" <?php echo ((int) ($mantenimiento_editar['id_espacio'] ?? 0) === (int) $espacio['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($espacio['nombre'] . ' - ' . $espacio['estado']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($mantenimiento_editar['descripcion'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="pendiente" <?php echo (($mantenimiento_editar['estado'] ?? 'pendiente') === 'pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="en_proceso" <?php echo (($mantenimiento_editar['estado'] ?? '') === 'en_proceso') ? 'selected' : ''; ?>>En proceso</option>
                                <option value="resuelto" <?php echo (($mantenimiento_editar['estado'] ?? '') === 'resuelto') ? 'selected' : ''; ?>>Resuelto</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control" rows="3"><?php echo htmlspecialchars($mantenimiento_editar['observaciones'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $mantenimiento_editar ? 'Actualizar mantenimiento' : 'Guardar mantenimiento'; ?>
                            </button>

                            <?php if ($mantenimiento_editar): ?>
                                <a href="mantenimiento.php" class="btn btn-outline-secondary">Cancelar edicion</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="<?php echo $puede_editar ? 'col-lg-8' : 'col-lg-12'; ?>">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Lista de mantenimientos</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Espacio</th>
                                <th>Descripcion</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Observaciones</th>
                                <th>Registrado por</th>
                                <?php if ($puede_editar): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_mantenimientos->num_rows > 0): ?>
                                <?php while ($mantenimiento = $resultado_mantenimientos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($mantenimiento['nombre_espacio']); ?></td>
                                        <td><?php echo htmlspecialchars($mantenimiento['descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($mantenimiento['fecha_reporte']); ?></td>
                                        <td><?php echo badgeEstado($mantenimiento['estado']); ?></td>
                                        <td><?php echo htmlspecialchars($mantenimiento['observaciones']); ?></td>
                                        <td><?php echo htmlspecialchars($mantenimiento['usuario_registro']); ?></td>
                                        <?php if ($puede_editar): ?>
                                            <td>
                                                <a href="mantenimiento.php?editar=<?php echo (int) $mantenimiento['id']; ?>" class="btn btn-sm btn-warning">Editar</a>

                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo (int) $mantenimiento['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este mantenimiento?');">Eliminar</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $puede_editar ? '7' : '6'; ?>" class="text-center">No hay mantenimientos registrados.</td>
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
