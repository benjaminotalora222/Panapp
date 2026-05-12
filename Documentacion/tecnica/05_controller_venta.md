# 📄 controllers/VentaController.php

## Descripción General

Controlador de ventas. Gestiona el registro de nuevas ventas y la anulación de ventas existentes. Usa transacciones de base de datos para garantizar la integridad de los datos.

---

## Ubicación

```
controllers/VentaController.php
```

---

## Dependencias

| Archivo | Descripción |
|---|---|
| `config/database.php` | Conexión a la base de datos |
| Tablas: `ventas`, `detalle_venta` | Tablas afectadas |

---

## Control de Acceso

Requiere sesión activa (cualquier rol). Sin sesión → redirige a `login.php`.

---

## Acción: `registrar` — POST

### Datos recibidos del formulario

| Campo POST | Tipo | Descripción |
|---|---|---|
| `id_metodo_pago` | int | ID del método de pago seleccionado |
| `total` | float | Total calculado en el frontend |
| `items` | JSON string | Array de productos del carrito |

### Estructura de `items`

```json
[
  {
    "id_producto": 1,
    "cantidad": 2,
    "precio_unitario": 5000,
    "subtotal": 10000
  }
]
```

### Flujo con Transacción

```
1. Validar id_metodo > 0 y total > 0
2. Decodificar JSON de items
3. BEGIN TRANSACTION
4. INSERT INTO ventas (fecha, total, id_usuario, id_metodo_pago, estado='completada')
5. Para cada item:
   - Validar id_producto > 0 y cantidad > 0
   - INSERT INTO detalle_venta
6. COMMIT
7. Guardar alerta de éxito en sesión
8. Redirigir a ventas/index.php
```

**En caso de error:** `ROLLBACK` + alerta de error + redirige a `crear.php`.

---

## Acción: `anular` — GET `?accion=anular&id=`

### Validaciones

1. ID de venta válido (> 0)
2. Venta existe en BD
3. Permisos: Admin puede anular cualquier venta; Cajero solo las suyas
4. Venta no esté ya anulada

### Proceso

```
UPDATE ventas SET estado = 'anulada' WHERE id_venta = :id
```

> ⚠️ La anulación **no revierte el stock** ni los registros de `detalle_venta`. Solo cambia el estado.

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| `items` JSON malformado | `json_decode()` retorna null → error | Validado con `empty($items)` |
| `total` manipulado desde el frontend | Precio incorrecto en BD | Recalcular total en el servidor |
| Producto eliminado después de agregar al carrito | `id_producto` inválido | El `continue` en el loop lo omite silenciosamente |
| Fallo a mitad de inserción de items | ROLLBACK garantiza consistencia | Transacción ya implementada |
| Sin verificación de stock | Se puede vender más de lo disponible | Agregar validación de stock en el servidor |

---

## 🔒 Consideración Importante

El `total` se recibe del **frontend** (JavaScript). Un usuario malicioso podría manipularlo. Para mayor seguridad, el total debería recalcularse en el servidor consultando los precios reales de la BD.
