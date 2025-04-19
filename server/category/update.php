<?php
require '../commons/db.php';
header('Content-Type: application/json');

session_start();

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si hay un usuario en la sesión o se proporciona por parámetro
$user_id = isset($_POST['user_id']) ? $_POST['user_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión']);
    exit;
}

// Validar datos recibidos
if (empty($_POST['category_id']) || empty(trim($_POST['name']))) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Primero verificamos que la categoría pertenezca al usuario
    $checkQuery = "SELECT id FROM category WHERE id = :id AND user_id = :user_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        'id' => $_POST['category_id'],
        'user_id' => $user_id
    ]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Categoría no encontrada o no autorizada']);
        exit;
    }
    
    // Actualizamos la categoría
    $query = "UPDATE category SET name = :name WHERE id = :id AND user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'name' => trim($_POST['name']),
        'id' => $_POST['category_id'],
        'user_id' => $user_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Categoría actualizada correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
