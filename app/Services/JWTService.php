<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTService
{

    //! Private______________________________________________________________________________________________________
    private $payload;

    //! Public_______________________________________________________________________________________________________

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function getJwtToken(): string
    {
        $jwt_token = JWT::encode($this->payload, config('app.JWT_SECRET'), 'HS256');
        return $jwt_token;
    }

    public static function checkTokenValidity(string $jwt_token): array
    {
        // $jwt_token = explode(" ", $jwt_token)[1];

        $decoded_jwt = JWT::decode($jwt_token, new Key(config('app.JWT_SECRET'), 'HS256'));

        $jwt_arr = (array) $decoded_jwt;

        return $jwt_arr;

    }

    public static function getTokenPayload(string $jwt_token): array {
        $body_b64 = \explode('.', $jwt_token)[1];

        $payload_raw = JWT::urlsafeB64Decode($body_b64);

        $payload = JWT::jsonDecode($payload_raw);

        return (array)$payload;
    }
}
