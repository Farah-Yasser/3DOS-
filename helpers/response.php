<?php

function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    header("Content-Type: application/json; charset=UTF-8");
    $res = [
        "success" => $success,
        "message" => $message
    ];
    if (!is_null($data)) $res['data'] = $data;
    echo json_encode($res);
    exit;
}

?>