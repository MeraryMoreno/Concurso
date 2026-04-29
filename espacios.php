<?php

require_once 'includes/auth.php';
validarRol(['administrador']);
require_once 'includes/ui.php';

require_once 'config/database.php';

$conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

$mensaje = '';
$tipo_mensaje = 'success';
$espacio_editar = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'guardar' || $accion === 'actualizar') {
        $nombre = trim($_POST['nombre'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $ubicacion = trim($_POST['ubicacion'] ?? '');
        $capacidad = (int) ($_POST['capacidad'] ?? 0);
        $equipamiento = trim($_POST['equipamiento'] ?? '');
        $estado = trim($_POST['estado'] ?? '');

        if ($nombre === '' || $tipo === '' || $ubicacion === '' || $capacidad <= 0 || $equipamiento === '' || $estado === '') {
            $mensaje = 'Completa todos los campos correctamente.';
            $tipo_mensaje = 'danger';
        } else {
            if ($accion === 'guardar') {
                $sql_guardar = "INSERT INTO espacios (nombre, tipo, ubicacion, capacidad, equipamiento, estado)
                                VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_guardar = $conexion->prepare($sql_guardar);
                $stmt_guardar->bind_param('sssiss', $nombre, $tipo, $ubicacion, $capacidad, $equipamiento, $estado);

                if ($stmt_guardar->execute()) {
                    header('Location: espacios.php?mensaje=guardado');
                    exit;
                }

                $mensaje = 'No fue posible guardar el espacio.';
                $tipo_mensaje = 'danger';
                $stmt_guardar->close();
            }

            if ($accion === 'actualizar') {
                $id = (int) ($_POST['id'] ?? 0);
                $sql_actualizar = "UPDATE espacios
                                   SET nombre = ?, tipo = ?, ubicacion = ?, capacidad = ?, equipamiento = ?, estado = ?
                                   WHERE id = ?";
                $stmt_actualizar = $conexion->prepare($sql_actualizar);
                $stmt_actualizar->bind_param('sssissi', $nombre, $tipo, $ubicacion, $capacidad, $equipamiento, $estado, $id);

                if ($stmt_actualizar->execute()) {
                    header('Location: espacios.php?mensaje=actualizado');
                    exit;
                }

                $mensaje = 'No fue posible actualizar el espacio.';
                $tipo_mensaje = 'danger';
                $stmt_actualizar->close();
            }
        }
    }

    if ($accion === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        $sql_eliminar = "DELETE FROM espacios WHERE id = ?";
        $stmt_eliminar = $conexion->prepare($sql_eliminar);
        $stmt_eliminar->bind_param('i', $id);

        if ($stmt_eliminar->execute()) {
            header('Location: espacios.php?mensaje=eliminado');
            exit;
        }

        $mensaje = 'No se pudo eliminar el espacio. Verifica si tiene informacion relacionada.';
        $tipo_mensaje = 'danger';
        $stmt_eliminar->close();
    }
}

if (isset($_GET['mensaje'])) {
    if ($_GET['mensaje'] === 'guardado') {
        $mensaje = 'Espacio registrado correctamente.';
    }

    if ($_GET['mensaje'] === 'actualizado') {
        $mensaje = 'Espacio actualizado correctamente.';
    }

    if ($_GET['mensaje'] === 'eliminado') {
        $mensaje = 'Espacio eliminado correctamente.';
    }
}

if (isset($_GET['editar'])) {
    $id_editar = (int) $_GET['editar'];
    $sql_editar = "SELECT * FROM espacios WHERE id = ?";
    $stmt_editar = $conexion->prepare($sql_editar);
    $stmt_editar->bind_param('i', $id_editar);
    $stmt_editar->execute();
    $resultado_editar = $stmt_editar->get_result();
    $espacio_editar = $resultado_editar->fetch_assoc();
    $stmt_editar->close();
}

$resultado_espacios = $conexion->query("SELECT * FROM espacios ORDER BY nombre ASC");

$titulo_pagina = 'Gestion de espacios';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Gestion de espacios</h1>
        <p class="text-muted mb-0">Aqui puedes registrar, editar y eliminar los espacios academicos.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>
</div>

<?php if ($mensaje !== ''): ?>
    <div class="alert alert-<?php echo $tipo_mensaje; ?>">
        <?php echo h($mensaje); ?>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3"><?php echo $espacio_editar ? 'Editar espacio' : 'Nuevo espacio'; ?></h2>

                <form method="POST" action="">
                    <input type="hidden" name="accion" value="<?php echo $espacio_editar ? 'actualizar' : 'guardar'; ?>">

                    <?php if ($espacio_editar): ?>
                        <input type="hidden" name="id" value="<?php echo $espacio_editar['id']; ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required value="<?php echo h($espacio_editar['nombre'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="">Selecciona una opcion</option>
                            <option value="Salon" <?php echo (($espacio_editar['tipo'] ?? '') === 'Salon') ? 'selected' : ''; ?>>Salon</option>
                            <option value="Laboratorio" <?php echo (($espacio_editar['tipo'] ?? '') === 'Laboratorio') ? 'selected' : ''; ?>>Laboratorio</option>
                            <option value="Centro de computo" <?php echo (($espacio_editar['tipo'] ?? '') === 'Centro de computo') ? 'selected' : ''; ?>>Centro de computo</option>
                            <option value="Aula multiple" <?php echo (($espacio_editar['tipo'] ?? '') === 'Aula multiple') ? 'selected' : ''; ?>>Aula multiple</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicacion</label>
                        <input type="text" name="ubicacion" id="ubicacion" class="form-control" required value="<?php echo h($espacio_editar['ubicacion'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="capacidad" class="form-label">Capacidad</label>
                        <input type="number" name="capacidad" id="capacidad" class="form-control" min="1" required value="<?php echo h($espacio_editar['capacidad'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="equipamiento" class="form-label">Equipamiento</label>
                        <input type="text" name="equipamiento" id="equipamiento" class="form-control" required value="<?php echo h($espacio_editar['equipamiento'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select name="estado" id="estado" class="form-select" required>
                            <option value="">Selecciona una opcion</option>
                            <option value="disponible" <?php echo (($espacio_editar['estado'] ?? '') === 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                            <option value="ocupado" <?php echo (($espacio_editar['estado'] ?? '') === 'ocupado') ? 'selected' : ''; ?>>Ocupado</option>
                            <option value="mantenimiento" <?php echo (($espacio_editar['estado'] ?? '') === 'mantenimiento') ? 'selected' : ''; ?>>Mantenimiento</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <?php echo $espacio_editar ? 'Actualizar espacio' : 'Guardar espacio'; ?>
                        </button>

                        <?php if ($espacio_editar): ?>
                            <a href="espacios.php" class="btn btn-outline-secondary">Cancelar edicion</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h2 class="h5 mb-3">Lista de espacios</h2>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Ubicacion</th>
                                <th>Capacidad</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado_espacios->num_rows > 0): ?>
                                <?php while ($espacio = $resultado_espacios->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo h($espacio['nombre']); ?></td>
                                        <td><?php echo h($espacio['tipo']); ?></td>
                                        <td><?php echo h($espacio['ubicacion']); ?></td>
                                        <td><?php echo h($espacio['capacidad']); ?></td>
                                        <td><?php echo badgeEstado($espacio['estado']); ?></td>
                                        <td>
                                            <a href="espacios.php?editar=<?php echo (int) $espacio['id']; ?>" class="btn btn-sm btn-warning">Editar</a>

                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id" value="<?php echo (int) $espacio['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Deseas eliminar este espacio?');">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No hay espacios registrados.</td>
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
