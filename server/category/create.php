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
if (empty(trim($_POST['name']))) {
    echo json_encode(['success' => false, 'message' => 'El nombre de la categoría es requerido']);
    exit;
}

try {
    $query = "INSERT INTO category (name, user_id) VALUES (:name, :user_id)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        'name' => trim($_POST['name']),
        'user_id' => $user_id
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Categoría creada correctamente',
        'category_id' => $db->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
