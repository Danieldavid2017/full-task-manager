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
if (empty(trim($_POST['title']))) {
    echo json_encode(['success' => false, 'message' => 'El título es requerido']);
    exit;
}

// Validar que la categoría exista y pertenezca al usuario si se proporciona
if (!empty($_POST['category_id'])) {
    try {
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
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error al verificar categoría: ' . $e->getMessage()]);
        exit;
    }
}

try {
    $query = "INSERT INTO task (title, description, due_date, complete, user_id, category_id) 
              VALUES (:title, :description, :due_date, :complete, :user_id, :category_id)";
    
    $stmt = $db->prepare($query);
    
    $completed = isset($_POST['completed']) && ($_POST['completed'] == '1' || $_POST['completed'] == 'on') ? 1 : 0;
    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    
    $stmt->execute([
        'title' => trim($_POST['title']),
        'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
        'due_date' => $dueDate,
        'complete' => $completed,
        'user_id' => $user_id,
        'category_id' => $categoryId
    ]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Tarea creada correctamente',
        'task_id' => $db->lastInsertId()
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
