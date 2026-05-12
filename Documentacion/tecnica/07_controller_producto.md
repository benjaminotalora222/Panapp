# 📄 controllers/ProductoController.php

## Descripción General

Controlador del módulo de productos. Gestiona la creación, edición y eliminación de productos del catálogo. La creación y edición están disponibles para todos los roles; la eliminación es exclusiva del **ADMIN**.

---

## Ubicación

```
controllers/ProductoController.php
```

---

## Dependencias

| Archivo/Tabla | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `productos` | Tabla principal del módulo |

---

## Control de Acceso

Requiere sesión activa (cualquier rol). Sin sesión → redirige a `login.php`.

---

## Acción: `crear` — POST

### Campos requeridos

| Campo | Validación |
|---|---|
| `nombre` | No vacío |
| `categoria` | No vacío |
| `precio` | > 0 |
| `unidad_medida` | No vacío |
| `descripcion` | Opcional |

**SQL:**
```sql
INSERT INTO productos (nombre, descripcion, categoria, precio, unidad_medida)
VALUES (...)
```

---

## Acción: `editar` — POST + GET `?id=`

Mismas validaciones que `crear`, más verificación de `id > 0`.

**SQL:**
```sql
UPDATE productos
SET nombre=..., descripcion=..., categoria=..., precio=..., unidad_medida=...
WHERE id_producto = :id
```

---

## Acción: `eliminar` — GET `?id=`

**Restricción de rol:**
```php
if (strtoupper($_SESSION['usuario']['rol']) !== 'ADMIN') {
    // Error: sin permiso
}
```

**SQL:**
```sql
DELETE FROM productos WHERE id_producto = :id
```

> ⚠️ Si el producto tiene ventas asociadas en `detalle_venta`, la BD lanzará un error de FK. El controlador captura la excepción y muestra "El producto está asociado a ventas existentes."

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Precio = 0 | Validado, muestra error | Ya manejado |
| Nombre duplicado | No hay restricción UNIQUE en BD | Agregar validación si se requiere |
| Eliminar producto con ventas | Error de FK capturado | Mensaje de error amigable |
| Cajero intenta eliminar | Error de permiso | Ya protegido |
