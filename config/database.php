<?php

$host = 'localhost';
$usuario_db = 'root';
$password_db = '';
$nombre_db = 'sistema_espacios';

function conectarDB($host, $usuario_db, $password_db, $nombre_db)
{
    $conexion = new mysqli($host, $usuario_db, $password_db, $nombre_db);

    if ($conexion->connect_error) {
        die('Error de conexion: ' . $conexion->connect_error);
    }

    $conexion->set_charset('utf8');

    return $conexion;
}
