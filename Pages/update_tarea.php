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

// Validar que se recibieron todos los datos necesarios
if (!isset($data['id']) || !isset($data['titulo']) || !isset($data['descripcion']) || !isset($data['prioridad']) || !isset($data['etiquetas'])) {
    $response['error'] = 'Datos de tarea incompletos.';
    echo json_encode($response);
    exit;
}

$taskId = $data['id'];
$titulo = trim($data['titulo']);
$descripcion = trim($data['descripcion']);
$prioridad = $data['prioridad'];
$etiquetas = trim($data['etiquetas']); // Nuevo campo de etiquetas
$userId = $_SESSION['id'];

// Validar que el título no esté vacío
if (empty($titulo)) {
    $response['error'] = 'El título de la tarea no puede estar vacío.';
    echo json_encode($response);
    exit;
}

// Preparar la sentencia SQL para actualizar la tarea
// Se verifica que la tarea pertenezca al usuario para evitar actualizaciones no autorizadas
$stmt = $conn->prepare("UPDATE tareas SET titulo = ?, descripcion = ?, prioridad = ?, etiquetas = ? WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ssssii", $titulo, $descripcion, $prioridad, $etiquetas, $taskId, $userId);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
    } else {
        // Esto ocurre si no se realizó ningún cambio o si la tarea no se encontró
        $response['error'] = 'No se realizó ningún cambio o la tarea no se encontró.';
    }
} else {
    $response['error'] = 'Error de la base de datos: ' . $stmt->error;
}

$stmt->close();
$conn->close();

echo json_encode($response);