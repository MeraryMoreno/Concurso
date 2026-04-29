@echo off
set DB_NAME=sistema_espacios
set DB_USER=root
set DB_PASS=
set MYSQLDUMP_PATH=C:\xampp\mysql\bin\mysqldump.exe
set OUTPUT_FILE=%~dp0respaldo_sistema_espacios.sql

echo Generando respaldo de la base de datos %DB_NAME%...

if not exist "%MYSQLDUMP_PATH%" (
    echo No se encontro mysqldump en la ruta configurada.
    pause
    exit /b 1
)

if "%DB_PASS%"=="" (
    "%MYSQLDUMP_PATH%" -u %DB_USER% %DB_NAME% > "%OUTPUT_FILE%"
) else (
    "%MYSQLDUMP_PATH%" -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%OUTPUT_FILE%"
)

if %errorlevel% neq 0 (
    echo Ocurrio un error al generar el respaldo.
    pause
    exit /b 1
)

echo Respaldo generado correctamente en:
echo %OUTPUT_FILE%
pause
