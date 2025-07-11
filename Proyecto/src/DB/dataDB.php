<?php

use App\Config\errorlogs;
use App\Config\ResponseHTTP;
use App\DB\connectionDB;
use Dotenv\Dotenv;

// Activar registro de errores
errorlogs::activa_error_logs();

// Cargar variables de entorno desde .env
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

// Recoger datos de entorno
$data = array(
    'user'     => $_ENV['USER'],
    'password' => $_ENV['PASSWORD'],
    'DB'       => $_ENV['DB'],
    'IP'       => $_ENV['IP'],
    'port'     => $_ENV['PORT']
);

// Construir DSN para PDO
$host = 'mysql:host=' . $data['IP'] . ';port=' . $data['port'] . ';dbname=' . $data['DB'];

// Inicializar conexión a base de datos
connectionDB::inicializar($host, $data['user'], $data['password']);

