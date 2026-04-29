# Sistema de Espacios

Sistema web para la gestion de espacios academicos, horarios, incidencias y mantenimiento dentro de una institucion educativa.

## Tecnologias utilizadas

- PHP
- MySQL
- HTML
- CSS
- Bootstrap 5
- Google Fonts
- XAMPP

## Modulos principales

- Inicio de sesion y control de roles
- Gestion de usuarios
- Gestion de espacios
- Gestion de horarios
- Registro de incidencias
- Seguimiento de mantenimiento
- Consulta de disponibilidad de espacios

## Roles del sistema

- `administrador`: administra usuarios, espacios, horarios, incidencias y mantenimiento
- `area_academica`: registra horarios y consulta informacion del sistema
- `prefecto`: consulta horarios y registra incidencias y mantenimientos

## Requisitos

- XAMPP instalado
- Apache activo
- MySQL activo
- Navegador web

## Instalacion y ejecucion

1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\`.
2. Iniciar `Apache` y `MySQL` desde el panel de control de XAMPP.
3. Abrir `phpMyAdmin`.
4. Importar el archivo `sql/sistema_espacios.sql`.
5. Verificar que se haya creado la base de datos `sistema_espacios`.
6. Abrir en el navegador la direccion:

```text
http://localhost/CONCURSO/
```

## Usuarios de prueba

- `admin` / `admin123`
- `academica` / `academica123`
- `prefecto` / `prefecto123`

## Base de datos

El proyecto incluye:

- `sql/sistema_espacios.sql`: script de creacion de la base de datos
- `sql/respaldo_bd.bat`: script de respaldo para exportar la base de datos en Windows con XAMPP


