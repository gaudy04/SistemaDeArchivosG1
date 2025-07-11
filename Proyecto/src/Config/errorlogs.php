<?php 

namespace App\Config;
class errorlogs{
    public static function activa_error_logs(){
        error_reporting(E_ALL);

        ini_set('ignore_repeated_errors',TRUE);
        ini_set('display_errors',FALSE);
        ini_set('log_errors',TRUE);
        ini_set('error_log',dirname(__DIR__).'/Logs/php-error.log');
    	date_default_timezone_set('America/Tegucigalpa');

    }
}

