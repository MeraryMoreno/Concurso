# Sistema de Espacios

## 1. Descripcion general de la solucion

Se desarrollo un sistema web para apoyar la gestion y monitoreo de espacios academicos dentro de una institucion educativa. La solucion integra en una sola plataforma el control de acceso por roles, la administracion de espacios, la asignacion de horarios, la consulta de disponibilidad, el registro de incidencias y el seguimiento de mantenimiento.

El objetivo principal del sistema es mejorar la organizacion institucional, evitar conflictos en la asignacion de espacios y brindar informacion clara sobre el estado de uso de salones, laboratorios y centros de computo.

## 2. Modulos principales

- **Inicio de sesion y roles**: permite acceder al sistema segun el rol del usuario.
- **Usuarios**: el administrador puede registrar, editar y controlar cuentas del sistema.
- **Espacios**: permite administrar salones, laboratorios, centros de computo y aulas multiples.
- **Horarios**: registra la asignacion de espacios por dia y hora, evitando conflictos.
- **Disponibilidad**: muestra si un espacio esta disponible, ocupado o en mantenimiento.
- **Incidencias**: registra eventos como ausencia de docente o falla del espacio.
- **Mantenimiento**: da seguimiento a fallas mediante estados como pendiente, en proceso y resuelto.

## 3. Modelo de base de datos

La base de datos es relacional y esta compuesta por las siguientes tablas:

- `roles`
- `usuarios`
- `espacios`
- `horarios`
- `incidencias`
- `mantenimientos`

Relaciones principales:

- Un usuario pertenece a un rol.
- Un horario pertenece a un espacio y a un usuario que lo registro.
- Una incidencia pertenece a un espacio y a un usuario que la registro.
- Un mantenimiento pertenece a un espacio y a un usuario que lo registro.

La estructura permite mantener integridad entre los modulos y consistencia en la informacion.

## 4. Decisiones tecnicas relevantes

- Se utilizo **PHP** por ser una tecnologia sencilla de implementar y explicar.
- Se utilizo **MySQL** como base de datos relacional para mantener integridad de la informacion.
- Se utilizo **Bootstrap** y estilos CSS propios para mejorar la interfaz sin complicar el desarrollo.
- Se implemento control de acceso por roles para separar responsabilidades.
- Se agregaron validaciones para evitar conflictos de horarios en un mismo espacio.
- El estado de los espacios se relaciona con horarios activos y con mantenimientos registrados.

## 5. Bitacora breve de desarrollo

### Distribucion de tareas

- Diseno y construccion de base de datos
- Implementacion de modulos funcionales
- Pruebas de flujo del sistema
- Mejora de interfaz y preparacion de entregables

### Principales problemas enfrentados

- Definir una estructura simple pero completa para cubrir la problematica.
- Evitar conflictos entre horarios en el mismo espacio.
- Mantener coherencia entre el estado del espacio y los registros de mantenimiento.
- Mejorar la interfaz sin aumentar mucho la complejidad tecnica.

### Herramientas de apoyo utilizadas

- XAMPP
- phpMyAdmin
- Bootstrap
- Google Fonts
- Cursor

### Declaracion de uso de IA

Se utilizo inteligencia artificial como herramienta de apoyo para:

- analizar la convocatoria
- organizar el alcance del proyecto
- proponer estructura de base de datos
- apoyar en la implementacion de modulos
- mejorar la redaccion de entregables

La logica del sistema, las decisiones funcionales y la revision del resultado fueron supervisadas y entendidas por el equipo.

## 6. Prompts utilizados

Ejemplos de prompts usados durante el desarrollo:

- "Analiza la convocatoria y dime los modulos exactos que debo implementar."
- "Ayudame a construir este sistema con PHP y MySQL usando codigo sencillo y entendible."
- "Genera una base de datos relacional simple para espacios, horarios, incidencias y mantenimiento."
- "Mejora la interfaz con Bootstrap sin usar codigo avanzado."

## 7. Conclusiones

La solucion desarrollada atiende la problematica principal del concurso al integrar la gestion de espacios academicos, la asignacion de horarios, la consulta de disponibilidad, el registro de incidencias y el seguimiento de mantenimiento en una sola aplicacion web funcional.

Este archivo puede copiarse a Word o Google Docs y exportarse como PDF para la entrega final.
