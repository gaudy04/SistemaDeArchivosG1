<?php
	namespace App\Config; //nombre de espacios
	use Dotenv\Dotenv; //variables de entorno 
	use Firebase\JWT\JWT; 
	class Security {
        final public static function secretkey(){
            $dotenv = Dotenv::createImmutable(dirname(__DIR__,2));
            
            $dotenv->load();
            return $_ENV['SECRET_KEY'];
        }

        final public static function createPassword(string $pass){
            $pass = password_hash($pass,PASSWORD_DEFAULT);
            return $pass;
        }
        
        final public static function validatePassword(string $pw, string $pwh){
            if(password_verify($pw,$pwh)){
                return TRUE;
            }else {
                error_log('la contraseña es incorrecta');
                return FALSE;
            }
        }

        final public static function createTokenjwt(string $key, array $data){
            $payload = array(
                'iat' => time(),
                'exp' => time() + (60*60*6),
                'data' => $data
            );
            $jwt = JWT::encode($payload,$key);
            return $jwt;
    }

    final public static function validateTokenjwt(string $key){
        if(!isset(getallheaders(){'Authorization'})){
            //echo 'el token de acceso requerido'
            die(json_encode(ResponseHTTP::status400()));
            exit;
        }
        try{
            $jwt = explode('',getallheaders()['Authorization']);
            $data = JWT::decode($jwt[1],$key,array('HS256'));

            self::$jwt_data = $data;
            return $data;
            exit;
        }catch(Exception $e){
            error_log('token invalido o expiro'. $e);
            die(json_encode(ResponseHTTP::status401('token invalido o ha expirado')));
        }
    }
    
    final public static function getDataJwt(){
        $jwt_decoded_array = json_decode(json_encode(self::$jwt_data),true);
        return $jwt_decoded_array['data'];
        exit;
    
    }
 }