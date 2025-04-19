<?php
require '../commons/db.php';
header('Content-Type: application/json');

session_start();

// Verificar si hay un usuario en la sesión o se proporciona por parámetro
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

if (!$user_id) {
    echo json_encode(['error' => 'No se ha iniciado sesión']);
    exit;
}

// Validar datos recibidos
if (empty($_GET['task_id'])) {
    echo json_encode(['error' => 'ID de tarea requerido']);
    exit;
}

try {
    $query = "SELECT id, title, description, due_date, complete, user_id, category_id
              FROM task 
              WHERE id = :task_id AND user_id = :user_id";
              
    $stmt = $db->prepare($query);
    $stmt->execute([
        'task_id' => $_GET['task_id'],
        'user_id' => $user_id
    ]);
    
    $task = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$task) {
        echo json_encode(['error' => 'Tarea no encontrada o no autorizada']);
        exit;
    }
    
    echo json_encode($task);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
