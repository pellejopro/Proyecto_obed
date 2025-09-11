<?php
session_start();
include '../Config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $prioridad = $_POST['prioridad'];
    $etiquetas = trim($_POST['etiquetas']); // Nuevo campo para las etiquetas
    $usuario_id = $_SESSION['id'];

    if (empty($titulo)) {
        $_SESSION['message'] = "El título de la tarea es obligatorio.";
        header("Location: tareas.php");
        exit;
    }

    $sql = "INSERT INTO tareas (titulo, descripcion, fecha_vencimiento, prioridad, etiquetas, usuario_id) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $titulo, $descripcion, $fecha_vencimiento, $prioridad, $etiquetas, $usuario_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tarea creada con éxito.";
    } else {
        $_SESSION['message'] = "Error al crear la tarea: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: perfil.php");
    exit;
}
?>