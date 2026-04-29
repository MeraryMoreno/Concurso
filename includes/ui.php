<?php

function h($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function claseBadgeEstado($estado)
{
    $mapa = [
        'disponible' => 'badge-estado badge-disponible',
        'ocupado' => 'badge-estado badge-ocupado',
        'mantenimiento' => 'badge-estado badge-mantenimiento',
        'activo' => 'badge-estado badge-activo',
        'cancelado' => 'badge-estado badge-cancelado',
        'reportada' => 'badge-estado badge-reportada',
        'atendida' => 'badge-estado badge-atendida',
        'pendiente' => 'badge-estado badge-pendiente',
        'en_proceso' => 'badge-estado badge-proceso',
        'resuelto' => 'badge-estado badge-resuelto',
        'inactivo' => 'badge-estado badge-inactivo',
        'administrador' => 'badge-estado badge-admin',
        'area_academica' => 'badge-estado badge-academica',
        'prefecto' => 'badge-estado badge-prefecto',
        'ausencia_docente' => 'badge-estado badge-incidencia',
        'falla_espacio' => 'badge-estado badge-incidencia',
        'otro' => 'badge-estado badge-secundario'
    ];

    return $mapa[$estado] ?? 'badge-estado badge-secundario';
}

function textoBonito($texto)
{
    $mapa = [
        'en_proceso' => 'En proceso',
        'area_academica' => 'Area academica',
        'ausencia_docente' => 'Ausencia de docente',
        'falla_espacio' => 'Falla del espacio'
    ];

    return $mapa[$texto] ?? ucfirst($texto);
}

function badgeEstado($estado)
{
    return '<span class="' . claseBadgeEstado($estado) . '">' . h(textoBonito($estado)) . '</span>';
}
