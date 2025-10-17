<?php
// date_default_timezone_set('Africa/Cairo');

header("Content-Type: application/json;");

$localhost= "localhost";     
$username= "root";          
$password= "";               
$database= "jwt_api_db";   

$connect = new mysqli($localhost, $username, $password, $database);


if ($connect->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $connect->connect_error
    ]);
    exit;
}
?>
