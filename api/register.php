<?php

include "../config/connection.php";     
include "../helpers/response.php";  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$input = file_get_contents("php://input");
$data = json_decode($input, true);


if (empty($data["username"]) || empty($data["email"]) || empty($data["password"])) {
    respond(false, "All fields are required", null, 400);
}

$username = trim($data["username"]);
$email = trim($data["email"]);
$password = $data["password"];
$role_id = isset($data["role_id"]) ? (int)$data["role_id"] : 2; 

$roleQuery = $connect->prepare("SELECT role_id FROM role WHERE role_id = ?");
$roleQuery->bind_param("i", $role_id);
$roleQuery->execute();
$roleResult = $roleQuery->get_result();

if ($roleResult->num_rows === 0) {
    $roleQuery->close();
    respond(false, "Invalid role", null, 400);
}
$roleQuery->close();

$check = $connect->prepare("SELECT user_id FROM user WHERE username = ?");
$check->bind_param("s", $username);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    $check->close();
    respond(false, "Username already exists", null, 400);
}
$check->close();

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $connect->prepare("INSERT INTO user (username, email, password, role_id) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $username, $email, $hashedPassword, $role_id);

if ($stmt->execute()) {
    // http_response_code(201); 
    // echo json_encode([
    //     "success" => true,
    //     "message" => "User registered successfully",
    //     "data" => [
    //         "username" => $username,
    //         "role_id" => $role_id
    //     ]
    // ]);
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'farahyasser2904@gmail.com';
    $mail->Password = 'jaqe kfzs tozm qshz';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('farahyasser2904@gmail.com', 'Hers Team');
    $mail->addAddress($data["email"], $data["username"]);
    $mail->isHTML(true);
    $mail->Subject = 'Welcome to Hers';
    $mail->Body    = '<h1>Welcome ' . $data["username"] . '!</h1>
    <p>Thank you for registering at <b>Hers</b>. We are excited to have you on board!</p><br>
    <p>You can now log in and start exploring our products.</p>
    <br>
    <p>Best regards,<br>Hers Team</p>';
    if($mail->send()) {
        respond(true, "User registered successfully and welcome email sent", null, 201);
    } else {
        respond(true, "User registered successfully but failed to send welcome email", null, 201);
    }

} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $stmt->error
    ]);
}
$stmt->close();
$connect->close();

?>