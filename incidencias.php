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
$incidencia_editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $puede_editar) {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' || $accion === 'actualizar') {
        $id_espacio = (int) ($_POST['id_espacio'] ?? 0);
        $tipo = trim($_POST['tipo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $estado = trim($_POST['estado'] ?? '');

        if ($id_espacio <= 0 || $tipo === '' || $descripcion === '' || $estado === '') {
            $mensaje = 'Completa todos los campos correctamente.';
            $tipo_mensaje = 'danger';
        } else {
            if ($accion === 'guardar') {
                $sql_guardar = "INSERT INTO incidencias (id_espacio, tipo, descripcion, id_usuario_registro, estado)
                                VALUES (?, ?, ?, ?, ?)";
                $stmt_guardar = $conexion->prepare($sql_guardar);
                $stmt_guardar->bind_param('issis', $id_espacio, $tipo, $descripcion, $_SESSION['id_usuario'], $estado);

                if ($stmt_guardar->execute()) {
                    header('Location: incidencias.php?mensaje=guardado');
                    exit;
                }

                $mensaje = 'No fue posible guardar la incidencia.';
                $tipo_mensaje = 'danger';
                $stmt_guardar->close();
            }

            if ($accion === 'actualizar') {
                $id = (int) ($_POST['id'] ?? 0);
                $sql_actualizar = "UPDATE incidencias
                                   SET id_espacio = ?, tipo = ?, descripcion = ?, estado = ?
                                   WHERE id = ?";
                $stmt_actualizar = $conexion->prepare($sql_actualizar);
                $stmt_actualizar->bind_param('isssi', $id_espacio, $tipo, $descripcion, $estado, $id);

                if ($stmt_actualizar->execute()) {
                    header('Location: incidencias.php?mensaje=actualizado');
                    exit;
                }

                $mensaje = 'No fue posible actualizar la incidencia.';
                $tipo_mensaje = 'danger';
                $stmt_actualizar->close();
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        $sql_eliminar = "DELETE FROM incidencias WHERE id = ?";
        $stmt_eliminar = $conexion->prepare($sql_eliminar);
        $stmt_eliminar->bind_param('i', $id);

        if ($stmt_eliminar->execute()) {
            header('Location: incidencias.php?mensaje=eliminado');
            exit;
        }

        $mensaje = 'No fue posible eliminar la incidencia.';
        $tipo_mensaje = 'danger';
        $stmt_eliminar->close();
    }
}

if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje = 'Incidencia registrada correctamente.';
    }

    if ($_GET['mensaje'] === 'actualizado') {
        $mensaje = 'Incidencia actualizada correctamente.';
    }

    if ($_GET['mensaje'] === 'eliminado') {
        $mensaje = 'Incidencia eliminada correctamente.';
    }
}

if (isset($_GET['editar']) && $puede_editar) {
    $id_editar = (int) $_GET['editar'];
    $sql_editar = "SELECT * FROM incidencias WHERE id = ?";
    $stmt_editar = $conexion->prepare($sql_editar);
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $incidencia_editar = $resultado_editar->fetch_assoc();
    $stmt_editar->close();
}

$resultado_espacios = $conexion->query("SELECT id, nombre FROM espacios ORDER BY nombre ASC");

$sql_incidencias = "SELECT incidencias.*, espacios.nombre AS nombre_espacio, usuarios.nombre_completo AS usuario_registro
                    FROM incidencias
                    INNER JOIN espacios ON incidencias.id_espacio = espacios.id
                    INNER JOIN usuarios ON incidencias.id_usuario_registro = usuarios.id
                    ORDER BY incidencias.fecha_incidencia DESC";

$resultado_incidencias = $conexion->query($sql_incidencias);

$titulo_pagina = 'Incidencias';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Incidencias</h1>
        <p class="text-muted mb-0">Aqui puedes registrar y consultar incidencias relacionadas con la operacion academica.</p>
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
        Tu rol solo puede consultar las incidencias registradas.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if ($puede_editar): ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?php echo $incidencia_editar ? 'Editar incidencia' : 'Nueva incidencia'; ?></h2>

                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="<?php echo $incidencia_editar ? 'actualizar' : 'guardar'; ?>">

                        <?php if ($incidencia_editar): ?>
                            <input type="hidden" name="id" value="<?php echo (int) $incidencia_editar['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="id_espacio" class="form-label">Espacio</label>
                            <select name="id_espacio" id="id_espacio" class="form-select" required>
                                <option value="">Selecciona una opcion</option>
                                <?php if ($resultado_espacios->num_rows > 0): ?>
                                    <?php while ($espacio = $resultado_espacios->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $espacio['id']; ?>" <?php echo ((int) ($incidencia_editar['id_espacio'] ?? 0) === (int) $espacio['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($espacio['nombre']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo de incidencia</label>
                            <select name="tipo" id="tipo" class="form-select" required>
                                <option value="">Selecciona una opcion</option>
                                <option value="ausencia_docente" <?php echo (($incidencia_editar['tipo'] ?? '') === 'ausencia_docente') ? 'selected' : ''; ?>>Ausencia de docente</option>
                                <option value="falla_espacio" <?php echo (($incidencia_editar['tipo'] ?? '') === 'falla_espacio') ? 'selected' : ''; ?>>Falla del espacio</option>
                                <option value="otro" <?php echo (($incidencia_editar['tipo'] ?? '') === 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea name="descripcion" id="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($incidencia_editar['descripcion'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="reportada" <?php echo (($incidencia_editar['estado'] ?? 'reportada') === 'reportada') ? 'selected' : ''; ?>>Reportada</option>
                                <option value="atendida" <?php echo (($incidencia_editar['estado'] ?? '') === 'atendida') ? 'selected' : ''; ?>>Atendida</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $incidencia_editar ? 'Actualizar incidencia' : 'Guardar incidencia'; ?>
                            </button>

                            <?php if ($incidencia_editar): ?>
                                <a href="incidencias.php" class="btn btn-outline-secondary">Cancelar edicion</a>
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
                <h2 class="h5 mb-3">Lista de incidencias</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Espacio</th>
                                <th>Tipo</th>
                                <th>Descripcion</th>
                                <th>Fecha</th>
                                <th>Registrado por</th>
                                <th>Estado</th>
                                <?php if ($puede_editar): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_incidencias->num_rows > 0): ?>
                                <?php while ($incidencia = $resultado_incidencias->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($incidencia['nombre_espacio']); ?></td>
                                        <td><?php echo badgeEstado($incidencia['tipo']); ?></td>
                                        <td><?php echo htmlspecialchars($incidencia['descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($incidencia['fecha_incidencia']); ?></td>
                                        <td><?php echo htmlspecialchars($incidencia['usuario_registro']); ?></td>
                                        <td><?php echo badgeEstado($incidencia['estado']); ?></td>
                                        <?php if ($puede_editar): ?>
                                            <td>
                                                <a href="incidencias.php?editar=<?php echo (int) $incidencia['id']; ?>" class="btn btn-sm btn-warning">Editar</a>

                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo (int) $incidencia['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar esta incidencia?');">Eliminar</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $puede_editar ? '7' : '6'; ?>" class="text-center">No hay incidencias registradas.</td>
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
