<?php

namespace App\Models;

use App\DB\Sql; // Importa la clase Sql
use PDOException; // Importa PDOException para manejar errores de base de datos

class RolModel extends Sql {
    protected $table = 'roles'; // Define el nombre de la tabla de roles

    public $id;
    public $nombre_Rol; // Propiedad para el nombre del rol

    public function __construct() {
        parent::__construct(); // Llama al constructor de la clase padre (Sql) para establecer la conexión a la DB
    }

    /**
     * Obtiene todos los roles de la base de datos.
     * @return array Un array de arrays asociativos con los datos de los roles.
     */
    public function getAll(): array {
        return $this->fetchAll("SELECT id, nombre_Rol FROM {$this->table}");
    }

    /**
     * Encuentra un rol por su ID.
     * @param int $id El ID del rol a buscar.
     * @return array|false Un array asociativo con los datos del rol, o false si no se encuentra.
     */
    public function find(int $id) {
        return $this->fetch("SELECT id, nombre_Rol FROM {$this->table} WHERE id = :id", [':id' => $id]);
    }

    /**
     * Crea un nuevo rol en la base de datos.
     * @param array $data Un array asociativo con los datos del nuevo rol (ej. ['nombre_Rol' => 'Administrador']).
     * @return int El ID del rol recién creado.
     * @throws \Exception Si el nombre del rol ya existe o hay un error en la base de datos.
     */
    public function create(array $data): int {
        try {
            $this->id = $this->insert($this->table, $data);
            return $this->id;
        } catch (PDOException $e) {
            // Maneja el error si el nombre del rol ya existe (código de error de unicidad)
            if ($e->getCode() == '23000') {
                throw new \Exception("El nombre del rol '{$data['nombre_Rol']}' ya existe.");
            }
            throw $e; // Re-lanza otras excepciones de PDO
        }
    }

    /**
     * Actualiza un rol existente en la base de datos.
     * @param int $id El ID del rol a actualizar.
     * @param array $data Un array asociativo con los datos a actualizar (ej. ['nombre_Rol' => 'Nuevo Nombre']).
     * @return int El número de filas afectadas (0 si no se actualizó, 1 si se actualizó).
     * @throws \Exception Si el nombre del rol ya existe (por otro ID) o hay un error en la base de datos.
     */
    public function update(int $id, array $data): int {
        $whereClause = "id = :id";
        $whereParams = [':id' => $id];
        try {
            return $this->update($this->table, $data, $whereClause, $whereParams);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new \Exception("El nombre del rol '{$data['nombre_Rol']}' ya existe para otro registro.");
            }
            throw $e;
        }
    }

    /**
     * Elimina un rol de la base de datos.
     * @param int $id El ID del rol a eliminar.
     * @return int El número de filas afectadas (0 si no se eliminó, 1 si se eliminó).
     */
    public function deleteRol(int $id): int {
        return $this->delete($this->table, "id = :id", [':id' => $id]);
    }

    /**
     * Verifica si un rol con un nombre específico ya existe.
     * @param string $nombreRol El nombre del rol a verificar.
     * @param int|null $excludeId Opcional. ID de un rol a excluir de la verificación (útil para actualizaciones).
     * @return bool True si el rol existe, False en caso contrario.
     */
    public function rolExists(string $nombreRol, ?int $excludeId = null): bool {
        return $this->recordExists($this->table, 'nombre_Rol', $nombreRol, $excludeId);
    }
}