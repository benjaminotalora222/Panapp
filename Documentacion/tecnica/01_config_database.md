# 📄 config/database.php

## Descripción General

Archivo de configuración de la conexión a la base de datos MySQL. Define la clase `Database` que encapsula los parámetros de conexión y expone un método para obtener una instancia PDO.

---

## Ubicación

```
config/database.php
```

---

## Dependencias

| Dependencia | Tipo | Descripción |
|---|---|---|
| PDO (PHP) | Extensión nativa | Driver de acceso a base de datos |
| MySQL | Servidor externo | Motor de base de datos |

---

## Clase: `Database`

### Propiedades privadas

| Propiedad | Valor por defecto | Descripción |
|---|---|---|
| `$host` | `127.0.0.1` | Dirección del servidor MySQL |
| `$port` | `3320` | Puerto personalizado (no el estándar 3306) |
| `$db_name` | `panaderia2` | Nombre de la base de datos |
| `$username` | `root` | Usuario de MySQL |
| `$password` | `""` | Contraseña (vacía en desarrollo) |
| `$conn` | `null` | Instancia de la conexión PDO |

### Método: `conectar()`

**Retorna:** `PDO` — objeto de conexión activa.

**Flujo interno:**

```
1. Inicializa $conn = null
2. Construye el DSN: "mysql:host=...;port=...;dbname=...;charset=utf8mb4"
3. Crea instancia PDO con credenciales
4. Configura ERRMODE_EXCEPTION para lanzar excepciones en errores SQL
5. Retorna $conn
```

**Manejo de errores:**

Si la conexión falla, llama a `die()` con el mensaje de error. Esto detiene completamente la ejecución del script.

---

## Flujo de Uso

```php
$database = new Database();
$db = $database->conectar();
// $db es ahora un objeto PDO listo para consultas
```

---

## ⚠️ Posibles Fallos

| Escenario | Consecuencia | Solución |
|---|---|---|
| Puerto incorrecto (3320 vs 3306) | `die()` con error de conexión | Verificar puerto real de MySQL |
| Contraseña vacía en producción | Vulnerabilidad de seguridad | Usar variables de entorno |
| `die()` en fallo | Muestra error técnico al usuario | Usar excepciones y páginas de error personalizadas |
| Sin charset utf8mb4 | Problemas con caracteres especiales (tildes, ñ) | Ya está configurado correctamente |

---

## 🔒 Consideraciones de Seguridad

- Las credenciales están **hardcodeadas** en el archivo. En producción se recomienda usar variables de entorno (`.env`) o un archivo de configuración fuera del webroot.
- La contraseña vacía es aceptable solo en entornos de desarrollo local.

---

## Archivos que dependen de este

Todos los controladores y vistas que realizan consultas a la base de datos incluyen este archivo:

```php
require_once __DIR__ . '/../config/database.php';
```
