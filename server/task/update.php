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
if (empty($_POST['task_id']) || empty(trim($_POST['title']))) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

// Validar que la tarea exista y pertenezca al usuario
try {
    $checkQuery = "SELECT id FROM task WHERE id = :id AND user_id = :user_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        'id' => $_POST['task_id'],
        'user_id' => $user_id
    ]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Tarea no encontrada o no autorizada']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al verificar tarea: ' . $e->getMessage()]);
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
    $query = "UPDATE task 
              SET title = :title, 
                  description = :description, 
                  due_date = :due_date, 
                  complete = :complete, 
                  category_id = :category_id
              WHERE id = :id AND user_id = :user_id";
    
    $stmt = $db->prepare($query);
    
    $complete = isset($_POST['completed']) && ($_POST['completed'] == '1' || $_POST['completed'] == 'on') ? 1 : 0;
    $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $categoryId = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    
    $stmt->execute([
        'title' => trim($_POST['title']),
        'description' => isset($_POST['description']) ? trim($_POST['description']) : null,
        'due_date' => $dueDate,
        'complete' => $complete,
        'category_id' => $categoryId,
        'id' => $_POST['task_id'],
        'user_id' => $user_id
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Tarea actualizada correctamente']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
