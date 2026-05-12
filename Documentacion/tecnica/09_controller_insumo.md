# 📄 controllers/InsumoController.php

## Descripción General

Controlador del módulo de insumos (materias primas). Gestiona el CRUD de insumos y crea automáticamente su registro en el inventario al crearlos. Exclusivo para **ADMIN**.

---

## Ubicación

```
controllers/InsumoController.php
```

---

## Dependencias

| Archivo/Tabla | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `insumos` | Tabla de insumos |
| `inventario_insumos` | Se crea registro automático al crear insumo |
| `movimientos_inventario` | Se elimina en cascada al eliminar insumo |
| `producto_insumo` | Se elimina en cascada al eliminar insumo |

---

## Control de Acceso

Requiere sesión activa. Si la acción no está vacía y el rol no es `ADMIN` → error de permiso.

---

## Acción: `crear` — POST

**Campos obligatorios:** `nombre`, `unidad_medida`

**Campo opcional:** `id_proveedor` (puede ser `null`)

**Proceso especial al crear:**
```
1. INSERT INTO insumos
2. Obtener lastInsertId()
3. INSERT INTO inventario_insumos con cantidad_actual = 0
```

Esto garantiza que todo insumo nuevo aparezca en el inventario con stock 0.

---

## Acción: `editar` — POST + GET `?id=`

Actualiza nombre, descripción, unidad de medida y proveedor del insumo.

---

## Acción: `eliminar` — GET `?id=`

**Eliminación en cascada manual con transacción:**

```
BEGIN TRANSACTION
1. DELETE FROM movimientos_inventario WHERE id_insumo = :id
2. DELETE FROM inventario_insumos WHERE id_insumo = :id
3. DELETE FROM producto_insumo WHERE id_insumo = :id
4. DELETE FROM insumos WHERE id_insumo = :id
COMMIT
```

> La eliminación en cascada se hace manualmente porque las FK pueden no tener `ON DELETE CASCADE` configurado en la BD.

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Fallo en INSERT de inventario_insumos | No hay transacción en `crear` | Agregar transacción al crear |
| Insumo con relaciones no contempladas | Error de FK en eliminar | El ROLLBACK revierte todo |
| `id_proveedor = 0` | Se convierte a `null` con `?: null` | Ya manejado |
