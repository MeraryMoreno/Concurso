<?php

require_once 'includes/auth.php';
validarRol(['administrador', 'area_academica', 'prefecto']);
require_once 'includes/ui.php';

require_once 'config/database.php';

$conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

$rol = obtenerRolUsuario();
$puede_editar = in_array($rol, ['administrador', 'area_academica'], true);

$mensaje = '';
$tipo_mensaje = 'success';
$horario_editar = null;

function existeConflictoHorario($conexion, $id_espacio, $dia_semana, $hora_inicio, $hora_fin, $id_excluir = 0)
{
    $sql = "SELECT id
            FROM horarios
            WHERE id_espacio = ?
            AND dia_semana = ?
            AND estado = 'activo'
            AND hora_inicio < ?
            AND hora_fin > ?";

    if ($id_excluir > 0) {
        $sql .= " AND id != ?";
    }

    $stmt = $conexion->prepare($sql);

    if ($id_excluir > 0) {
        $stmt->bind_param('isssi', $id_espacio, $dia_semana, $hora_fin, $hora_inicio, $id_excluir);
    } else {
        $stmt->bind_param('isss', $id_espacio, $dia_semana, $hora_fin, $hora_inicio);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $existe = $resultado->num_rows > 0;
    $stmt->close();

    return $existe;
}

function espacioEnMantenimiento($conexion, $id_espacio)
{
    $sql = "SELECT id FROM espacios WHERE id = ? AND estado = 'mantenimiento'";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id_espacio);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $en_mantenimiento = $resultado->num_rows > 0;
    $stmt->close();

    return $en_mantenimiento;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $puede_editar) {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' || $accion === 'actualizar') {
        $id_espacio = (int) ($_POST['id_espacio'] ?? 0);
        $grupo_carrera = trim($_POST['grupo_carrera'] ?? '');
        $materia_actividad = trim($_POST['materia_actividad'] ?? '');
        $docente = trim($_POST['docente'] ?? '');
        $dia_semana = trim($_POST['dia_semana'] ?? '');
        $hora_inicio = trim($_POST['hora_inicio'] ?? '');
        $hora_fin = trim($_POST['hora_fin'] ?? '');
        $estado = trim($_POST['estado'] ?? '');

        if (
            $id_espacio <= 0 || $grupo_carrera === '' || $materia_actividad === '' ||
            $docente === '' || $dia_semana === '' || $hora_inicio === '' ||
            $hora_fin === '' || $estado === ''
        ) {
            $mensaje = 'Completa todos los campos correctamente.';
            $tipo_mensaje = 'danger';
        } elseif ($hora_inicio >= $hora_fin) {
            $mensaje = 'La hora de inicio debe ser menor que la hora final.';
            $tipo_mensaje = 'danger';
        } elseif (espacioEnMantenimiento($conexion, $id_espacio)) {
            $mensaje = 'No puedes asignar un horario a un espacio en mantenimiento.';
            $tipo_mensaje = 'danger';
        } else {
            $id_edicion = 0;

            if ($accion === 'actualizar') {
                $id_edicion = (int) ($_POST['id'] ?? 0);
            }

            if (existeConflictoHorario($conexion, $id_espacio, $dia_semana, $hora_inicio, $hora_fin, $id_edicion)) {
                $mensaje = 'Ya existe un horario activo en ese espacio durante ese periodo.';
                $tipo_mensaje = 'danger';
            } else {
                if ($accion === 'guardar') {
                    $sql_guardar = "INSERT INTO horarios (
                                        id_espacio, grupo_carrera, materia_actividad, docente,
                                        dia_semana, hora_inicio, hora_fin, id_usuario_registro, estado
                                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

                    $stmt_guardar = $conexion->prepare($sql_guardar);
                    $stmt_guardar->bind_param(
                        'issssssis',
                        $id_espacio,
                        $grupo_carrera,
                        $materia_actividad,
                        $docente,
                        $dia_semana,
                        $hora_inicio,
                        $hora_fin,
                        $_SESSION['id_usuario'],
                        $estado
                    );

                    if ($stmt_guardar->execute()) {
                        header('Location: horarios.php?mensaje=guardado');
                        exit;
                    }

                    $mensaje = 'No fue posible guardar el horario.';
                    $tipo_mensaje = 'danger';
                    $stmt_guardar->close();
                }

                if ($accion === 'actualizar') {
                    $id = (int) ($_POST['id'] ?? 0);
                    $sql_actualizar = "UPDATE horarios
                                       SET id_espacio = ?, grupo_carrera = ?, materia_actividad = ?, docente = ?,
                                           dia_semana = ?, hora_inicio = ?, hora_fin = ?, estado = ?
                                       WHERE id = ?";

                    $stmt_actualizar = $conexion->prepare($sql_actualizar);
                    $stmt_actualizar->bind_param(
                        'isssssssi',
                        $id_espacio,
                        $grupo_carrera,
                        $materia_actividad,
                        $docente,
                        $dia_semana,
                        $hora_inicio,
                        $hora_fin,
                        $estado,
                        $id
                    );

                    if ($stmt_actualizar->execute()) {
                        header('Location: horarios.php?mensaje=actualizado');
                        exit;
                    }

                    $mensaje = 'No fue posible actualizar el horario.';
                    $tipo_mensaje = 'danger';
                    $stmt_actualizar->close();
                }
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        $sql_eliminar = "DELETE FROM horarios WHERE id = ?";
        $stmt_eliminar = $conexion->prepare($sql_eliminar);
        $stmt_eliminar->bind_param('i', $id);

        if ($stmt_eliminar->execute()) {
            header('Location: horarios.php?mensaje=eliminado');
            exit;
        }

        $mensaje = 'No fue posible eliminar el horario.';
        $tipo_mensaje = 'danger';
        $stmt_eliminar->close();
    }
}

if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje = 'Horario registrado correctamente.';
    }

    if ($_GET['mensaje'] === 'actualizado') {
        $mensaje = 'Horario actualizado correctamente.';
    }

    if ($_GET['mensaje'] === 'eliminado') {
        $mensaje = 'Horario eliminado correctamente.';
    }
}

if (isset($_GET['editar']) && $puede_editar) {
    $id_editar = (int) $_GET['editar'];
    $sql_editar = "SELECT * FROM horarios WHERE id = ?";
    $stmt_editar = $conexion->prepare($sql_editar);
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $horario_editar = $resultado_editar->fetch_assoc();
    $stmt_editar->close();
}

$resultado_espacios = $conexion->query("SELECT id, nombre, estado FROM espacios ORDER BY nombre ASC");

$sql_horarios = "SELECT horarios.*, espacios.nombre AS nombre_espacio
                 FROM horarios
                 INNER JOIN espacios ON horarios.id_espacio = espacios.id
                 ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'),
                          hora_inicio ASC";

$resultado_horarios = $conexion->query($sql_horarios);

$titulo_pagina = 'Horarios';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Horarios</h1>
        <p class="text-muted mb-0">Aqui puedes asignar y consultar horarios por espacio academico.</p>
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
        Tu rol solo puede consultar los horarios registrados.
    </div>
<?php endif; ?>

<div class="row g-4">
    <?php if ($puede_editar): ?>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3"><?php echo $horario_editar ? 'Editar horario' : 'Nuevo horario'; ?></h2>

                    <form method="POST" action="">
                        <input type="hidden" name="accion" value="<?php echo $horario_editar ? 'actualizar' : 'guardar'; ?>">

                        <?php if ($horario_editar): ?>
                            <input type="hidden" name="id" value="<?php echo (int) $horario_editar['id']; ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="id_espacio" class="form-label">Espacio</label>
                            <select name="id_espacio" id="id_espacio" class="form-select" required>
                                <option value="">Selecciona una opcion</option>
                                <?php if ($resultado_espacios->num_rows > 0): ?>
                                    <?php while ($espacio = $resultado_espacios->fetch_assoc()): ?>
                                        <option value="<?php echo (int) $espacio['id']; ?>" <?php echo ((int) ($horario_editar['id_espacio'] ?? 0) === (int) $espacio['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($espacio['nombre'] . ' - ' . $espacio['estado']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="grupo_carrera" class="form-label">Grupo o carrera</label>
                            <input type="text" name="grupo_carrera" id="grupo_carrera" class="form-control" required value="<?php echo htmlspecialchars($horario_editar['grupo_carrera'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="materia_actividad" class="form-label">Materia o actividad</label>
                            <input type="text" name="materia_actividad" id="materia_actividad" class="form-control" required value="<?php echo htmlspecialchars($horario_editar['materia_actividad'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="docente" class="form-label">Docente</label>
                            <input type="text" name="docente" id="docente" class="form-control" required value="<?php echo htmlspecialchars($horario_editar['docente'] ?? ''); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="dia_semana" class="form-label">Dia</label>
                            <select name="dia_semana" id="dia_semana" class="form-select" required>
                                <option value="">Selecciona una opcion</option>
                                <option value="Lunes" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Lunes') ? 'selected' : ''; ?>>Lunes</option>
                                <option value="Martes" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Martes') ? 'selected' : ''; ?>>Martes</option>
                                <option value="Miercoles" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Miercoles') ? 'selected' : ''; ?>>Miercoles</option>
                                <option value="Jueves" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Jueves') ? 'selected' : ''; ?>>Jueves</option>
                                <option value="Viernes" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Viernes') ? 'selected' : ''; ?>>Viernes</option>
                                <option value="Sabado" <?php echo (($horario_editar['dia_semana'] ?? '') === 'Sabado') ? 'selected' : ''; ?>>Sabado</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_inicio" class="form-label">Hora inicio</label>
                                <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required value="<?php echo htmlspecialchars($horario_editar['hora_inicio'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_fin" class="form-label">Hora fin</label>
                                <input type="time" name="hora_fin" id="hora_fin" class="form-control" required value="<?php echo htmlspecialchars($horario_editar['hora_fin'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select name="estado" id="estado" class="form-select" required>
                                <option value="activo" <?php echo (($horario_editar['estado'] ?? 'activo') === 'activo') ? 'selected' : ''; ?>>Activo</option>
                                <option value="cancelado" <?php echo (($horario_editar['estado'] ?? '') === 'cancelado') ? 'selected' : ''; ?>>Cancelado</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $horario_editar ? 'Actualizar horario' : 'Guardar horario'; ?>
                            </button>

                            <?php if ($horario_editar): ?>
                                <a href="horarios.php" class="btn btn-outline-secondary">Cancelar edicion</a>
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
                <h2 class="h5 mb-3">Lista de horarios</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Espacio</th>
                                <th>Grupo/Carrera</th>
                                <th>Materia/Actividad</th>
                                <th>Docente</th>
                                <th>Dia</th>
                                <th>Horario</th>
                                <th>Estado</th>
                                <?php if ($puede_editar): ?>
                                    <th>Acciones</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_horarios->num_rows > 0): ?>
                                <?php while ($horario = $resultado_horarios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($horario['nombre_espacio']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['grupo_carrera']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['materia_actividad']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['docente']); ?></td>
                                        <td><?php echo htmlspecialchars($horario['dia_semana']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($horario['hora_inicio'], 0, 5) . ' - ' . substr($horario['hora_fin'], 0, 5)); ?></td>
                                        <td><?php echo badgeEstado($horario['estado']); ?></td>
                                        <?php if ($puede_editar): ?>
                                            <td>
                                                <a href="horarios.php?editar=<?php echo (int) $horario['id']; ?>" class="btn btn-sm btn-warning">Editar</a>

                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id" value="<?php echo (int) $horario['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este horario?');">Eliminar</button>
                                                </form>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?php echo $puede_editar ? '8' : '7'; ?>" class="text-center">No hay horarios registrados.</td>
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
