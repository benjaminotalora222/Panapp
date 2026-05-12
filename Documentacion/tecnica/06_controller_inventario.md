# 📄 controllers/InventarioController.php

## Descripción General

Controlador del módulo de inventario. Gestiona los ajustes de stock de insumos (entradas y salidas). Exclusivo para el rol **ADMIN**.

---

## Ubicación

```
controllers/InventarioController.php
```

---

## Dependencias

| Archivo/Tabla | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| `inventario_insumos` | Tabla de stock actual por insumo |
| `movimientos_inventario` | Historial de movimientos |

---

## Control de Acceso

Solo usuarios con rol `ADMIN`. Sin acceso → redirige a `inventario/index.php`.

---

## Acción: `ajustar` — POST

### Datos recibidos

| Campo | Tipo | Valores válidos |
|---|---|---|
| `id_insumo` | int | > 0 |
| `tipo` | string | `'entrada'` o `'salida'` |
| `cantidad` | int | > 0 |
| `motivo` | string | Opcional, texto libre |

### Flujo con Transacción

```
1. Validar campos (id_insumo > 0, tipo válido, cantidad > 0)
2. BEGIN TRANSACTION
3. Buscar registro en inventario_insumos para el insumo
4. Si tipo='salida' y stock < cantidad → error "Stock insuficiente"
5. Si registro existe:
   - entrada: nueva_cantidad = actual + cantidad
   - salida:  nueva_cantidad = actual - cantidad
   - UPDATE inventario_insumos
6. Si registro NO existe:
   - Si tipo='salida' → error "Sin stock registrado"
   - Si tipo='entrada' → INSERT inventario_insumos con cantidad inicial
7. INSERT movimientos_inventario (historial)
8. COMMIT
9. Alerta de éxito → redirige a inventario/index.php
```

---

## Tablas Afectadas

### `inventario_insumos`
```sql
UPDATE inventario_insumos
SET cantidad_actual = :cantidad, fecha_actualizacion = NOW()
WHERE id_insumo = :id
```

### `movimientos_inventario`
```sql
INSERT INTO movimientos_inventario (id_insumo, tipo, cantidad, motivo, fecha)
VALUES (:id_insumo, :tipo, :cantidad, :motivo, NOW())
```

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Salida con stock insuficiente | Error detectado y mostrado | Ya validado |
| Insumo sin registro en inventario_insumos | Solo falla en salida; entrada crea el registro | Ya manejado |
| Fallo en INSERT de movimiento | ROLLBACK revierte el UPDATE de stock | Transacción garantiza consistencia |
| `cantidad` = 0 o negativa | Validado antes de procesar | Ya validado |
| Acceso de Cajero | Redirige sin procesar | Ya protegido |
