<?php

namespace App\Controllers;

use App\Models\RolModel;

class RolController {

    protected $rolModel;

    // Puedes reutilizar las constantes de validación de UserController o definirlas aquí si no quieres acoplarte
    public const VALIDAR_ID = '/^[1-9]\d*$/';
    public const VALIDAR_NOMBRE_ROL = '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{3,30}$/'; // Nombre de rol más específico

    public function __construct() {
        $this->rolModel = new RolModel();
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
        $roles = $this->rolModel->getAll();
        $this->sendJsonResponse(['status' => 'success', 'data' => $roles]);
    }

    public function show(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de rol inválido.'], 400);
        }

        $rol = $this->rolModel->find($id);
        if ($rol) {
            $this->sendJsonResponse(['status' => 'success', 'data' => $rol]);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Rol no encontrado.'], 404);
        }
    }

    public function store(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        $errors = [];
        if (!isset($input['nombre_Rol']) || !$this->validate($input['nombre_Rol'], self::VALIDAR_NOMBRE_ROL)) {
            $errors[] = 'El nombre del rol es requerido y debe ser texto válido (3-30 caracteres).';
        } elseif ($this->rolModel->rolExists($input['nombre_Rol'])) {
            $errors[] = 'El nombre del rol ya existe.';
        }

        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        try {
            $rolId = $this->rolModel->create($input);
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Rol creado exitosamente.', 'id' => $rolId], 201);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al crear rol: ' . $e->getMessage()], 500);
        }
    }

    public function update(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de rol inválido.'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        $errors = [];
        if (isset($input['nombre_Rol'])) {
            if (!$this->validate($input['nombre_Rol'], self::VALIDAR_NOMBRE_ROL)) {
                $errors[] = 'El nombre del rol debe ser texto válido (3-30 caracteres).';
            } elseif ($this->rolModel->rolExists($input['nombre_Rol'], $id)) {
                $errors[] = 'El nombre del rol ya existe para otro registro.';
            }
        } else {
            $errors[] = 'El nombre del rol es requerido para la actualización.';
        }


        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        try {
            $rowsAffected = $this->rolModel->update($id, $input);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Rol actualizado exitosamente.']);
            } else {
                $this->sendJsonResponse(['status' => 'info', 'message' => 'No se realizaron cambios en el rol o rol no encontrado.'], 200);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al actualizar rol: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de rol inválido.'], 400);
        }

        try {
            $rowsAffected = $this->rolModel->deleteRol($id);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Rol eliminado exitosamente.'], 204);
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Rol no encontrado para eliminar.'], 404);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al eliminar rol: ' . $e->getMessage()], 500);
        }
    }
}