<?php

/*(Gestor de Consultas Dinámicas y Verificación)
Esta clase será útil para centralizar la lógica de ejecución de consultas y preparación de sentencias.*/

namespace App\DB;

use PDO;
use PDOException;

class Sql extends connectionDB {

    // Método genérico para ejecutar una consulta preparada
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Error de consulta SQL: " . $e->getMessage() . " | SQL: " . $sql . " | Params: " . json_encode($params));
            // Dependiendo del entorno, puedes relanzar la excepción o devolver null
            throw new PDOException("Error al ejecutar la consulta: " . $e->getMessage());
        }
    }

    // Método para obtener un solo registro
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Método para obtener múltiples registros
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Método para insertar un registro
    public function insert($table, $data) {
        $columns = implode(", ", array_keys($data));
        $placeholders = ":" . implode(", :", array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return $this->getConnection()->lastInsertId(); // Retorna el ID del último insertado
    }

    // Método para actualizar un registro
    public function update($table, $data, $whereClause, $whereParams) {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $set = implode(", ", $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$whereClause}";
        $params = array_merge($data, $whereParams); // Unir parámetros de SET y WHERE
        return $this->query($sql, $params)->rowCount(); // Retorna el número de filas afectadas
    }

    // Método para eliminar un registro
    public function delete($table, $whereClause, $whereParams) {
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        return $this->query($sql, $whereParams)->rowCount(); // Retorna el número de filas afectadas
    }

    /**
     * Verifica si un registro existe en una tabla.
     * @param string $table Nombre de la tabla.
     * @param string $column Columna a verificar (ej. 'email', 'username').
     * @param mixed $value Valor a buscar.
     * @param int|null $excludeId ID de un registro a excluir (útil para actualizaciones).
     * @return bool True si el registro existe, False en caso contrario.
     */
    public function recordExists($table, $column, $value, $excludeId = null) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$column} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->query($sql, $params);
        return (bool) $stmt->fetchColumn(); // fetchColumn(0) obtiene el valor de la primera columna
    }
}