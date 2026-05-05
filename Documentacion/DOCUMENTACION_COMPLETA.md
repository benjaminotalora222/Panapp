# PanApp - Documentacion Tecnica Completa

> Sistema de gestion integral para panaderias colombianas

---

## 1. Descripcion General

**PanApp** es una aplicacion web desarrollada en PHP con arquitectura MVC (Modelo-Vista-Controlador) orientada a la gestion operativa y administrativa de una panaderia. Permite registrar ventas, controlar inventario de insumos, gestionar productos, proveedores y usuarios, ademas de generar reportes de ventas.

### Objetivo

Digitalizar y optimizar los procesos diarios de una panaderia: reducir errores manuales, mejorar el control del inventario y agilizar el punto de venta.

---

## 2. Tecnologias Utilizadas

| Componente | Tecnologia |
|---|---|
| Lenguaje backend | PHP (sin framework) |
| Base de datos | MySQL (via Laragon, puerto 3320) |
| Acceso a BD | PDO (PHP Data Objects) |
| Frontend | HTML5, CSS3, JavaScript vanilla |
| Framework CSS | Tailwind CSS (CDN) |
| Iconos | Font Awesome 6 |
| Tipografia | Google Fonts (Nunito, Playfair Display) |
| Alertas UI | SweetAlert2 |
| Patron de diseno | MVC (Modelo - Vista - Controlador) |

---

## 3. Arquitectura del Sistema

El proyecto sigue el patron **MVC** con la siguiente separacion de responsabilidades:

```
PanApp/
├── config/          # Configuracion de base de datos
├── controllers/     # Logica de negocio y manejo de peticiones HTTP
├── models/          # Acceso a datos (actualmente solo Usuario)
├── views/           # Interfaces de usuario (HTML + PHP)
│   ├── dashboard/   # Paneles de administrador y cajero
│   ├── insumos/     # CRUD de insumos
│   ├── inventario/  # Control de stock
│   ├── layouts/     # Plantillas compartidas (header, sidebar, footer)
│   ├── productos/   # CRUD de productos
│   ├── proveedores/ # CRUD de proveedores
│   ├── reportes/    # Reportes y exportacion PDF
│   ├── usuarios/    # Login, registro
│   └── ventas/      # Registro y detalle de ventas
├── public/          # Punto de entrada (index.php - landing page)
├── sql/             # Script de base de datos
└── Documentacion/   # Documentacion del proyecto
```

### Flujo de una peticion

1. El usuario accede desde el navegador.
2. Hace una accion (ej: registrar venta).
3. El formulario envia datos al **Controlador** correspondiente via POST/GET.
4. El controlador valida los datos, usa el **Modelo** si es necesario.
5. Se consulta o actualiza la **Base de Datos** via PDO.
6. Se redirige al usuario a la **Vista** con el resultado (usando `$_SESSION['alert']`).

---

## 4. Configuracion de Base de Datos

**Archivo:** `config/database.php`

Clase `Database` con metodo `conectar()` que retorna una conexion PDO.

| Parametro | Valor por defecto |
|---|---|
| Host | 127.0.0.1 |
| Puerto | 3320 |
| Base de datos | panaderia2 |
| Usuario | root |
| Contrasena | (vacia) |
| Charset | utf8mb4 |
| Modo de error | PDO::ERRMODE_EXCEPTION |

---

## 5. Esquema de Base de Datos

**Base de datos:** `panaderia2`

### Tablas

#### `usuarios`
Almacena todos los usuarios del sistema.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_usuario | BIGINT PK AUTO_INCREMENT | Identificador unico |
| nombres | VARCHAR(100) | Nombres del usuario |
| apellidos | VARCHAR(100) | Apellidos del usuario |
| email | VARCHAR(150) UNIQUE | Correo electronico (login) |
| password_hash | VARCHAR(255) | Contrasena hasheada con bcrypt |
| rol | VARCHAR(20) | `ADMIN` o `CAJERO` |
| activo | BOOLEAN | Estado de la cuenta |
| created_at | TIMESTAMP | Fecha de registro |

#### `admin`
Extiende el perfil de usuarios con rol ADMIN.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_admin | BIGINT PK | Identificador |
| id_usuario | BIGINT FK UNIQUE | Referencia a usuarios |
| permisos | TEXT | Permisos adicionales |

#### `cajero`
Extiende el perfil de usuarios con rol CAJERO.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_cajero | BIGINT PK | Identificador |
| id_usuario | BIGINT FK UNIQUE | Referencia a usuarios |
| turno | VARCHAR(50) | Turno asignado |

#### `proveedores`
Empresas o personas que suministran insumos.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_proveedor | INT PK AUTO_INCREMENT | Identificador |
| nombre | VARCHAR(100) | Nombre del proveedor |
| telefono | VARCHAR(20) | Telefono de contacto |
| correo | VARCHAR(100) | Correo electronico |
| direccion | VARCHAR(150) | Direccion fisica |
| estado | VARCHAR(20) | `activo` o `inactivo` |
| fecha_registro | DATETIME | Fecha de registro |

#### `insumos`
Materias primas utilizadas en la produccion.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_insumo | INT PK AUTO_INCREMENT | Identificador |
| nombre | VARCHAR(100) | Nombre del insumo |
| descripcion | VARCHAR(150) | Descripcion opcional |
| unidad_medida | VARCHAR(50) | Kg, litros, unidades, etc. |
| id_proveedor | INT FK | Proveedor asociado |

#### `inventario_insumos`
Stock actual de cada insumo.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_inventario | INT PK AUTO_INCREMENT | Identificador |
| id_insumo | INT FK | Insumo referenciado |
| cantidad_actual | INT | Cantidad disponible en stock |
| fecha_actualizacion | DATETIME | Ultima actualizacion |

#### `movimientos_inventario`
Historial de entradas y salidas de insumos.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_movimiento | INT PK AUTO_INCREMENT | Identificador |
| id_insumo | INT FK | Insumo afectado |
| tipo | VARCHAR(20) | `entrada` o `salida` |
| cantidad | INT | Cantidad del movimiento |
| motivo | VARCHAR(50) | Razon del ajuste |
| fecha | DATETIME | Fecha del movimiento |

#### `productos`
Productos terminados disponibles para la venta.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_producto | INT PK AUTO_INCREMENT | Identificador |
| nombre | VARCHAR(100) | Nombre del producto |
| descripcion | VARCHAR(150) | Descripcion opcional |
| categoria | VARCHAR(50) | Categoria (pan, pastel, etc.) |
| precio | DECIMAL(10,2) | Precio de venta |
| unidad_medida | VARCHAR(50) | Unidad de venta |

#### `producto_insumo`
Relacion muchos a muchos entre productos e insumos (receta).

| Campo | Tipo | Descripcion |
|---|---|---|
| id_producto | INT FK | Producto |
| id_insumo | INT FK | Insumo utilizado |
| cantidad_usada | DECIMAL(10,2) | Cantidad del insumo por producto |

#### `metodos_pago`
Formas de pago aceptadas.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_metodo_pago | INT PK AUTO_INCREMENT | Identificador |
| nombre | VARCHAR(50) | Nombre (Efectivo, Tarjeta, etc.) |

#### `ventas`
Registro de cada transaccion de venta.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_venta | INT PK AUTO_INCREMENT | Identificador |
| fecha | DATETIME | Fecha y hora de la venta |
| total | DECIMAL(10,2) | Total de la venta |
| id_usuario | BIGINT FK | Usuario que realizo la venta |
| id_metodo_pago | INT FK | Metodo de pago utilizado |
| estado | VARCHAR(20) | `completada` o `anulada` |

#### `detalle_venta`
Productos incluidos en cada venta.

| Campo | Tipo | Descripcion |
|---|---|---|
| id_detalle | INT PK AUTO_INCREMENT | Identificador |
| id_venta | INT FK | Venta a la que pertenece |
| id_producto | INT FK | Producto vendido |
| cantidad | INT | Cantidad vendida |
| precio_unitario | DECIMAL(10,2) | Precio al momento de la venta |
| subtotal | DECIMAL(10,2) | cantidad x precio_unitario |

### Diagrama de relaciones (resumen)

```
usuarios ──< ventas >── metodos_pago
usuarios ──< admin
usuarios ──< cajero
proveedores ──< insumos >── inventario_insumos
                insumos ──< movimientos_inventario
                insumos >──< productos (producto_insumo)
ventas ──< detalle_venta >── productos
```

---

## 6. Roles y Control de Acceso

El sistema maneja dos roles definidos en la tabla `usuarios`:

### ADMIN (Administrador)
Acceso completo al sistema.

- Gestionar usuarios (crear, editar, activar/desactivar, eliminar)
- Gestionar productos (crear, editar, eliminar)
- Gestionar insumos (crear, editar, eliminar)
- Gestionar proveedores (crear, editar, eliminar)
- Ajustar inventario (entradas y salidas de stock)
- Ver reportes generales de todos los cajeros
- Registrar y anular ventas
- Exportar reportes a PDF

### CAJERO
Acceso limitado al punto de venta.

- Registrar ventas
- Ver y editar productos (no eliminar)
- Ver inventario (solo lectura)
- Ver proveedores e insumos (solo lectura)
- Ver sus propios reportes de ventas
- Anular sus propias ventas

### Implementacion de seguridad

- Cada controlador verifica `$_SESSION['usuario']` al inicio.
- Las rutas restringidas redirigen a `login.php` si no hay sesion activa.
- Las acciones exclusivas de ADMIN verifican `strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN'`.
- Las contrasenas se almacenan con `password_hash($password, PASSWORD_DEFAULT)` (bcrypt).
- El login usa `password_verify()` para comparar.
- Se llama `session_regenerate_id(true)` al iniciar sesion para prevenir session fixation.
- Todas las salidas de datos usan `htmlspecialchars()` para prevenir XSS.
- Todas las consultas SQL usan sentencias preparadas con PDO para prevenir SQL injection.

---

## 7. Modulos del Sistema

### 7.1 Autenticacion

**Archivos:** `controllers/AuthController.php`, `controllers/UsuarioController.php`, `views/usuarios/login.php`, `views/usuarios/registre.php`

#### Login (`AuthController::login`)
1. Recibe `email` y `password` por POST.
2. Valida que los campos no esten vacios y que el email tenga formato valido.
3. Busca el usuario en BD con `Usuario::obtenerPorEmail()`.
4. Verifica que el usuario exista y este activo.
5. Verifica la contrasena con `password_verify()`.
6. Crea la sesion con datos del usuario.
7. Redirige segun rol: ADMIN -> `dashboard/admin.php`, CAJERO -> `dashboard/cajero.php`.

#### Logout (`AuthController::logout`)
Destruye la sesion y redirige al login.

#### Registro (`UsuarioController::registrar`)
Permite crear nuevos usuarios desde la vista publica de registro. Valida todos los campos, verifica correo duplicado y hashea la contrasena antes de guardar.

---

### 7.2 Gestion de Usuarios

**Archivos:** `controllers/AdminUsuarioController.php`, `models/usuario.php`, `views/dashboard/admin.php`, `views/dashboard/usuario_crear.php`, `views/dashboard/usuario_editar.php`

**Acceso:** Solo ADMIN

#### Acciones disponibles

| Accion | Descripcion |
|---|---|
| `crear` | Crea un nuevo usuario con validacion completa |
| `editar` | Actualiza datos del usuario (contrasena opcional) |
| `toggleEstado` | Activa o desactiva una cuenta |
| `eliminar` | Elimina un usuario (no puede eliminarse a si mismo) |

#### Modelo `Usuario`

| Metodo | Descripcion |
|---|---|
| `existeCorreo($email)` | Verifica si un correo ya esta registrado |
| `obtenerPorEmail($email)` | Busca usuario por email (para login) |
| `registrar($datos)` | Inserta nuevo usuario |
| `obtenerTodos()` | Lista todos los usuarios ordenados por fecha |
| `obtenerPorId($id)` | Obtiene un usuario por su ID |
| `actualizar($id, $datos)` | Actualiza datos del usuario |
| `cambiarEstado($id, $activo)` | Activa o desactiva la cuenta |
| `eliminar($id)` | Elimina el usuario de la BD |

---

### 7.3 Gestion de Productos

**Archivos:** `controllers/ProductoController.php`, `views/productos/`

| Accion | Rol requerido | Descripcion |
|---|---|---|
| `crear` | ADMIN y CAJERO | Agrega un nuevo producto al catalogo |
| `editar` | ADMIN y CAJERO | Modifica datos del producto |
| `eliminar` | Solo ADMIN | Elimina el producto (falla si tiene ventas asociadas) |

**Campos del producto:** nombre, descripcion, categoria, precio, unidad_medida.

---

### 7.4 Gestion de Insumos

**Archivos:** `controllers/InsumoController.php`, `views/insumos/`

**Acceso:** Solo ADMIN para crear, editar y eliminar. Todos pueden ver.

| Accion | Descripcion |
|---|---|
| `crear` | Crea el insumo y automaticamente genera un registro en `inventario_insumos` con stock 0 |
| `editar` | Actualiza nombre, descripcion, unidad y proveedor |
| `eliminar` | Elimina en cascada: movimientos, inventario, relaciones producto_insumo e insumo |

---

### 7.5 Control de Inventario

**Archivos:** `controllers/InventarioController.php`, `views/inventario/`

**Acceso:** Solo ADMIN puede ajustar. Todos pueden ver.

#### Ajuste de stock (`ajustar`)
1. Recibe `id_insumo`, `tipo` (entrada/salida), `cantidad` y `motivo`.
2. Verifica stock suficiente para salidas.
3. Actualiza `inventario_insumos` con la nueva cantidad.
4. Registra el movimiento en `movimientos_inventario`.
5. Usa transacciones PDO para garantizar consistencia.

**Indicadores de stock en la vista:**
- Stock OK: cantidad > 5
- Stock bajo: cantidad <= 5
- Sin stock: cantidad = 0

---

### 7.6 Gestion de Proveedores

**Archivos:** `controllers/ProveedorController.php`, `views/proveedores/`

**Acceso:** Solo ADMIN para CRUD. Todos pueden ver.

| Accion | Descripcion |
|---|---|
| `crear` | Registra nuevo proveedor con nombre, telefono, correo, direccion y estado |
| `editar` | Actualiza datos del proveedor |
| `eliminar` | Elimina proveedor (falla si tiene insumos asociados) |

---

### 7.7 Registro de Ventas

**Archivos:** `controllers/VentaController.php`, `views/ventas/`

#### Registrar venta
1. El cajero/admin selecciona productos desde una grilla interactiva con buscador.
2. Ajusta cantidades en el carrito (JavaScript).
3. Selecciona metodo de pago.
4. Confirma con SweetAlert2.
5. El formulario envia los items como JSON al controlador.
6. El controlador inserta en `ventas` y en `detalle_venta` dentro de una transaccion.
7. Redirige al dashboard con confirmacion.

#### Anular venta
- ADMIN puede anular cualquier venta.
- CAJERO solo puede anular sus propias ventas.
- No se puede anular una venta ya anulada.
- Cambia el campo `estado` a `'anulada'`.

---

### 7.8 Reportes

**Archivos:** `views/reportes/index.php`, `views/reportes/exportar_pdf.php`

**Acceso:** Todos los roles (ADMIN ve todos, CAJERO ve solo los suyos).

#### Periodos disponibles
- **Diario:** ventas del dia actual
- **Semanal:** ventas de la semana en curso (lunes a domingo)
- **Mensual:** ventas del mes en curso

#### Pestanas de reporte

**Reporte de Ventas:**
- Total de ventas completadas
- Total de ingresos
- Promedio por venta
- Desglose por metodo de pago
- Tabla detallada con numero de venta, fecha, cajero (solo admin), metodo y total

**Productos mas vendidos:**
- Ranking con medallas (oro, plata, bronce)
- Unidades vendidas, numero de ventas y total de ingresos por producto

#### Exportacion PDF
Disponible desde el boton "Exportar PDF" en la vista de reportes.

---

## 8. Vistas y Layouts

### Layouts compartidos (`views/layouts/`)

| Archivo | Descripcion |
|---|---|
| `header.php` | Cabecera HTML, carga de CSS/JS, barra de navegacion superior |
| `sidebar.php` | Menu lateral con navegacion por modulos |
| `footer.php` | Cierre de etiquetas HTML, scripts finales |

Todas las vistas internas incluyen estos layouts con:
```php
require_once __DIR__ . '/../layouts/header.php';
// ... contenido ...
require_once __DIR__ . '/../layouts/footer.php';
```

### Sistema de alertas

Las alertas se pasan entre controladores y vistas mediante `$_SESSION['alert']`:
```php
$_SESSION['alert'] = [
    'icon'  => 'success|error|warning',
    'title' => 'Titulo',
    'text'  => 'Mensaje descriptivo'
];
```
Las vistas las consumen con SweetAlert2 y luego hacen `unset($_SESSION['alert'])`.

---

## 9. Pagina de Inicio (Landing Page)

**Archivo:** `public/index.php`

Pagina publica de presentacion del sistema con:
- Barra de navegacion sticky con scroll activo
- Hero section con llamada a la accion
- Banda de estadisticas (ventas, disponibilidad)
- Grilla de modulos con acceso rapido
- Seccion "Por que PanApp" con 3 caracteristicas destacadas
- CTA strip con boton de ingreso
- Footer con enlaces

Tecnologias usadas en la landing: CSS variables, animaciones CSS, Intersection Observer API para scroll reveal, Google Fonts.

---

## 10. Estructura de Archivos Completa

```
PanApp/
│
├── config/
│   └── database.php              # Clase Database con metodo conectar()
│
├── controllers/
│   ├── AdminUsuarioController.php # CRUD de usuarios (solo ADMIN)
│   ├── AuthController.php         # Login y logout
│   ├── InsumoController.php       # CRUD de insumos
│   ├── InventarioController.php   # Ajuste de stock
│   ├── ProductoController.php     # CRUD de productos
│   ├── ProveedorController.php    # CRUD de proveedores
│   ├── UsuarioController.php      # Registro publico de usuarios
│   └── VentaController.php        # Registro y anulacion de ventas
│
├── models/
│   └── usuario.php                # Modelo Usuario con metodos CRUD
│
├── views/
│   ├── dashboard/
│   │   ├── admin.php              # Panel principal del administrador
│   │   ├── cajero.php             # Panel principal del cajero
│   │   ├── usuario_crear.php      # Formulario crear usuario
│   │   └── usuario_editar.php     # Formulario editar usuario
│   ├── insumos/
│   │   ├── crear.php              # Formulario crear insumo
│   │   └── index.php              # Listado de insumos
│   ├── inventario/
│   │   ├── ajuste.php             # Formulario ajuste de stock
│   │   └── index.php              # Listado de inventario
│   ├── layouts/
│   │   ├── footer.php             # Pie de pagina compartido
│   │   ├── header.php             # Cabecera compartida
│   │   └── sidebar.php            # Menu lateral compartido
│   ├── productos/
│   │   ├── crear.php              # Formulario crear producto
│   │   ├── editar.php             # Formulario editar producto
│   │   └── index.php              # Listado de productos
│   ├── proveedores/
│   │   ├── crear.php              # Formulario crear proveedor
│   │   └── index.php              # Listado de proveedores
│   ├── reportes/
│   │   ├── exportar_pdf.php       # Generacion de PDF
│   │   └── index.php              # Vista de reportes con filtros
│   ├── usuarios/
│   │   ├── index.php              # Listado de usuarios
│   │   ├── login.php              # Formulario de inicio de sesion
│   │   └── registre.php           # Formulario de registro
│   └── ventas/
│       ├── crear.php              # Punto de venta (carrito)
│       ├── detalle.php            # Detalle de una venta
│       └── index.php              # Historial de ventas
│
├── public/
│   └── index.php                  # Landing page publica
│
├── sql/
│   └── panaderia2.sql             # Script DDL de la base de datos
│
├── img/                           # Imagenes del proyecto
├── Scrits/                        # Scripts adicionales
│
└── Documentacion/
    ├── arquictetura.md
    ├── documentacion.md
    ├── DOCUMENTACION_COMPLETA.md  # Este archivo
    ├── CASOS DE USOS.png
    ├── DIAGRAMA DE ACTIVIDADES.docx
    ├── DIAGRAMA DE CLASES.png
    ├── DIAGRAMA DE COMPONENTES.png
    ├── DIAGRAMA DE DESPLIEGUE.png
    ├── DIAGRAMA DE FLUJO.png
    ├── DIAGRAMA E.R.png
    └── HISTORIAS DE USUARIOS PanApp.xlsx
```

---

## 11. Instalacion y Configuracion

### Requisitos previos

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Servidor web (Apache/Nginx) - se recomienda Laragon en Windows
- Extensiones PHP: `pdo`, `pdo_mysql`, `session`

### Pasos de instalacion

1. **Clonar o copiar el proyecto** en la carpeta `www` de Laragon (o `htdocs` de XAMPP):
   ```
   C:\laragon\www\PanApp\
   ```

2. **Crear la base de datos** ejecutando el script SQL:
   ```sql
   -- En phpMyAdmin o MySQL Workbench:
   SOURCE sql/panaderia2.sql;
   ```

3. **Configurar la conexion** en `config/database.php`:
   ```php
   private $host     = "127.0.0.1";
   private $port     = "3320";      // Puerto de MySQL en Laragon
   private $db_name  = "panaderia2";
   private $username = "root";
   private $password = "";
   ```

4. **Insertar metodos de pago** iniciales (requerido para registrar ventas):
   ```sql
   INSERT INTO metodos_pago (nombre) VALUES ('Efectivo'), ('Tarjeta'), ('Transferencia');
   ```

5. **Crear el primer usuario administrador** desde la vista de registro:
   ```
   http://localhost/PanApp/views/usuarios/registre.php
   ```

6. **Acceder al sistema:**
   ```
   http://localhost/PanApp/public/index.php
   ```

---

## 12. Flujos Principales

### Flujo de Login

```
Usuario ingresa email + password
        |
        v
AuthController::login()
        |
        +-- Validar campos vacios
        +-- Validar formato email
        +-- Buscar usuario en BD
        +-- Verificar cuenta activa
        +-- Verificar password (bcrypt)
        +-- Crear sesion
        |
        v
   Rol = ADMIN?  -->  dashboard/admin.php
   Rol = CAJERO? -->  dashboard/cajero.php
```

### Flujo de Registro de Venta

```
Cajero/Admin selecciona productos (carrito JS)
        |
        v
Selecciona metodo de pago
        |
        v
Confirma con SweetAlert2
        |
        v
VentaController::registrar()
        |
        +-- Validar metodo de pago y total
        +-- Decodificar items JSON
        +-- BEGIN TRANSACTION
        +-- INSERT INTO ventas
        +-- INSERT INTO detalle_venta (por cada item)
        +-- COMMIT
        |
        v
Redirige al dashboard con alerta de exito
```

### Flujo de Ajuste de Inventario

```
Admin selecciona insumo + tipo (entrada/salida) + cantidad
        |
        v
InventarioController::ajustar()
        |
        +-- Validar datos
        +-- Verificar stock suficiente (si es salida)
        +-- BEGIN TRANSACTION
        +-- UPDATE inventario_insumos
        +-- INSERT INTO movimientos_inventario
        +-- COMMIT
        |
        v
Redirige al inventario con alerta de exito
```

---

## 13. Seguridad

| Amenaza | Medida implementada |
|---|---|
| SQL Injection | Sentencias preparadas PDO con `bindParam()` en todas las consultas |
| XSS | `htmlspecialchars()` en todas las salidas de datos en vistas |
| Session Fixation | `session_regenerate_id(true)` al iniciar sesion |
| Acceso no autorizado | Verificacion de sesion y rol al inicio de cada controlador |
| Contrasenas en texto plano | `password_hash()` con PASSWORD_DEFAULT (bcrypt) |
| Escalada de privilegios | Verificacion de rol en cada accion sensible |
| Auto-eliminacion | El admin no puede eliminar su propio usuario |

---

## 14. Consideraciones y Mejoras Futuras

- Agregar recuperacion de contrasena por email.
- Implementar paginacion en tablas con muchos registros.
- Agregar busqueda y filtros en los listados.
- Implementar descuento automatico de insumos al registrar una venta (usando la tabla `producto_insumo`).
- Agregar modulo de caja diaria (apertura/cierre).
- Implementar notificaciones de stock bajo.
- Agregar soporte para imagenes de productos.
- Migrar a un framework PHP (Laravel/Slim) para mayor escalabilidad.
- Agregar pruebas unitarias.
- Implementar HTTPS en produccion.

---

*Documentacion generada para PanApp - Sistema de Gestion de Panaderia - 2026*
