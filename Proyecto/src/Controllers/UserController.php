<?php

namespace App\Controllers;

use App\Models\UsuarioModel; // Importa tu modelo de usuario
use App\Models\RolModel;     // Si necesitaras validar el rol_id contra la tabla de roles
use App\Models\DepartamentoModel; // Si necesitaras validar el departamento_id

class UserController {

    protected $userModel;
    protected $rolModel; // Opcional: para validar si un rol_id existe en la BD
    protected $departamentoModel; // Opcional: para validar si un departamento_id existe en la BD

    // --- Atributos estáticos para validación ---
    // (Definidos como constantes para mayor seguridad y claridad)
    public const VALIDAR_TEXTO = '/^[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{3,50}$/'; // Texto alfabético, espacios, tildes, ñ, 3-50 caracteres
    public const VALIDAR_USERNAME = '/^[a-zA-Z0-9_]{4,20}$/'; // Alfanumérico y guion bajo, 4-20 caracteres
    public const VALIDAR_EMAIL = '/^[^\s@]+@[^\s@]+\.[^\s@]+$/'; // Formato de email básico
    public const VALIDAR_PASSWORD = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'; // Mínimo 8 caracteres, 1 mayúscula, 1 minúscula, 1 número, 1 caracter especial
    public const VALIDAR_ID = '/^[1-9]\d*$/'; // Números enteros positivos (para IDs)
    public const VALIDAR_BOOLEANO = '/^(0|1)$/'; // Para valores booleanos (0 o 1)

    public function __construct() {
        $this->userModel = new UsuarioModel();
        // $this->rolModel = new RolModel(); // Descomentar si necesitas validar la existencia del rol_id
        // $this->departamentoModel = new DepartamentoModel(); // Descomentar si necesitas validar la existencia del departamento_id
    }

    /**
     * Helper para enviar respuestas JSON.
     * @param array $data Los datos a enviar en la respuesta.
     * @param int $statusCode El código de estado HTTP.
     */
    private function sendJsonResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit();
    }

    /**
     * Valida un valor contra una expresión regular.
     * @param string|int $value El valor a validar.
     * @param string $pattern La expresión regular (debe ser una de las constantes estáticas).
     * @return bool True si es válido, false en caso contrario.
     */
    private function validate($value, string $pattern): bool {
        // Asegura que el valor sea string para preg_match
        return (bool) preg_match($pattern, (string)$value);
    }

    /**
     * Obtener todos los usuarios (GET /api/users).
     */
    public function index(): void {
        $users = $this->userModel->getAll();
        // En un caso real, elimina la contraseña antes de enviar los datos
        $users = array_map(function($user) {
            unset($user['password']);
            return $user;
        }, $users);
        $this->sendJsonResponse(['status' => 'success', 'data' => $users]);
    }

    /**
     * Obtener un usuario por ID (GET /api/users/{id}).
     * @param int $id El ID del usuario.
     */
    public function show(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de usuario inválido.'], 400);
        }

        $user = $this->userModel->find($id);
        if ($user) {
            unset($user['password']); // Nunca enviar la contraseña
            $this->sendJsonResponse(['status' => 'success', 'data' => $user]);
        } else {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado.'], 404);
        }
    }

    /**
     * Crear un nuevo usuario (POST /api/users).
     */
    public function store(): void {
        $input = json_decode(file_get_contents('php://input'), true);

        // Validaciones de los campos de entrada
        $errors = [];
        if (!isset($input['nombre']) || !$this->validate($input['nombre'], self::VALIDAR_TEXTO)) {
            $errors[] = 'El nombre es requerido y debe ser texto válido (3-50 caracteres).';
        }
        if (!isset($input['user_name']) || !$this->validate($input['user_name'], self::VALIDAR_USERNAME)) {
            $errors[] = 'El nombre de usuario es requerido y debe ser alfanumérico (4-20 caracteres).';
        } elseif ($this->userModel->usernameExists($input['user_name'])) {
            $errors[] = 'El nombre de usuario ya está en uso.';
        }
        if (!isset($input['password']) || !$this->validate($input['password'], self::VALIDAR_PASSWORD)) {
            $errors[] = 'La contraseña es requerida y debe cumplir los requisitos de seguridad.';
        }
        if (isset($input['departamento_id'])) {
            if (!$this->validate($input['departamento_id'], self::VALIDAR_ID)) {
                $errors[] = 'ID de departamento inválido.';
            }
            // else if (!$this->departamentoModel->find($input['departamento_id'])) {
            //     $errors[] = 'El departamento especificado no existe.';
            // }
        }
        if (isset($input['rol_id'])) {
            if (!$this->validate($input['rol_id'], self::VALIDAR_ID)) {
                $errors[] = 'ID de rol inválido.';
            }
            // else if (!$this->rolModel->find($input['rol_id'])) {
            //     $errors[] = 'El rol especificado no existe.';
            // }
        }

        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        // Hashear la contraseña antes de guardar
        $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);

        try {
            $userId = $this->userModel->create($input);
            $this->sendJsonResponse(['status' => 'success', 'message' => 'Usuario creado exitosamente.', 'id' => $userId], 201);
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al crear usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar un usuario (PUT /api/users/{id}).
     * @param int $id El ID del usuario a actualizar.
     */
    public function update(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de usuario inválido.'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);

        // Validaciones para la actualización (solo si el campo está presente)
        $errors = [];
        if (isset($input['nombre']) && !$this->validate($input['nombre'], self::VALIDAR_TEXTO)) {
            $errors[] = 'El nombre debe ser texto válido (3-50 caracteres).';
        }
        if (isset($input['user_name'])) {
            if (!$this->validate($input['user_name'], self::VALIDAR_USERNAME)) {
                $errors[] = 'El nombre de usuario debe ser alfanumérico (4-20 caracteres).';
            } elseif ($this->userModel->usernameExists($input['user_name'], $id)) { // Excluir el propio ID
                $errors[] = 'El nombre de usuario ya está en uso por otro usuario.';
            }
        }
        if (isset($input['password']) && !$this->validate($input['password'], self::VALIDAR_PASSWORD)) {
            $errors[] = 'La contraseña debe cumplir los requisitos de seguridad.';
        }
        if (isset($input['departamento_id'])) {
            if (!$this->validate($input['departamento_id'], self::VALIDAR_ID)) {
                $errors[] = 'ID de departamento inválido.';
            }
            // else if (!$this->departamentoModel->find($input['departamento_id'])) {
            //     $errors[] = 'El departamento especificado no existe.';
            // }
        }
        if (isset($input['rol_id'])) {
            if (!$this->validate($input['rol_id'], self::VALIDAR_ID)) {
                $errors[] = 'ID de rol inválido.';
            }
            // else if (!$this->rolModel->find($input['rol_id'])) {
            //     $errors[] = 'El rol especificado no existe.';
            // }
        }

        if (!empty($errors)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => $errors], 400);
        }

        // Hashear la contraseña si se está actualizando
        if (isset($input['password'])) {
            $input['password'] = password_hash($input['password'], PASSWORD_DEFAULT);
        }

        try {
            $rowsAffected = $this->userModel->update($id, $input);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Usuario actualizado exitosamente.']);
            } else {
                $this->sendJsonResponse(['status' => 'info', 'message' => 'No se realizaron cambios en el usuario o usuario no encontrado.'], 200);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al actualizar usuario: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar un usuario (DELETE /api/users/{id}).
     * @param int $id El ID del usuario a eliminar.
     */
    public function destroy(int $id): void {
        if (!$this->validate($id, self::VALIDAR_ID)) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'ID de usuario inválido.'], 400);
        }

        try {
            $rowsAffected = $this->userModel->deleteUser($id);
            if ($rowsAffected > 0) {
                $this->sendJsonResponse(['status' => 'success', 'message' => 'Usuario eliminado exitosamente.'], 204); // 204 No Content
            } else {
                $this->sendJsonResponse(['status' => 'error', 'message' => 'Usuario no encontrado para eliminar.'], 404);
            }
        } catch (\Exception $e) {
            $this->sendJsonResponse(['status' => 'error', 'message' => 'Error al eliminar usuario: ' . $e->getMessage()], 500);
        }
    }
}