<?php

namespace App\Controllers;

use App\Models\PermisoModel;

class PermisoController {

    protected $permisoModel;

    // Reutilizar constantes o definir nuevas si son muy específicas
    public const VALIDAR_ID = '/^[1-9]\d*$/';
    public const VALIDAR_NOMBRE_PERMISO = '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{3,50}$/'; // Nombre del permiso
    public const VALIDAR_TIPO_ACCION = '/^[a-zA-Z]{3,20}$/'; // Ej. 'lectura', 'escritura', 'eliminar'

    public function __construct() {
        $this->permisoModel = new PermisoModel();
    }

    private function sendJsonResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    private function validate($value, string $pattern): bool {
        return (bool) preg_match($pattern, (string)$value);
    }

    public function index(): void {
        $permisos = $this->permisoModel->getAll();
        $this->sendJsonResponse(['status' => 'success', 'data' => $permisos]);
    }

    public function show(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de permiso inválido.'], 400);
        }

        $permiso = $this->permisoModel->find($id);
        if ($permiso) {
            $this->sendJsonResponse(['status' => 'success', 'data' => $permiso]);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Permiso no encontrado.'], 404);
        }
    }

    public function store(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        $errors = [];
        if (!isset($input['Nombre_Permiso']) || !$this->validate($input['Nombre_Permiso'], self::VALIDAR_NOMBRE_PERMISO)) {
            $errors[] = 'El nombre del permiso es requerido y debe ser texto válido (3-50 caracteres).';
        } elseif ($this->permisoModel->permisoExists($input['Nombre_Permiso'])) {
            $errors[] = 'El nombre del permiso ya existe.';
        }
        if (!isset($input['Tipo_Accion']) || !$this->validate($input['Tipo_Accion'], self::VALIDAR_TIPO_ACCION)) {
            $errors[] = 'El tipo de acción es requerido y debe ser texto válido (3-20 caracteres).';
        }

        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        try {
            $permisoId = $this->permisoModel->create($input);
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Permiso creado exitosamente.', 'id' => $permisoId], 201);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al crear permiso: ' . $e->getMessage()], 500);
        }
    }

    public function update(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de permiso inválido.'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $errors = [];
        if (isset($input['Nombre_Permiso'])) {
            if (!$this->validate($input['Nombre_Permiso'], self::VALIDAR_NOMBRE_PERMISO)) {
                $errors[] = 'El nombre del permiso debe ser texto válido (3-50 caracteres).';
            } elseif ($this->permisoModel->permisoExists($input['Nombre_Permiso'], $id)) {
                $errors[] = 'El nombre del permiso ya existe para otro registro.';
            }
        }
        if (isset($input['Tipo_Accion']) && !$this->validate($input['Tipo_Accion'], self::VALIDAR_TIPO_ACCION)) {
            $errors[] = 'El tipo de acción debe ser texto válido (3-20 caracteres).';
        }

        if (empty($input)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Datos para actualizar son requeridos.'], 400);
        }

        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        try {
            $rowsAffected = $this->permisoModel->update($id, $input);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Permiso actualizado exitosamente.']);
            } else {
                $this->sendJsonResponse(['status' => 'info', 'message' => 'No se realizaron cambios en el permiso o permiso no encontrado.'], 200);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al actualizar permiso: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de permiso inválido.'], 400);
        }

        try {
            $rowsAffected = $this->permisoModel->deletePermiso($id);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Permiso eliminado exitosamente.'], 204);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Permiso no encontrado para eliminar.'], 404);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al eliminar permiso: ' . $e->getMessage()], 500);
        }
    }
}