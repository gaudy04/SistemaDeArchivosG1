<?php
/*Actualizado por Lorena */

use App\Config\errorlogs;
use App\Config\ResponseHTTP;

require dirname(__DIR__) . '/vendor/autoload.php';
errorlogs::activa_error_logs();

// Cargar variables de entorno (asegúrate de que esto esté funcionando)
$dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['route'])) {
    $url = explode('/', $_GET['route']);
    $ruta = $url[0];

    // --- ¡AÑADE 'rol' y 'permiso' A LA LISTA AQUÍ! ---
    $lista = ['auth', 'user', 'login', 'dashboard', 'logout', 'rol', 'permiso', 'file', 'version', 'comment', 'department', 'log']; // Agrega también otras entidades de tu diagrama
    // --- FIN DE LA ADICIÓN ---

    $file = dirname(__DIR__) . '/src/Routes/' . $ruta . '.php';

    if (!in_array($ruta, $lista)) {
        echo json_encode(ResponseHTTP::status404('La ruta no existe'));
        error_log("Intento de acceso a ruta no permitida: " . $ruta); // Registro más específico
        exit;
    }

    if (!file_exists($file) || !is_readable($file)) {
        echo json_encode(ResponseHTTP::status404('El archivo de ruta no existe o no es legible para ' . $ruta)); // Mensaje más descriptivo
        error_log("Archivo de ruta no encontrado o no legible: " . $file);
        exit;
    }

    // Proteger rutas que requieren autenticación
    // Aquí puedes definir qué rutas requieren que el usuario esté logueado
    $rutasProtegidas = ['dashboard', 'logout', 'user', 'rol', 'permiso', 'file', 'version', 'comment', 'department', 'log']; // Añade todas las rutas de la API que requieren autenticación
    if (in_array($ruta, $rutasProtegidas) && !isset($_SESSION['user_id'])) {
        // Redirigir al login si no está autenticado
        header('Location: /Proyecto/Public/login'); // Ajusta la ruta base de tu proyecto
        exit;
    }

    require $file; // Carga el archivo de ruta correspondiente (ej. src/Routes/rol.php)
    exit;

} else {
    // Si no se especifica ruta, redirigir al login o al dashboard si ya está logueado
    if (isset($_SESSION['user_id'])) {
        header('Location: /Proyecto/Public/dashboard'); // Ajusta la ruta base de tu proyecto
    } else {
        header('Location: /Proyecto/Public/login'); // Ajusta la ruta base de tu proyecto
    }
    exit;
}

