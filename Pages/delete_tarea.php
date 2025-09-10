<?php
session_start();
include '../Config/conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    $response['error'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

// Obtener los datos del cuerpo de la solicitud (JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Validar que se recibió el ID de la tarea
if (!isset($data['id'])) {
    $response['error'] = 'ID de tarea no proporcionado.';
    echo json_encode($response);
    exit;
}

$taskId = $data['id'];
$userId = $_SESSION['id'];

// Preparar la sentencia SQL para eliminar la tarea
// Se verifica que la tarea pertenezca al usuario para evitar eliminaciones no autorizadas
$stmt = $conn->prepare("DELETE FROM tareas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $taskId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
    } else {
        $response['error'] = 'No se encontró la tarea o no tienes permiso para eliminarla.';
    }
} else {
    $response['error'] = 'Error de la base de datos: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>