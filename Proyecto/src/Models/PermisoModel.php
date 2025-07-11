<?php

namespace App\Models;

use App\DB\Sql; // Importa la clase Sql
use PDOException; // Importa PDOException para manejar errores de base de datos

class PermisoModel extends Sql {
    protected $table = 'permisos'; // Define el nombre de la tabla de permisos

    public $id;
    public $Nombre_Permiso; // Propiedad para el nombre del permiso
    public $Tipo_Accion;    // Propiedad para el tipo de acción (ej. 'lectura', 'escritura')

    public function __construct() {
        parent::__construct(); // Llama al constructor de la clase padre (Sql)
    }

    /**
     * Obtiene todos los permisos de la base de datos.
     * @return array Un array de arrays asociativos con los datos de los permisos.
     */
    public function getAll(): array {
        return $this->fetchAll("SELECT id, Nombre_Permiso, Tipo_Accion FROM {$this->table}");
    }

    /**
     * Encuentra un permiso por su ID.
     * @param int $id El ID del permiso a buscar.
     * @return array|false Un array asociativo con los datos del permiso, o false si no se encuentra.
     */
    public function find(int $id) {
        return $this->fetch("SELECT id, Nombre_Permiso, Tipo_Accion FROM {$this->table} WHERE id = :id", [':id' => $id]);
    }

    /**
     * Crea un nuevo permiso en la base de datos.
     * @param array $data Un array asociativo con los datos del nuevo permiso (ej. ['Nombre_Permiso' => 'Subir Archivo', 'Tipo_Accion' => 'escritura']).
     * @return int El ID del permiso recién creado.
     * @throws \Exception Si el nombre del permiso ya existe o hay un error en la base de datos.
     */
    public function create(array $data): int {
        try {
            $this->id = $this->insert($this->table, $data);
            return $this->id;
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new \Exception("El permiso '{$data['Nombre_Permiso']}' ya existe.");
            }
            throw $e;
        }
    }

    /**
     * Actualiza un permiso existente en la base de datos.
     * @param int $id El ID del permiso a actualizar.
     * @param array $data Un array asociativo con los datos a actualizar.
     * @return int El número de filas afectadas.
     * @throws \Exception Si el nombre del permiso ya existe (por otro ID) o hay un error en la base de datos.
     */
    public function update(int $id, array $data): int {
        $whereClause = "id = :id";
        $whereParams = [':id' => $id];
        try {
            return $this->update($this->table, $data, $whereClause, $whereParams);
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') {
                throw new \Exception("El permiso '{$data['Nombre_Permiso']}' ya existe para otro registro.");
            }
            throw $e;
        }
    }

    /**
     * Elimina un permiso de la base de datos.
     * @param int $id El ID del permiso a eliminar.
     * @return int El número de filas afectadas.
     */
    public function deletePermiso(int $id): int {
        return $this->delete($this->table, "id = :id", [':id' => $id]);
    }

    /**
     * Verifica si un permiso con un nombre específico ya existe.
     * @param string $nombrePermiso El nombre del permiso a verificar.
     * @param int|null $excludeId Opcional. ID de un permiso a excluir de la verificación.
     * @return bool True si el permiso existe, False en caso contrario.
     */
    public function permisoExists(string $nombrePermiso, ?int $excludeId = null): bool {
        return $this->recordExists($this->table, 'Nombre_Permiso', $nombrePermiso, $excludeId);
    }
}