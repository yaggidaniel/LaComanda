# Restaurante La comanda de Daniel Yaggi 

## Base de Datos

## Descripción

Esta sección contiene la estructura de la base de datos para el sistema de gestión de un restaurante llamado Restaurante La comanda de Daniel Yaggi. La base de datos se ha diseñado para cumplir con los siguientes requerimientos:

- Gestión de empleados en diferentes categorías: bartender, cerveceros, cocineros, mozos y socios.
- Registro de comandas, pedidos y productos.
- Control del estado de las mesas y las encuestas de los clientes.
- Seguimiento de las operaciones y generación de informes.

## Estructura de la Base de Datos

La base de datos se compone de las siguientes tablas:

- `Empleados`: Registra información sobre los empleados, incluyendo su categoría y horarios de trabajo.
- `Comandas`: Almacena detalles de los pedidos realizados por los clientes, incluyendo el nombre del cliente y el estado de preparación.
- `Mesas`: Registra información sobre las mesas, su código de identificación y estado.
- `Encuestas`: Contiene la retroalimentación de los clientes, incluyendo puntuaciones y comentarios.
- `Productos`: Lista los productos ofrecidos en el restaurante.
- `Pedidos`: Registra los pedidos realizados en las comandas y los productos pedidos.
- `Socios`: Puede ser utilizada para gestionar los socios del restaurante.

## Uso de la Base de Datos

La base de datos se puede utilizar como respaldo para una aplicación que gestione las operaciones del restaurante. Aquí hay un ejemplo de cómo se pueden utilizar las tablas principales:

- Los empleados pueden iniciar sesión en la aplicación y registrar su horario de trabajo.
- Los mozos toman pedidos de los clientes y registran comandas. Cada empleado puede ver sus comandas pendientes.
- Los cocineros y bartenders registran la preparación de pedidos y cambian el estado a "listo para servir" cuando están listos.
- Los clientes pueden realizar encuestas después de su comida.
- Los socios tienen acceso a todas las operaciones y pueden cerrar mesas.

## Requisitos Técnicos

- Sistema de gestión de bases de datos MySQL.
- Lenguaje de programación (PHP) para implementar la lógica de la aplicación que interactúa con la base de datos.

## Contribuciones

Si deseas contribuir a este proyecto o realizar mejoras en la base de datos, no dudes en clonar el repositorio y enviar solicitudes de extracción.

## Contacto

Para más información, puedes ponerte en contacto con Daniel Yaggi en [yaggidaniel@gmail.com].

