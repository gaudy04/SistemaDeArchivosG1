<?php

// src/Routes/permiso.php

use App\Controllers\PermisoController;
use App\Config\ResponseHTTP; // Para enviar respuestas HTTP

header('Access-Control-Allow-Origin: *'); // Permite CORS, ajusta en producción
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$controller = new PermisoController();
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route']; // Obtiene la ruta completa, ej. 'permiso/1', 'permiso'
$urlSegments = explode('/', $route);

switch ($method) {
    case 'GET':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->show($id); // GET /api/permiso/{id}
        } else {
            $controller->index(); // GET /api/permiso
        }
        break;
    case 'POST':
        $controller->store(); // POST /api/permiso
        break;
    case 'PUT':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->update($id); // PUT /api/permiso/{id}
        } else {
            ResponseHTTP::status400('ID de permiso no proporcionado para la actualización.');
        }
        break;
    case 'DELETE':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->destroy($id); // DELETE /api/permiso/{id}
        } else {
            ResponseHTTP::status400('ID de permiso no proporcionado para la eliminación.');
        }
        break;
    default:
        ResponseHTTP::status405('Método no permitido para esta ruta.'); // Método no permitido
        break;
}