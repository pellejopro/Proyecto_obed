<?php
session_start();
include '../Config/conexion.php';

header('Content-Type: application/json');

$response = ['success' => false, 'error' => '', 'notificaciones' => []];

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id'])) {
    $response['error'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

$userId = $_SESSION['id'];
$hoy = date('Y-m-d');
$fecha_limite = date('Y-m-d', strtotime('+3 days')); // Próximos 3 días

// Consulta corregida para usar la tabla y columnas correctas
$stmt = $conn->prepare("SELECT id, titulo, fecha_vencimiento FROM tareas WHERE usuario_id = ? AND estado = 'pendiente' AND fecha_vencimiento BETWEEN ? AND ?");
$stmt->bind_param("iss", $userId, $hoy, $fecha_limite);

if ($stmt->execute()) {
    $result = $stmt->get_result();
    $notificaciones = [];

    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }

    $response['success'] = true;
    $response['notificaciones'] = $notificaciones;
} else {
    $response['error'] = 'Error en la base de datos: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);
