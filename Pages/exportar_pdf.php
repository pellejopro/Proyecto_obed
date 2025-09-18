<?php
// exportar_pdf.php

session_start();

// Asegúrate de que las rutas son correctas
require_once __DIR__ . '/../Config/conexion.php';
require_once __DIR__ . '/../Librerias/fpdf.php';

// Verificar sesión
if (!isset($_SESSION['id'])) {
    die("Acceso denegado.");
}

$usuario_id = $_SESSION['id'];

// La variable de conexión es $conn, no $conexion
$sql = "SELECT id, titulo, descripcion, fecha_vencimiento AS fecha FROM tareas WHERE usuario_id = ? ORDER BY fecha_vencimiento ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if (!$resultado) {
    // Si la consulta falla, muestra el error de la conexión
    die("Error en la consulta: " . $conn->error);
}

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, utf8_decode('Mis Tareas'), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 12);

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $pdf->Cell(0, 10, 'ID: ' . $fila['id'], 0, 1);
        $pdf->Cell(0, 10, 'Título: ' . utf8_decode($fila['titulo']), 0, 1);
        $pdf->MultiCell(0, 10, utf8_decode('Descripción: ' . $fila['descripcion']), 0, 1);
        $pdf->Cell(0, 10, 'Fecha de vencimiento: ' . $fila['fecha'], 0, 1);
        $pdf->Ln(5);
    }
} else {
    $pdf->Cell(0, 10, 'No tienes tareas registradas.', 0, 1);
}

// Descargar como PDF
$pdf->Output('D', 'mis_tareas.pdf');

$stmt->close();
$conn->close();