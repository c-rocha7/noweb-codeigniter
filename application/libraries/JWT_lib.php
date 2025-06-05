<?php

defined('BASEPATH') or exit('No direct script access allowed');

class JWT_lib
{

    private $secret_key;

    public function __construct()
    {
        $this->secret_key = 'sua_chave_secreta_muito_forte_aqui_2024';
    }

    /**
     * Gerar token JWT
     */
    public function generate_token($payload)
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload['iat'] = time();
        $payload['exp'] = time() + (24 * 60 * 60);
        $payload = json_encode($payload);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secret_key, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    /**
     * Validar token JWT
     */
    public function validate_token($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($header, $payload, $signature) = $parts;

        // Verificar assinatura
        $valid_signature = hash_hmac('sha256', $header . "." . $payload, $this->secret_key, true);
        $valid_signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($valid_signature));

        if ($signature !== $valid_signature) {
            return false;
        }

        // Decodificar payload
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);

        // Verificar expiração
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }
}
