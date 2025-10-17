<?php

header("Content-Type: application/json");

include "../config/connection.php";
include "../middleware/auth.php";

$decoded = verifyJWT(); // verify token
$role = $decoded->data->role;
$method = $_SERVER['REQUEST_METHOD'];

if ($role === 'editor' && $method === 'DELETE') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Forbidden: You do not have permission to access this resource!"
        ]);
        exit;
}

// Only admin can modify (POST, PUT, DELETE)
if ($role !== 'admin' && $method !== 'GET') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Forbidden: You do not have permission to perform this action!"
    ]);
    exit;
}

if ($method === 'GET') {
    // Pagination setup
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $offset = ($page - 1) * $limit;

    $countQuery = "SELECT COUNT(*) as total FROM category";
    $countResult = $connect->query($countQuery);
    $total = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($total / $limit);

    $query = "SELECT * FROM category LIMIT $limit OFFSET $offset";
    $result = $connect->query($query);
    $categories = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode([
            "success" => true,
            "message" => "Categories retrieved successfully",
            "data" => $categories,
            "pagination" => [
                "page" => $page,
                "total_pages" => $totalPages,
                "total_items" => $total,
                "items_per_page" => $limit
            ]
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "No categories found",
            "data" => []
        ]);
    }
    $connect->close();
}

elseif ($method === 'POST') {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (empty($data['category_name'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category name is required"
        ]);
        exit;
    }

    $check = "SELECT * FROM category WHERE category_name = ?";
    $stmt = $connect->prepare($check);
    $stmt->bind_param("s", $data['category_name']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode([
            "success" => false,
            "message" => "Category already exists"
        ]);
        exit;
    }
    $stmt->close();


    $insert = "INSERT INTO category (category_name) VALUES (?)";
    $stmt = $connect->prepare($insert);
    $stmt->bind_param("s", $data['category_name']);
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => "Category added successfully",
            "data" => [
                "category_id" => $stmt->insert_id,
                "category_name" => $data['category_name']
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();
}

elseif ($method === 'PUT') {
    if (!isset($_GET['category_id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category ID is required"
        ]);
        exit;
    }

    $category_id = (int)$_GET['category_id'];

    $check = "SELECT * FROM category WHERE category_id = ?";
    $stmt = $connect->prepare($check);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Category not found"
        ]);
        exit;
    }

    $old = $result->fetch_assoc();
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (empty($data)) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "No data provided for update",
            "data" => $old
        ]);
        exit;
    }

    $new = [
        "category_name" => $data['category_name'] ?? $old['category_name']
    ];

    $update = "UPDATE category SET category_name = ? WHERE category_id = ?";
    $stmt = $connect->prepare($update);
    $stmt->bind_param("si", $new['category_name'], $category_id);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Category updated successfully",
            "old_data" => $old,
            "new_data" => $new
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();
}

elseif ($method === 'DELETE') {
    if (!isset($_GET['category_id'])) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Category ID is required"
        ]);
        exit;
    }

    $category_id = (int)$_GET['category_id'];

    $check = "SELECT * FROM category WHERE category_id = ?";
    $stmt = $connect->prepare($check);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Category not found"
        ]);
        exit;
    }

    $delete = "DELETE FROM category WHERE category_id = ?";
    $stmt = $connect->prepare($delete);
    $stmt->bind_param("i", $category_id);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Category deleted successfully",
            "deleted_category_id" => $category_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();
}

?>