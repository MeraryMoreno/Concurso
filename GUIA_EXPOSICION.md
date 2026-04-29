# Guia de exposicion

## Objetivo

Esta guia sirve para exponer el proyecto de forma clara, ordenada y facil de defender ante el jurado en un tiempo aproximado de 10 minutos.

## Recomendacion general

No expliquen todo a detalle tecnico desde el inicio. Primero expliquen el problema, luego la solucion, despues muestren el sistema funcionando y al final destaquen las decisiones tecnicas importantes.

## Estructura sugerida de exposicion

## 1. Presentacion inicial (1 minuto)

Texto sugerido:

"Buenos dias. Nuestro proyecto se llama Sistema de Espacios. Se desarrollo para resolver la problematica de gestion de espacios academicos dentro de una institucion educativa, integrando en una sola plataforma el control de usuarios, horarios, disponibilidad, incidencias y mantenimiento."

## 2. Problemática detectada (1 minuto)

Puntos clave para decir:

- antes no habia una plataforma centralizada
- era dificil saber si un espacio estaba disponible, ocupado o en mantenimiento
- podian ocurrir conflictos de horarios
- no habia un seguimiento claro de incidencias
- tampoco existia un control organizado del mantenimiento

Texto sugerido:

"La problematica principal era la falta de control centralizado sobre los espacios academicos. Esto provocaba poca visibilidad del uso de aulas y laboratorios, conflictos de horarios, dificultad para registrar incidencias y poca claridad en el seguimiento de mantenimiento."

## 3. Solucion propuesta (1 minuto)

Puntos clave:

- sistema web sencillo
- acceso por roles
- administracion de espacios
- registro de horarios con validacion
- consulta de disponibilidad
- incidencias y mantenimiento

Texto sugerido:

"Como solucion desarrollamos un sistema web con tres roles principales: administrador, area academica y prefecto. El sistema permite administrar espacios, registrar horarios, consultar disponibilidad, reportar incidencias y dar seguimiento al mantenimiento."

## 4. Explicacion de roles (1 minuto)

Explicar de forma breve:

- `administrador`: controla usuarios, espacios y modulos generales
- `area_academica`: registra horarios y consulta informacion
- `prefecto`: consulta horarios, registra incidencias y seguimiento de mantenimiento

Texto sugerido:

"Se implementaron roles para controlar el acceso a las funciones del sistema. El administrador tiene control general, el area academica trabaja principalmente con horarios y el prefecto se enfoca en incidencias y mantenimiento."

## 5. Demostracion del sistema (4 minutos)

### Orden recomendado de demo

1. **Login**
   - mostrar acceso con usuario y contrasena
   - mencionar que el sistema valida credenciales y roles

2. **Dashboard**
   - mostrar accesos rapidos
   - mencionar que la interfaz se organizo por modulos

3. **Usuarios** (solo rapido)
   - mostrar que el administrador puede registrar y editar usuarios
   - mencionar que existe control de roles y estado de cuenta

4. **Espacios**
   - mostrar alta y consulta de espacios
   - destacar nombre, tipo, ubicacion, capacidad, equipamiento y estado

5. **Horarios**
   - mostrar un horario registrado
   - explicar que el sistema evita conflictos en el mismo espacio y horario

6. **Disponibilidad**
   - mostrar como el sistema indica si un espacio esta disponible, ocupado o en mantenimiento

7. **Incidencias**
   - mostrar registro de ausencia de docente o falla del espacio

8. **Mantenimiento**
   - mostrar seguimiento por estado: pendiente, en proceso y resuelto
   - explicar que el estado del espacio cambia segun el mantenimiento

## 6. Base de datos y decisiones tecnicas (1 minuto)

Puntos clave:

- base de datos relacional
- tablas principales: roles, usuarios, espacios, horarios, incidencias, mantenimientos
- relaciones entre modulos
- integridad de la informacion

Texto sugerido:

"La base de datos se diseno de forma relacional para mantener integridad y coherencia entre los modulos. Cada horario, incidencia y mantenimiento se relaciona con un espacio y con el usuario que lo registro."

## 7. Cierre (1 minuto)

Texto sugerido:

"Con esta solucion logramos atender la problematica principal del concurso, integrando la gestion de espacios, horarios, disponibilidad, incidencias y mantenimiento en una sola aplicacion funcional, clara y facil de usar."

## Puntos tecnicos que deben mencionar si les preguntan

- el sistema usa PHP y MySQL
- la autenticacion controla el acceso por rol
- los horarios validan conflictos en el mismo espacio
- la disponibilidad depende del horario actual y del estado de mantenimiento
- el mantenimiento puede cambiar el estado del espacio
- las incidencias quedan asociadas a un espacio y a un usuario

## Preguntas probables del jurado y respuestas sugeridas

### 1. Como controlan los roles?

Respuesta sugerida:

"Cada usuario tiene un rol registrado en la base de datos. Al iniciar sesion, el sistema guarda ese rol y segun eso permite o restringe el acceso a los modulos."

### 2. Como evitan conflictos en horarios?

Respuesta sugerida:

"Antes de guardar un horario, el sistema revisa si ya existe otro horario activo en el mismo espacio, dia y rango de horas. Si detecta traslape, no permite registrarlo."

### 3. Como saben si un espacio esta disponible?

Respuesta sugerida:

"El sistema revisa el horario actual del espacio y tambien su estado general. Si tiene mantenimiento aparece como mantenimiento; si tiene un horario activo en ese momento aparece como ocupado; si no, aparece como disponible."

### 4. Por que eligieron estas tecnologias?

Respuesta sugerida:

"Elegimos PHP y MySQL porque son tecnologias estables, faciles de implementar localmente con XAMPP y adecuadas para construir una solucion funcional en el tiempo del concurso."

### 5. Que reglas de negocio implementaron?

Respuesta sugerida:

"Control de roles, validacion de credenciales, prevencion de choques de horarios, relacion entre mantenimiento y estado del espacio, y asociacion de incidencias y horarios con sus espacios correspondientes."

### 6. Como usaron inteligencia artificial?

Respuesta sugerida:

"Se utilizo como herramienta de apoyo para analizar la convocatoria, organizar el alcance, apoyar en la estructura del sistema y mejorar la redaccion de algunos entregables. La implementacion, revision y comprension de la solucion fue supervisada por el equipo."

## Reparto sugerido entre integrantes

### Integrante 1

- presentacion del problema
- solucion general
- tecnologias utilizadas

### Integrante 2

- base de datos
- gestion de espacios
- horarios y validaciones

### Integrante 3

- incidencias
- mantenimiento
- disponibilidad
- cierre y conclusiones

## Consejos para la exposicion

- no lean todo literalmente
- hablen claro y corto
- muestren primero lo que mejor funciona
- si el tiempo es corto, enfoquense en login, espacios, horarios y disponibilidad
- si les preguntan algo tecnico, respondan con ejemplos del sistema
- no digan que el sistema "solo es un prototipo"; mejor digan que es una solucion funcional

## Checklist antes de exponer

- verificar que Apache y MySQL esten activos
- verificar que la base de datos este importada
- probar login con los usuarios de prueba
- abrir previamente las pantallas principales
- tener listo el orden de la demo
- acordar quien explicara cada parte
