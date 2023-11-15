<?php
use Firebase\JWT\JWT;

if (!class_exists('Firebase\JWT\JWT')) {
    die('La librería Firebase JWT no se cargó correctamente.'); // es la librería
}

class AuthJWT {

    // Clave secreta utilizada para codificar y decodificar JWT
    private static $secretKey = 'clave';

    // Algoritmo de encriptación para JWT
    private static $encryptionAlgorithm = 'HS256';

    // Costantes 
    private const EXPIRATION_TIME = 30000; // 5 minutos en segundos


    // Crea un token JWT con los datos especificados
    public static function createToken($data) {
        $now = time();
        $payload = array(
            'iat' => $now,                                          // Current timestamp
            'exp' => $now + self::EXPIRATION_TIME,                  // Expiración: actual + constante
            'data' => $data,                                        // Datos a almacenar en el token
        );
        return JWT::encode($payload, self::$secretKey, self::$encryptionAlgorithm);  // Codifica el payload usando la clave secreta
    }

    // Verifica la autenticidad de un token JWT
    public static function verifyToken($token) {
        if (empty($token) || $token == "") {
            throw new Exception("El token está vacío");
        } else {
            try {
                $decoded = JWT::decode($token, self::$secretKey);   // Decodifica el token usando la clave secreta
            } catch (Exception $e) {
                throw $e;  // Si la decodificación falla, arroja una excepción
            }
        }
        return $decoded;  // Devuelve el token decodificado
    }

    // Obtiene el payload de un token JWT
    public static function getPayload($token) {
        if (empty($token) || $token == "") {
            throw new Exception("El token está vacío");
        }
        return JWT::decode($token, self::$secretKey);  // Decodifica el token y devuelve el payload
    }

    // Obtiene los datos almacenados en un token JWT
    public static function getData($token) {
        return JWT::decode($token, self::$secretKey)->data;  // Decodifica el token y devuelve los datos
    }
}
?>
