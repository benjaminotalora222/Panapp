# Bugfix Requirements Document

## Introduction

Al abrir el modal de detalle de venta ("Venta #XXXX") desde la vista `views/ventas/index.php`, el sistema no carga la información correctamente: muestra el mensaje "⚠️ Error al cargar el detalle." y los campos CAJERO y MÉTODO DE PAGO permanecen vacíos ("—"). El problema impide que administradores y cajeros consulten el detalle de cualquier venta registrada.

La causa raíz es la URL hardcodeada `/PanApp/controllers/VentaDetalleController.php` en la llamada `fetch()` de la función JavaScript `verDetalleVentaModal()`. Cuando el proyecto se sirve desde una ruta diferente a `/PanApp/` (por ejemplo desde la raíz del servidor, desde `localhost/`, o desde cualquier otro subdirectorio), el servidor devuelve una página HTML de error 404 o una redirección en lugar del JSON esperado. La función `r.json()` falla al intentar parsear HTML, lo que cae directamente en el bloque `.catch()` y muestra el mensaje de error sin poblar ningún campo del modal.

Un problema secundario también identificado: cuando el backend devuelve `data.error` (por ejemplo, "No autorizado" o "ID inválido"), el código solo ejecuta `alert(data.error)` pero nunca oculta el spinner de carga ni muestra el error dentro del modal, dejando la interfaz en un estado inconsistente.

---

## Bug Analysis

### Current Behavior (Defect)

1.1 WHEN el usuario hace clic en el botón de detalle de una venta y el proyecto está desplegado en una ruta que no sea exactamente `/PanApp/` THEN el sistema envía la petición `fetch` a una URL incorrecta, recibe una respuesta HTML (404 u otra), falla al parsear JSON y muestra "⚠️ Error al cargar el detalle." sin poblar ningún campo del modal.

1.2 WHEN la petición fetch falla por URL incorrecta THEN el sistema deja los campos CAJERO y MÉTODO DE PAGO mostrando "—" (valor vacío inicial) en lugar del nombre del cajero y el método de pago real.

1.3 WHEN el backend responde con un objeto de error JSON (`data.error`) THEN el sistema muestra únicamente un `alert()` nativo del navegador pero no oculta el spinner de carga ni despliega el error dentro del modal, dejando la UI bloqueada con el spinner visible.

---

### Expected Behavior (Correct)

2.1 WHEN el usuario hace clic en el botón de detalle de una venta, sin importar la ruta de despliegue del proyecto THEN el sistema SHALL construir la URL del endpoint usando la ruta base dinámica del proyecto (equivalente a `APP_BASE` definido en `config/app.php`) de modo que la petición `fetch` siempre llegue al controlador correcto y devuelva el JSON esperado.

2.2 WHEN la petición fetch es exitosa y el backend devuelve los datos de la venta THEN el sistema SHALL poblar correctamente los campos CAJERO y MÉTODO DE PAGO del modal con los valores reales (`nombres + apellidos` y `metodo_pago`) obtenidos de la respuesta JSON.

2.3 WHEN el backend devuelve un objeto de error JSON (`data.error`) THEN el sistema SHALL ocultar el spinner de carga, mostrar el mensaje de error dentro del cuerpo del modal (en el elemento `#mdv2-loading`) y NO dejar la interfaz con el spinner bloqueado.

---

### Unchanged Behavior (Regression Prevention)

3.1 WHEN el proyecto está desplegado en la ruta `/PanApp/` (configuración original) y el usuario abre el detalle de una venta existente THEN el sistema SHALL CONTINUE TO mostrar correctamente toda la información de la venta: número, fecha, cajero, método de pago, estado, lista de productos y total.

3.2 WHEN el usuario está autenticado con sesión válida y solicita el detalle de una venta THEN el sistema SHALL CONTINUE TO requerir autenticación activa antes de devolver datos, rechazando peticiones sin sesión con el error "No autorizado".

3.3 WHEN el usuario abre el modal de detalle de una venta con estado "completada" o "pendiente" THEN el sistema SHALL CONTINUE TO mostrar el botón "Anular Venta" en el modal.

3.4 WHEN el usuario abre el modal de nueva venta o utiliza cualquier otra funcionalidad de la vista de ventas (filtros, búsqueda, registro de nueva venta) THEN el sistema SHALL CONTINUE TO funcionar sin cambios en dichos flujos.

3.5 WHEN el administrador consulta el detalle de una venta con `id_metodo_pago` o `id_usuario` correctamente relacionados en la base de datos THEN el sistema SHALL CONTINUE TO devolver el nombre del método de pago y el nombre completo del cajero mediante los JOINs existentes en `VentaDetalleController.php`.

---

## Apéndice: Condición del Bug (Bug Condition Methodology)

**Bug Condition Function:**
```pascal
FUNCTION isBugCondition(X)
  INPUT: X of type FetchRequest
  OUTPUT: boolean

  // El bug se dispara cuando la URL del fetch no coincide
  // con la ruta real del controlador en el servidor
  RETURN X.baseUrl ≠ SERVER_ACTUAL_BASE_PATH
END FUNCTION
```

**Property — Fix Checking:**
```pascal
// Property: La petición fetch siempre alcanza VentaDetalleController.php
FOR ALL X WHERE isBugCondition(X) DO
  result ← verDetalleVentaModal'(X.ventaId)
  ASSERT result.httpStatus = 200
    AND result.contentType = 'application/json'
    AND modal.cajero ≠ '—'
    AND modal.metodoPago ≠ '—'
END FOR
```

**Property — Preservation Checking:**
```pascal
// Property: El comportamiento en entornos con ruta /PanApp/ no cambia
FOR ALL X WHERE NOT isBugCondition(X) DO
  ASSERT verDetalleVentaModal(X) = verDetalleVentaModal'(X)
END FOR
```
