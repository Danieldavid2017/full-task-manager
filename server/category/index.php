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
    $query = "SELECT id, name, user_id FROM category WHERE user_id = :user_id ORDER BY name";
    $stmt = $db->prepare($query);
    $stmt->execute(['user_id' => $user_id]);
    
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categories);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la base de datos: ' . $e->getMessage()]);
    exit;
}
?>
