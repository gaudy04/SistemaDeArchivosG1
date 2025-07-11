<?php



use App\Controllers\RolController;
use App\Config\ResponseHTTP; // Para enviar respuestas HTTP

header('Access-Control-Allow-Origin: *'); // Permite CORS, ajusta en producción
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Manejar preflight requests (OPTIONS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

$controller = new RolController();
$method = $_SERVER['REQUEST_METHOD'];
$route = $_GET['route']; // Obtiene la ruta completa, ej. 'rol/1', 'rol'
$urlSegments = explode('/', $route);

switch ($method) {
    case 'GET':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->show($id); // GET /api/rol/{id}
        } else {
            $controller->index(); // GET /api/rol
        }
        break;
    case 'POST':
        $controller->store(); // POST /api/rol
        break;
    case 'PUT':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->update($id); // PUT /api/rol/{id}
        } else {
            ResponseHTTP::status400('ID de rol no proporcionado para la actualización.');
        }
        break;
    case 'DELETE':
        if (isset($urlSegments[1]) && is_numeric($urlSegments[1])) {
            $id = (int) $urlSegments[1];
            $controller->destroy($id); // DELETE /api/rol/{id}
        } else {
            ResponseHTTP::status400('ID de rol no proporcionado para la eliminación.');
        }
        break;
    default:
        ResponseHTTP::status405('Método no permitido para esta ruta.'); // Método no permitido
        break;
}