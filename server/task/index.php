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

try {
    $query = "SELECT t.id, t.title, t.description, t.due_date, t.complete, t.user_id, t.category_id, 
                     c.name as category_name
              FROM task t
              LEFT JOIN category c ON t.category_id = c.id
              WHERE t.user_id = :user_id
              ORDER BY t.complete ASC, t.due_date ASC, t.title ASC";
              
    $stmt = $db->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($tasks);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
