<?php
session_start();
include '../Config/conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => ''];

if (!isset($_SESSION['id'])) {
    $response['error'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['estado'])) {
    $response['error'] = 'ID de tarea o estado no proporcionado.';
    echo json_encode($response);
    exit;
}

$taskId = $data['id'];
$estado = $data['estado']; // 'completada' o 'pendiente'
$userId = $_SESSION['id'];

// Validar que el estado sea un valor permitido
if (!in_array($estado, ['completada', 'pendiente'])) {
    $response['error'] = 'Estado no válido.';
    echo json_encode($response);
    exit;
}

// Preparar la sentencia SQL para actualizar el estado
$stmt = $conn->prepare("UPDATE tareas SET estado = ? WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("sii", $estado, $taskId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
    } else {
        $response['error'] = 'No se realizó ningún cambio o la tarea no se encontró.';
    }
} else {
    $response['error'] = 'Error de la base de datos: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>