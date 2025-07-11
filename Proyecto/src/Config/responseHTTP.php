<?php
namespace App\Config;

class ResponseHTTP {
    private static $mensaje = [
        'status' => '',
        'message' => '',
        'data' => null
    ];

    public static function status100(string $res) {
        self::$mensaje['status'] = '100';
        self::$mensaje['message'] = $res;
        http_response_code(100);
        return self::get();
    }

    public static function status101(string $res) {
        self::$mensaje['status'] = '101';
        self::$mensaje['message'] = $res;
        http_response_code(101);
        return self::get();
    }

    public static function status200(string $res) {
        self::$mensaje['status'] = '200';
        self::$mensaje['message'] = $res;
        http_response_code(200);
        return self::get();
    }

    public static function status201(string $res) {
        self::$mensaje['status'] = '201';
        self::$mensaje['message'] = $res;
        http_response_code(201);
        return self::get();
    }

    public static function status202(string $res) {
        self::$mensaje['status'] = '202';
        self::$mensaje['message'] = $res;
        http_response_code(202);
        return self::get();
    }

    public static function status301(string $res) {
        self::$mensaje['status'] = '301';
        self::$mensaje['message'] = $res;
        http_response_code(301);
        return self::get();
    }

    public static function status302(string $res) {
        self::$mensaje['status'] = '302';
        self::$mensaje['message'] = $res;
        http_response_code(302);
        return self::get();
    }

    public static function status304(string $res) {
        self::$mensaje['status'] = '304';
        self::$mensaje['message'] = $res;
        http_response_code(304);
        return self::get();
    }

    public static function status400(string $res) {
        self::$mensaje['status'] = '400';
        self::$mensaje['message'] = $res;
        http_response_code(400);
        return self::get();
    }

    public static function status401(string $res) {
        self::$mensaje['status'] = '401';
        self::$mensaje['message'] = $res;
        http_response_code(401);
        return self::get();
    }

    public static function status403(string $res) {
        self::$mensaje['status'] = '403';
        self::$mensaje['message'] = $res;
        http_response_code(403);
        return self::get();
    }

    public static function status404(string $res) {
        self::$mensaje['status'] = '404';
        self::$mensaje['message'] = $res;
        http_response_code(404);
        return self::get();
    }

    public static function status405(string $res) {
        self::$mensaje['status'] = '405';
        self::$mensaje['message'] = $res;
        http_response_code(405);
        return self::get();
    }

    public static function status422(string $res) {
        self::$mensaje['status'] = '422';
        self::$mensaje['message'] = $res;
        http_response_code(422);
        return self::get();
    }

    public static function status500(string $res) {
        self::$mensaje['status'] = '500';
        self::$mensaje['message'] = $res;
        http_response_code(500);
        return self::get();
    }

    public static function status501(string $res) {
        self::$mensaje['status'] = '501';
        self::$mensaje['message'] = $res;
        http_response_code(501);
        return self::get();
    }

    public static function status503(string $res) {
        self::$mensaje['status'] = '503';
        self::$mensaje['message'] = $res;
        http_response_code(503);
        return self::get();
    }

    public static function get(): array {
        return self::$mensaje;
    }
}


