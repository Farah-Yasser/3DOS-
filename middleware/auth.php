<?php

include "../config/connection.php";
include "../vendor/autoload.php";
include "../config/config.php";
include "../helpers/response.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function verifyJWT() {
    $headers = getallheaders();

    // Debug: Log headers
    error_log("Headers: " . print_r($headers, true));

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Authorization header missing"
        ]);
        exit;
    }
    echo json_encode($headers);

    $authHeader = $headers['Authorization']; // "Bearer <token>"
    $token = str_replace('Bearer ', '', $authHeader);

    // Debug: Log token
    error_log("Token: " . $token);

    $config = require __DIR__ . '/../config/config.php';
    $secret = $config['jwt_secret'];

    try {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        return $decoded;
    } catch (Exception $e) {
        // Debug: Log decoding error
        error_log("JWT Error: " . $e->getMessage());

        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "Invalid or expired token",
            "error" => $e->getMessage()
        ]);
        exit;
    }
}
?>