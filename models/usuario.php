<?php
class Usuario {
    private $conn;
    private $tabla = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    // ─── Verificar si ya existe un correo registrado ───
    public function existeCorreo($email) {
        $sql = "SELECT id_usuario FROM " . $this->tabla . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // ─── Obtener usuario activo por email (para login) ───
    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE email = :email LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ─── Registrar nuevo usuario ───
    public function registrar($datos) {
        try {
            $sql = "INSERT INTO " . $this->tabla . "
                        (nombres, apellidos, email, password_hash, rol, activo, created_at)
                    VALUES
                        (:nombres, :apellidos, :email, :password_hash, :rol, 1, NOW())";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":nombres",       $datos['nombres']);
            $stmt->bindParam(":apellidos",     $datos['apellidos']);
            $stmt->bindParam(":email",         $datos['email']);
            $stmt->bindParam(":password_hash", $datos['password_hash']);
            $stmt->bindParam(":rol",           $datos['rol']);
            $stmt->execute();

            return true;

        } catch (Exception $e) {
            return "Error al registrar: " . $e->getMessage();
        }
    }

    // ─── Obtener todos los usuarios ───
    public function obtenerTodos() {
        $sql = "SELECT id_usuario, nombres, apellidos, email, rol, activo, created_at
                FROM " . $this->tabla . "
                ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ─── Obtener usuario por ID ───
    public function obtenerPorId($id_usuario) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ─── Actualizar datos de un usuario ───
    public function actualizar($id_usuario, $datos) {
        try {
            $sql = "UPDATE " . $this->tabla . "
                    SET nombres   = :nombres,
                        apellidos = :apellidos,
                        email     = :email,
                        rol       = :rol";

            if (!empty($datos['password_hash'])) {
                $sql .= ", password_hash = :password_hash";
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":nombres",    $datos['nombres']);
            $stmt->bindParam(":apellidos",  $datos['apellidos']);
            $stmt->bindParam(":email",      $datos['email']);
            $stmt->bindParam(":rol",        $datos['rol']);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

            if (!empty($datos['password_hash'])) {
                $stmt->bindParam(":password_hash", $datos['password_hash']);
            }

            $stmt->execute();
            return true;

        } catch (Exception $e) {
            return "Error al actualizar: " . $e->getMessage();
        }
    }

    // ─── Activar o desactivar usuario ───
    public function cambiarEstado($id_usuario, $activo) {
        try {
            $sql = "UPDATE " . $this->tabla . " SET activo = :activo WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":activo",     $activo,     PDO::PARAM_INT);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al cambiar estado: " . $e->getMessage();
        }
    }

    // ─── Eliminar usuario por ID ───
    public function eliminar($id_usuario) {
        try {
            $sql = "DELETE FROM " . $this->tabla . " WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }
}
?>