<?php

header("Content-Type: application/json");
ob_clean();

include "../config/connection.php";
include "../middleware/auth.php";

$decoded = verifyJWT(); // Verify token
$role= $decoded->data->role;
$method = $_SERVER['REQUEST_METHOD'];

if ($role === 'editor' && $method === 'DELETE') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Forbidden: You do not have permission to access this resource!"
        ]);
        exit;
}


if ($role !== 'admin' && $method !== 'GET') {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "Forbidden: You do not have permission to perform this action!"
    ]);
    exit;
}

if($method === 'GET'){
    $page= isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit= isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
    $offset= ($page - 1) * $limit;

    //search and filter by category or order by price
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
    $order = isset($_GET['order']) ? $_GET['order'] : ' ';

    $count="SELECT COUNT(*) as total FROM product WHERE archive = 0";
    $countResult= $connect->query($count);
    $total= $countResult->fetch_assoc()['total'];
    $totalPages= ceil($total / $limit);

    // $query = "SELECT p.*, c.category_name FROM product p LEFT JOIN category c ON p.category_id = c.category_id WHERE p.archive = 0 LIMIT $limit OFFSET $offset";
    // $result = $connect->query($query);
    $query= "SELECT p.*, c.category_name FROM product p
    LEFT JOIN category c ON p.category_id = c.category_id
    WHERE p.archive = 0";

    if($search){
        $query .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";
    }
    if($category_id){
        $query .= " AND p.category_id = $category_id";
    }
    if($order === 'asc'){
        $query .= " ORDER BY p.price ASC, p.name ASC";
    } elseif($order === 'desc'){
        $query .= " ORDER BY p.price DESC, p.name ASC";
    } else{
        $query .= " ORDER BY p.name ASC";
    }
    $query .= " LIMIT $limit OFFSET $offset";
    $result= $connect->query($query);
    
    $products = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode([
            "success" => true,
            "message" => "Products retrieved successfully",
            "data" => $products,
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
            "message" => "No products found",
            "data" => []
        ]);
    }
    
    $connect->close();
}

elseif ($method === 'POST') {
    if (isset($_POST["products"])) {
        $data = json_decode($_POST["products"], true);
    } else {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);
    }

    if (empty($data) || !is_array($data)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "No valid product data provided"]);
        exit;
    }

    $responses = [];

    foreach ($data as $index => $product) {
        if (empty($product['name']) || empty($product['price']) || empty($product['category_id'])) {
            $responses[] = [
                "success" => false,
                "message" => "Missing required fields for product index $index"
            ];
            continue;
        }

        // Check category existence
        $category = $connect->prepare("SELECT category_id FROM category WHERE category_id = ?");
        $category->bind_param("i", $product["category_id"]);
        $category->execute();
        $catResult = $category->get_result();
        if ($catResult->num_rows === 0) {
            $responses[] = [
                "success" => false,
                "message" => "Invalid category ID for product: " . $product['name']
            ];
            continue;
        }
        $category->close();

        $image = null;
        if (isset($_FILES['image']['name'][$index]) && $_FILES['image']['error'][$index] === UPLOAD_ERR_OK) {
            $allowed = ["jpg", "jpeg", "png", "gif"];
            $ext = strtolower(pathinfo($_FILES['image']['name'][$index], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $uploadDir = __DIR__ . "/uploads/";
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

                $newName = uniqid("img_", true) . "." . $ext;
                $uploadPath = $uploadDir . $newName;

                if (move_uploaded_file($_FILES['image']['tmp_name'][$index], $uploadPath)) {
                    $image = "uploads/" . $newName;
                }
            }
        }

        $archive = 0;
        $insert = "INSERT INTO product (name, description, price, quantity, image, category_id, archive)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($insert);
        $stmt->bind_param(
            "ssdisii",
            $product['name'],
            $product['description'],
            $product['price'],
            $product['quantity'],
            $image,
            $product['category_id'],
            $archive
        );

        if ($stmt->execute()) {
            $responses[] = [
                "success" => true,
                "message" => "Product added successfully",
                "data" => [
                    "product_id" => $stmt->insert_id,
                    "name" => $product['name'],
                    "description" => $product['description'] ?? null,
                    "price" => $product['price'],
                    "quantity" => $product['quantity'] ?? 0,
                    "image" => $image,
                    "category_id" => $product['category_id']
                ]
            ];
        } else {
            $responses[] = [
                "success" => false,
                "message" => "Database error for product " . $product['name'] . ": " . $stmt->error
            ];
        }
        $stmt->close();
    }

    http_response_code(207);
    echo json_encode([
        "success" => true,
        "message" => "Products added successfully",
        "results" => $responses
    ]);
}

elseif($method === 'PUT' && isset($_GET['action'])){
    if(!isset($_GET['product_id']) || !isset($_GET['action'])){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product ID and valid action are required"
        ]);
        exit;
    }
    $product_id= (int)$_GET['product_id'];
    $action= $_GET['action'];

    if($action === 'archive'){
        $archive= 1;
        $message= "Product archived successfully";
    } elseif($action === 'unarchive'){
        $archive= 0;
        $message= "Product unarchived successfully";
    } else{
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);
        exit;
    }

    $check= "SELECT * FROM product WHERE product_id = ?";
    $stmt= $connect->prepare($check);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result= $stmt->get_result(); 

    if($result->num_rows === 0){
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $update= "UPDATE product SET archive = ? WHERE product_id = ?";
    $stmt= $connect->prepare($update);
    $stmt->bind_param("ii", $archive, $product_id);
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => $message,
            "product_id" => $product_id,
            "archive" => $archive
        ]);
    } else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();

}

elseif($method === 'PUT'){
    $product_id=isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

    if(!isset($_GET['product_id'])){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product ID is required"
        ]);
        exit;
    }
    
    $check= "SELECT * FROM product WHERE product_id = ? AND archive = 0";
    $stmt= $connect->prepare($check);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result= $stmt->get_result();
    
    if($result->num_rows === 0){
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $old= $result->fetch_assoc();
    $input= file_get_contents("php://input");
    $data= json_decode($input, true);

    if(empty($data)){
        http_response_code(401);
        echo json_encode([
            "success" => false,
            "message" => "No data provided for update",
            "data" => $old
        ]);
        exit;
    }
    $new= [
        "name" => $data['name'] ?? $old['name'],
        "description" => $data['description'] ?? $old['description'],
        "price" => $data['price'] ?? $old['price'],
        "quantity" => $data['quantity'] ?? $old['quantity'],
        "image" => $data['image'] ?? $old['image'],
        "category_id" => $data['category_id'] ?? $old['category_id']
    ];
    $update= "UPDATE product SET name = ?, description = ?, price = ?, quantity = ?, image = ?, category_id = ? WHERE product_id = ?";
    $stmt= $connect->prepare($update);
    $stmt->bind_param("ssdisii", $new['name'], $new['description'], $new['price'], $new['quantity'], $new['image'], $new['category_id'], $product_id);
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product updated successfully",
            "old_data" => $old,
            "new_data" => $new
        ]);
    } else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();

}

elseif($method === 'DELETE'){
    if(!isset($_GET['product_id'])){
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Product ID is required"
        ]);
        exit;
    }
    $product_id= (int)$_GET['product_id'];

    $check= "SELECT * FROM product WHERE product_id = ?";
    $stmt= $connect->prepare($check);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result= $stmt->get_result();

    if($result->num_rows === 0){
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Product not found"
        ]);
        exit;
    }

    $delete= "DELETE FROM product WHERE product_id = ?";
    $stmt= $connect->prepare($delete);
    $stmt->bind_param("i", $product_id);
    if($stmt->execute()){
        http_response_code(200);
        echo json_encode([
            "success" => true,
            "message" => "Product deleted successfully",
            "deleted_product_id" => $product_id
        ]);
    } else{
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $stmt->error
        ]);
    }
    $stmt->close();

}
?>