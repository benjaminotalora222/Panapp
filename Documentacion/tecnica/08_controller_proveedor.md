# 📄 controllers/ProveedorController.php

## Descripción General

Controlador del módulo de proveedores. Gestiona el CRUD completo de proveedores. Exclusivo para el rol **ADMIN**.

---

## Ubicación

```
controllers/ProveedorController.php
```

---

## Dependencias

| Archivo/Tabla | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `proveedores` | Tabla principal del módulo |

---

## Control de Acceso

Solo `ADMIN`. Sin acceso → redirige a `proveedores/index.php`.

---

## Acción: `crear` — POST

**Campo obligatorio:** `nombre`

**Campos opcionales:** `telefono`, `correo`, `direccion`, `estado` (default: `'activo'`)

**SQL:**
```sql
INSERT INTO proveedores (nombre, telefono, correo, direccion, estado, fecha_registro)
VALUES (..., NOW())
```

---

## Acción: `editar` — POST + GET `?id=`

Actualiza todos los campos del proveedor.

**SQL:**
```sql
UPDATE proveedores
SET nombre=..., telefono=..., correo=..., direccion=..., estado=...
WHERE id_proveedor = :id
```

---

## Acción: `eliminar` — GET `?id=`

**SQL:**
```sql
DELETE FROM proveedores WHERE id_proveedor = :id
```

> ⚠️ Si el proveedor tiene insumos asociados (FK en tabla `insumos`), la BD lanzará error. El controlador muestra "Este proveedor tiene insumos asociados."

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Eliminar proveedor con insumos | Error de FK capturado | Mensaje de error amigable |
| Correo de proveedor inválido | No hay validación de formato | Agregar `filter_var()` para el correo |
| Estado con valor inesperado | Se guarda tal cual | Validar que sea `'activo'` o `'inactivo'` |
