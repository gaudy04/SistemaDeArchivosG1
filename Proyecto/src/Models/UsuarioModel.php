

<?php

namespace App\Models;

namespace App\Models;
use App\DB\connectionDB; 
use App\DB\Sql; // Importa la clase Sql
use App\Config\responseHTTP; 
use App\Config\Security; 

class UsuarioModel extends Sql {
    protected $table = 'usuarios'; // Nombre de la tabla en la BD

    public $id;
    public $nombre;
    public $user_name;
    public $password; // ¡Nunca devuelvas la contraseña en respuestas de API!
    public $departamento_id; // Clave foránea a Departamentos
    public $rol_id; // Clave foránea a Roles (si un usuario tiene un solo rol)

    // Propiedad para almacenar el nombre del rol del usuario actual, útil para los permisos.
    protected $rol_nombre;

    public function __construct() {
        parent::__construct(); // Llama al constructor de Sql
    }

    // Método para obtener todos los usuarios
    public function getAll() {
        // Unimos con la tabla roles para obtener el nombre del rol
        $sql = "SELECT u.id, u.nombre, u.user_name, u.departamento_id, u.rol_id, r.nombre_Rol
                FROM {$this->table} u
                JOIN roles r ON u.rol_id = r.id";
        return $this->fetchAll($sql);
    }

    // Método para obtener un usuario por ID
    public function find($id) {
        $sql = "SELECT u.id, u.nombre, u.user_name, u.departamento_id, u.rol_id, r.nombre_Rol
                FROM {$this->table} u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.id = :id";
        $user = $this->fetch($sql, [':id' => $id]);
        if ($user) {
            $this->id = $user['id'];
            $this->nombre = $user['nombre'];
            $this->user_name = $user['user_name'];
            $this->departamento_id = $user['departamento_id'];
            $this->rol_id = $user['rol_id'];
            $this->rol_nombre = $user['nombre_Rol']; // Guardamos el nombre del rol
        }
        return $user;
    }

    // Método para obtener un usuario por user_name (para login)
    public function findByUsername($username) {
        $sql = "SELECT u.*, r.nombre_Rol
                FROM {$this->table} u
                JOIN roles r ON u.rol_id = r.id
                WHERE u.user_name = :user_name";
        $user = $this->fetch($sql, [':user_name' => $username]);
        if ($user) {
            $this->id = $user['id'];
            $this->nombre = $user['nombre'];
            $this->user_name = $user['user_name'];
            $this->password = $user['password']; // Se necesita para password_verify
            $this->departamento_id = $user['departamento_id'];
            $this->rol_id = $user['rol_id'];
            $this->rol_nombre = $user['nombre_Rol']; // Guardamos el nombre del rol
        }
        return $user;
    }

    // Método para crear un nuevo usuario
    public function create($data) {
        try {
            $this->id = $this->insert($this->table, $data);
            return $this->id;
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new \Exception("El nombre de usuario ya existe o el ID de rol/departamento no es válido.");
            }
            throw $e;
        }
    }

    // Método para actualizar un usuario
    public function update($id, $data) {
        $whereClause = "id = :id";
        $whereParams = [':id' => $id];
        return $this->update($this->table, $data, $whereClause, $whereParams);
    }

    // Método para eliminar un usuario
    public function deleteUser($id) {
        return $this->delete($this->table, "id = :id", [':id' => $id]);
    }

    // Método para verificar si un nombre de usuario ya existe
    public function usernameExists($username, $excludeId = null) {
        return $this->recordExists($this->table, 'user_name', $username, $excludeId);
    }

    // --- Métodos relacionados con los Permisos ---

    /**
     * Obtiene la lista de permisos asociados a un rol específico.
     * En un sistema real, esto se obtendría de la DB (ej. tabla `rol_permiso`).
     * Aquí, lo simulamos con un array hardcodeado.
     *
     * @param string $roleName El nombre del rol (ej. 'usuario', 'administrador').
     * @return array Una lista de permisos.
     */
    private function getPermissionsForRole(string $roleName): array {
        $permissions = [];
        switch (strtolower($roleName)) {
            case 'usuario':
                $permissions = [
                    'Subir Archivo',
                    'Descargar Archivo',
                    'Renombrar Archivo',
                    'Obtener Version Anterior',
                    'Compartir',
                    'Crear Carpeta',
                    'Mover Carpeta'
                ];
                break;
            case 'administrador':
                // Los administradores tienen todos los permisos de usuario más los suyos propios
                $permissions = array_merge(
                    $this->getPermissionsForRole('usuario'), // Hereda los de usuario
                    [
                        'Eliminar Carpeta',
                        'Eliminar Archivo'
                    ]
                );
                break;
            default:
                // Roles desconocidos no tienen permisos
                break;
        }
        return $permissions;
    }

    /**
     * Verifica si el usuario actual tiene un permiso específico.
     * Requiere que el usuario haya sido cargado previamente (ej. con find() o findByUsername()).
     *
     * @param string $permission El permiso a verificar (ej. 'Subir Archivo').
     * @return bool True si el usuario tiene el permiso, false en caso contrario.
     */
    public function hasPermission(string $permission): bool {
        if (!$this->rol_nombre) {
            // El usuario no ha sido cargado o no tiene un rol asignado.
            // Considera una lógica de manejo de errores o lanza una excepción.
            return false;
        }

        $userPermissions = $this->getPermissionsForRole($this->rol_nombre);

        return in_array($permission, $userPermissions);
    }

    // Métodos específicos para verificar cada permiso (facilita la legibilidad en los controladores)
    public function canUploadFile(): bool {
        return $this->hasPermission('Subir Archivo');
    }

    public function canDownloadFile(): bool {
        return $this->hasPermission('Descargar Archivo');
    }

    public function canRenameFile(): bool {
        return $this->hasPermission('Renombrar Archivo');
    }

    public function canGetPreviousVersion(): bool {
        return $this->hasPermission('Obtener Version Anterior');
    }

    public function canShare(): bool {
        return $this->hasPermission('Compartir');
    }

    public function canCreateFolder(): bool {
        return $this->hasPermission('Crear Carpeta');
    }

    public function canMoveFolder(): bool {
        return $this->hasPermission('Mover Carpeta');
    }

    public function canDeleteFolder(): bool {
        return $this->hasPermission('Eliminar Carpeta');
    }

    public function canDeleteFile(): bool {
        return $this->hasPermission('Eliminar Archivo');
    }

    // Puedes agregar más métodos para permisos de administrador si los necesitas
    // Por ejemplo, para ver logs de actividad, asignar roles, etc.
    // public function canViewActivityLogs(): bool {
    //     return $this->rol_nombre === 'administrador';
    // }
    // public function canAssignRoles(): bool {
    //     return $this->rol_nombre === 'administrador';
    // }
}