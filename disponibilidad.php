<?php

require_once 'includes/auth.php';
validarRol(['administrador', 'area_academica', 'prefecto']);
require_once 'includes/ui.php';

require_once 'config/database.php';

$conexion = conectarDB($host, $usuario_db, $password_db, $nombre_db);

date_default_timezone_set('America/Mexico_City');

$dias_semana = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miercoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sabado',
    'Sunday' => 'Domingo'
];

$dia_actual_ingles = date('l');
$dia_actual = $dias_semana[$dia_actual_ingles] ?? '';
$hora_actual = date('H:i:s');
$fecha_hora_actual = date('d/m/Y H:i');

$sql_espacios = "SELECT * FROM espacios ORDER BY nombre ASC";
$resultado_espacios = $conexion->query($sql_espacios);

$espacios_disponibilidad = [];

if ($resultado_espacios->num_rows > 0) {
    $sql_horario_actual = "SELECT materia_actividad, docente, hora_inicio, hora_fin
                           FROM horarios
                           WHERE id_espacio = ?
                           AND dia_semana = ?
                           AND estado = 'activo'
                           AND hora_inicio <= ?
                           AND hora_fin > ?
                           LIMIT 1";

    $stmt_horario_actual = $conexion->prepare($sql_horario_actual);

    while ($espacio = $resultado_espacios->fetch_assoc()) {
        $estado_actual = 'disponible';
        $detalle_estado = 'Sin actividad en este momento.';

        if ($espacio['estado'] === 'mantenimiento') {
            $estado_actual = 'mantenimiento';
            $detalle_estado = 'El espacio se encuentra en mantenimiento.';
        } elseif ($dia_actual !== 'Domingo') {
            $stmt_horario_actual->bind_param('isss', $espacio['id'], $dia_actual, $hora_actual, $hora_actual);
            $stmt_horario_actual->execute();
            $resultado_horario_actual = $stmt_horario_actual->get_result();
            $horario_actual = $resultado_horario_actual->fetch_assoc();

            if ($horario_actual) {
                $estado_actual = 'ocupado';
                $detalle_estado = 'Ocupado por ' . $horario_actual['materia_actividad'] . ' con ' . $horario_actual['docente'] .
                    ' de ' . substr($horario_actual['hora_inicio'], 0, 5) . ' a ' . substr($horario_actual['hora_fin'], 0, 5) . '.';
            }
        }

        $espacios_disponibilidad[] = [
            'nombre' => $espacio['nombre'],
            'tipo' => $espacio['tipo'],
            'ubicacion' => $espacio['ubicacion'],
            'capacidad' => $espacio['capacidad'],
            'equipamiento' => $espacio['equipamiento'],
            'estado_actual' => $estado_actual,
            'detalle_estado' => $detalle_estado
        ];
    }

    $stmt_horario_actual->close();
}

$titulo_pagina = 'Disponibilidad de espacios';
require_once 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3">Disponibilidad de espacios</h1>
        <p class="text-muted mb-0">Consulta el estado actual de los espacios segun horarios y mantenimiento.</p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <a href="dashboard.php" class="btn btn-outline-secondary">Volver al panel</a>
    </div>
</div>

<div class="alert alert-info">
    <strong>Consulta actual:</strong> <?php echo htmlspecialchars($fecha_hora_actual); ?> |
    <strong>Dia:</strong> <?php echo htmlspecialchars($dia_actual); ?>
</div>

<div class="row g-3">
    <?php if (!empty($espacios_disponibilidad)): ?>
        <?php foreach ($espacios_disponibilidad as $espacio): ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h2 class="h5 mb-0"><?php echo htmlspecialchars($espacio['nombre']); ?></h2>

                            <?php echo badgeEstado($espacio['estado_actual']); ?>
                        </div>

                        <p class="mb-1"><strong>Tipo:</strong> <?php echo htmlspecialchars($espacio['tipo']); ?></p>
                        <p class="mb-1"><strong>Ubicacion:</strong> <?php echo htmlspecialchars($espacio['ubicacion']); ?></p>
                        <p class="mb-1"><strong>Capacidad:</strong> <?php echo htmlspecialchars((string) $espacio['capacidad']); ?></p>
                        <p class="mb-3"><strong>Equipamiento:</strong> <?php echo htmlspecialchars($espacio['equipamiento']); ?></p>
                        <div class="border-top pt-3">
                            <p class="mb-0"><?php echo htmlspecialchars($espacio['detalle_estado']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="alert alert-warning mb-0">
                No hay espacios registrados para mostrar la disponibilidad.
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$conexion->close();
require_once 'includes/footer.php';
?>
