<?php
// Inicia la sesión de PHP
session_start();

// Verifica que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

// Incluye la conexión a la base de datos
include '../Config/conexion.php';

// Asegúrate de que la solicitud sea un POST y el contenido sea JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['CONTENT_TYPE']) || strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Solicitud no válida']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

// Verifica que el array de IDs exista y no esté vacío
if (!isset($input['ids']) || !is_array($input['ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'IDs de tareas no proporcionados']);
    exit;
}

$ids = $input['ids'];
$usuario_id = $_SESSION['id'];

// Inicia una transacción para asegurar la integridad de los datos
$conn->begin_transaction();
try {
    // Recorre los IDs y actualiza la posición en la base de datos
    foreach ($ids as $index => $id) {
        $posicion = $index + 1;
        $stmt = $conn->prepare("UPDATE tareas SET posicion = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("iii", $posicion, $id, $usuario_id);
        $stmt->execute();
        $stmt->close();
    }

    // Confirma la transacción
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Si algo sale mal, revierte los cambios
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error al reordenar las tareas: ' . $e->getMessage()]);

} finally {
    $conn->close();
}
?>