<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioAutenticado()
{
    return isset($_SESSION['id_usuario']);
}

function validarSesion()
{
    if (!usuarioAutenticado()) {
        header('Location: login.php');
        exit;
    }
}

function obtenerRolUsuario()
{
    return $_SESSION['rol'] ?? '';
}

function validarRol($roles_permitidos)
{
    validarSesion();

    if (!in_array(obtenerRolUsuario(), $roles_permitidos, true)) {
        header('Location: dashboard.php');
        exit;
    }
}
