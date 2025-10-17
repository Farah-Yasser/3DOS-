<?php

header("Content-Type: application/json; charset=UTF-8");

include "../config/connection.php";
include "../helpers/response.php";
include "../config/config.php";
include "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$config = require __DIR__ . '/../config/config.php';
$secret = $config['jwt_secret'];
$accessExp = $config['access_token_exp'];   
$refreshExp = $config['refresh_token_exp']; 

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (empty($data["username"]) || empty($data["password"])) {
    respond(false, "Username and password are required", null, 400);
}

$username = $data["username"];
$password = $data["password"];

$userQuery = $connect->prepare("
    SELECT u.user_id, u.username, u.password, u.role_id, r.role AS role_name
    FROM user u
    INNER JOIN role r ON u.role_id = r.role_id
    WHERE u.username = ?
");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    respond(false, "Invalid username or password", null, 401);
}

$user = $userResult->fetch_assoc();
$userQuery->close();

if (!password_verify($password, $user["password"])) {
    respond(false, "Incorrect password", null, 401);
}

$now = time();
$payload = [
    "iat" => $now,
    "exp" => $now + $accessExp,
    "sub" => $user["user_id"],
    "data" => [
        "username" => $user["username"],
        "role" => $user["role_name"]
    ]
];

$accessToken = JWT::encode($payload, $secret, 'HS256');

$refreshToken = bin2hex(random_bytes(32)); 
$hashedRefresh = password_hash($refreshToken, PASSWORD_BCRYPT);
$expiresAt = gmdate('Y-m-d H:i:s', $now + $refreshExp);

$insert = $connect->prepare("
    INSERT INTO refresh (user_id, token, expire_at)
    VALUES (?, ?, ?)
");
$insert->bind_param("iss", $user['user_id'], $hashedRefresh, $expiresAt);
$insert->execute();
$insert->close();

respond(true, "Login successful", [
    "access_token" => $accessToken,
    "expires_in" => $accessExp,
    "refresh_token" => $refreshToken,
    "refresh_expires_in" => $refreshExp,
    "user" => [
        "id" => $user['user_id'],
        "username" => $user['username'],
        "role" => $user['role_name']
    ]
]);
?>