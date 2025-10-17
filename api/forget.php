<?php
header("Content-Type: application/json");

include "../config/connection.php";
include "../helpers/response.php";
include "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (empty($data["email"])) {
    respond(false, "Email is required", null, 400);
}
$email = $data["email"];

// ✅ 1. Check if user exists
$user = "SELECT * FROM user WHERE email = ?";
$userQuery = $connect->prepare($user);
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    respond(false, "User not found", null, 404);
}

$user = $userResult->fetch_assoc();
$user_id = $user['user_id'];
$userQuery->close();

// ✅ 2. Create token and expiration
$token = bin2hex(random_bytes(32));
$hashedToken = password_hash($token, PASSWORD_BCRYPT);
$expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

// ✅ 3. Delete old reset tokens properly (fixed bug)
$old = "DELETE FROM pass_reset WHERE user_id = ?";
$oldQuery = $connect->prepare($old);
$oldQuery->bind_param("i", $user_id);
$oldQuery->execute();
$oldQuery->close();

// ✅ 4. Insert new token
$new = "INSERT INTO pass_reset (user_id, token, expire_at) VALUES (?, ?, ?)";
$newQuery = $connect->prepare($new);
$newQuery->bind_param("iss", $user_id, $hashedToken, $expiresAt);

if ($newQuery->execute()) {

    // ✅ 5. Generate reset link
    $reset_link = "http://localhost/jwt_api/api/reset.php?token=$token&email=$email";

    // ✅ 6. Set up PHPMailer securely
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "farahyasser2904@gmail.com"; // Your Gmail
        $mail->Password = "jaqe kfzs tozm qshz"; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("farahyasser2904@gmail.com", "Hers Team");
        $mail->addAddress($email, $user['username']);
        $mail->isHTML(true); // ✅ important: send as HTML
        $mail->Subject = "Password Reset Request";
        $mail->Body = "
            <h2>Hello {$user['username']},</h2>
            <p>We received a request to reset your password. Please click the link below to set a new password. This link will expire in 1 hour.</p>
            <p><a href=\"$reset_link\">Reset Password</a></p>
            <p>If you did not request a password reset, please ignore this message.</p>
            <p>Best regards,<br>Hers Team</p>
        ";

        if ($mail->send()) {
            respond(true, "Password reset link has been sent to your email", null, 200);
        } else {
            respond(false, "Failed to send email", null, 500);
        }
    } catch (Exception $e) {
        respond(false, "Mailer Error: " . $mail->ErrorInfo, null, 500);
    }

} else {
    respond(false, "Database error: " . $newQuery->error, null, 500);
}

$newQuery->close();
$connect->close();
?>
