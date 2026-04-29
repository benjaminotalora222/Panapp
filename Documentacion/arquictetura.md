##  Arquitectura del Software

PanApp está diseñado bajo una arquitectura de tipo **cliente-servidor**, utilizando una estructura organizada que separa las responsabilidades del sistema para facilitar su mantenimiento y escalabilidad.

---

##  Tipo de Arquitectura
Se implementa una arquitectura basada en el patrón **MVC (Modelo - Vista - Controlador)**:

- **Modelo (Model):**
  Se encarga de la gestión de datos y la lógica del negocio. Aquí se manejan las entidades como productos, ventas, inventario, proveedores e insumos.

- **Vista (View):**
  Representa la interfaz de usuario. Permite la interacción con el sistema a través de páginas web.

- **Controlador (Controller):**
  Gestiona la comunicación entre el modelo y la vista, procesando las solicitudes del usuario y devolviendo respuestas.

---

##  Componentes del Sistema

### 1. Cliente (Frontend)
- Interfaz web accesible desde el navegador.
- Permite a los usuarios (Administrador y Cajero) interactuar con el sistema.
- Tecnologías típicas: PHP

### 2. Servidor (Backend)
- Desarrollado en Python.
- Procesa la lógica del sistema.
- Maneja autenticación, gestión de datos y reglas del negocio.

### 3. Base de Datos
- Almacena toda la información del sistema:
  - Usuarios
  - Productos
  - Ventas
  - Inventario
  - Proveedores
  - Insumos

---

##  Flujo de Funcionamiento

1. El usuario accede desde el navegador (cliente).
2. Realiza una acción (ej: registrar una venta).
3. El controlador recibe la solicitud.
4. Se procesa la lógica en el modelo.
5. Se consulta o actualiza la base de datos.
6. Se devuelve una respuesta al usuario en la vista.

---

##  Control de Acceso
El sistema maneja roles de usuario:

- **Administrador:**
  Acceso completo a todas las funcionalidades.

- **Cajero:**
  Acceso limitado, principalmente a ventas y reportes.

---

## 📊 Ventajas de la Arquitectura
- Separación clara de responsabilidades.
- Facilita el mantenimiento del código.
- Permite escalar el sistema en el futuro.
- Mejora la organización del proyecto.