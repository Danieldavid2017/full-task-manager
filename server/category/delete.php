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
if (empty($_POST['category_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de categoría requerido']);
    exit;
}

try {
    // Iniciar transacción para eliminar categoría y actualizar tareas relacionadas
    $db->beginTransaction();
    
    // Verificar que la categoría pertenezca al usuario
    $checkQuery = "SELECT id FROM category WHERE id = :id AND user_id = :user_id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->execute([
        'id' => $_POST['category_id'],
        'user_id' => $user_id
    ]);
    
    if ($checkStmt->rowCount() === 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Categoría no encontrada o no autorizada']);
        exit;
    }
    
    // Establecer a NULL la categoría en las tareas relacionadas o eliminarlas
    // (dependiendo de los requisitos del sistema)
    $updateTasksQuery = "UPDATE task SET category_id = NULL WHERE category_id = :category_id AND user_id = :user_id";
    $updateTasksStmt = $db->prepare($updateTasksQuery);
    $updateTasksStmt->execute([
        'category_id' => $_POST['category_id'],
        'user_id' => $user_id
    ]);
    
    // Eliminar la categoría
    $deleteQuery = "DELETE FROM category WHERE id = :id AND user_id = :user_id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->execute([
        'id' => $_POST['category_id'],
        'user_id' => $user_id
    ]);
    
    // Confirmar transacción
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
