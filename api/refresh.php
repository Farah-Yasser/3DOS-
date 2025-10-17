<?php
header("Content-Type: application/json");

include "../config/connection.php";
include "../config/config.php";
include "../helpers/response.php";
include "../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

date_default_timezone_set('Africa/Cairo'); // Adjust if needed

$config = require __DIR__ . '/../config/config.php';
$secret = $config['jwt_secret'];
$accessExp = $config['access_token_exp'];
$refreshExp = $config['refresh_token_exp'];

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (empty($data['username']) || empty($data['refresh_token'])) {
    respond(false, "Username and refresh token are required", null, 400);
}

$username = trim($data['username']);
$refresh_token = trim($data['refresh_token']);

// Step 1: Find user
$userQuery = $connect->prepare("SELECT user_id, username, role_id FROM user WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    respond(false, "User not found", null, 404);
}
$user = $userResult->fetch_assoc();
$userQuery->close();

// Step 2: Get most recent refresh token for that user
$tokenQuery = $connect->prepare("SELECT * FROM refresh WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$tokenQuery->bind_param("i", $user['user_id']);
$tokenQuery->execute();
$tokenResult = $tokenQuery->get_result();

if ($tokenResult->num_rows === 0) {
    respond(false, "No refresh token found. Please log in again.", null, 401);
}

$stored = $tokenResult->fetch_assoc();
$tokenQuery->close();

// Step 3: Check expiry
if (strtotime($stored['expire_at']) < time()) {
    respond(false, "Refresh token has expired. Please log in again.", null, 401);
}

// Step 4: Verify the token hash
if (!password_verify($refresh_token, $stored['token'])) {
    respond(false, "Invalid refresh token. Please log in again.", null, 401);
}

// Step 5: Generate new access token
$now = time();
$payload = [
    'iat' => $now,
    'exp' => $now + $accessExp,
    'sub' => $user['user_id'],
    'data' => [
        'username' => $user['username'],
        'role' => $user['role_id']
    ]
];

$new_access_token = JWT::encode($payload, $secret, 'HS256');

// Step 6: Respond with new token
respond(true, "New access token generated successfully", [
    "access_token" => $new_access_token,
    "expires_in" => $accessExp
]);
?>
