<?php
header("Content-Type: application/json");

include "../config/connection.php";
include "../helpers/response.php";

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Validate input
if (empty($data["email"]) || empty($data["token"]) || empty($data["new_password"])) {
    respond(false, "Email, token, and new password are required", null, 400);
}

$email = trim($data["email"]);
$token = trim($data["token"]);
$new_password = password_hash($data["new_password"], PASSWORD_BCRYPT);

// 1️⃣ Get user info
$userQuery = $connect->prepare("SELECT * FROM user WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    respond(false, "User not found", null, 404);
}

$user = $userResult->fetch_assoc();
$user_id = $user['user_id'];
$userQuery->close();

// 2️⃣ Get the latest password reset request
$resetQuery = $connect->prepare("SELECT * FROM pass_reset WHERE user_id = ? ORDER BY reset_id DESC LIMIT 1");
$resetQuery->bind_param("i", $user_id);
$resetQuery->execute();
$resetResult = $resetQuery->get_result();

if ($resetResult->num_rows === 0) {
    respond(false, "No reset request found for this user", null, 404);
}

$row = $resetResult->fetch_assoc();
$resetQuery->close();

// 3️⃣ Check token expiration
if (strtotime($row['expire_at']) < time()) {
    respond(false, "Reset token has expired. Please request a new one.", null, 400);
}

// 4️⃣ Verify token match
if (!password_verify($token, $row['token'])) {
    respond(false, "Invalid reset token", null, 400);
}

// 5️⃣ Update password
$updateQuery = $connect->prepare("UPDATE user SET password = ? WHERE user_id = ?");
$updateQuery->bind_param("si", $new_password, $user_id);

if ($updateQuery->execute()) {
    // 6️⃣ Delete used reset token
    $deleteQuery = $connect->prepare("DELETE FROM pass_reset WHERE user_id = ?");
    $deleteQuery->bind_param("i", $user_id);
    $deleteQuery->execute();
    $deleteQuery->close();

    respond(true, "Password has been reset successfully. You can now log in with your new password.", null, 200);
} else {
    respond(false, "Database error: " . $updateQuery->error, null, 500);
}

$updateQuery->close();
$connect->close();
?>
