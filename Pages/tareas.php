<?php
session_start();
include '../Config/conexion.php';

// Redirigir si no hay sesión iniciada
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];
$success_message = '';
$error_message = '';

// Lógica para agregar una nueva tarea
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['titulo']) && isset($_POST['descripcion']) && isset($_POST['prioridad'])) {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $prioridad = $_POST['prioridad'];
    $estado = 'pendiente'; 
    $fecha_creacion = date('Y-m-d H:i:s');
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;

    if (empty($titulo)) {
        $error_message = "El título de la tarea no puede estar vacío.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tareas (usuario_id, titulo, descripcion, prioridad, estado, fecha_creacion, fecha_vencimiento) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $usuario_id, $titulo, $descripcion, $prioridad, $estado, $fecha_creacion, $fecha_vencimiento);

        if ($stmt->execute()) {
            $success_message = "Tarea agregada correctamente.";
        } else {
            $error_message = "Error al agregar la tarea: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Obtener todas las tareas del usuario para mostrarlas
$sql = "SELECT * FROM tareas WHERE usuario_id = ? ORDER BY fecha_vencimiento ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$tareas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tareas[] = $row;
    }
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestor de Tareas</title>
    <style>
        body {
            background-color: #1a1a1a;
            font-family: 'Arial', sans-serif;
            color: #e0e0e0;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 50px;
        }
        .container {
            max-width: 800px;
            width: 90%;
            background: #2a2a2a;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3c3c3c;
            padding-bottom: 20px;
        }
        h1 {
            color: #f90;
            font-size: 2.5em;
            margin: 0;
        }
        .header-actions {
            display: flex;
            gap: 15px;
        }
        .header-actions button {
            background-color: #f90;
            color: #1a1a1a;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .header-actions button:hover {
            background-color: #ffa500;
            transform: translateY(-2px);
        }
        .form-container {
            background: #222;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-container h2 {
            margin-top: 0;
            color: #f90;
        }
        #add-task-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        #add-task-form input, #add-task-form textarea, #add-task-form select {
            padding: 15px;
            border: 2px solid #555;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: #e0e0e0;
            font-size: 1em;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        #add-task-form input:focus, #add-task-form textarea:focus, #add-task-form select:focus {
            outline: none;
            border-color: #f90;
        }
        #add-task-form button {
            background-color: #4CAF50; 
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            align-self: flex-end;
        }
        #add-task-form button:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        .task-list-container {
            margin-top: 30px;
        }
        #task-list {
            list-style: none;
            padding: 0;
        }
        .task-item {
            background: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .task-details {
            flex-grow: 1;
            padding-right: 20px;
        }
        .task-title {
            font-weight: bold;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        .task-description {
            font-size: 0.9em;
            color: #ccc;
            margin: 0;
        }
        .task-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .task-actions button {
            background: #555;
            border: none;
            color: #e0e0e0;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .task-actions button:hover {
            background: #f90;
            color: #111;
        }
        .task-actions .delete-btn {
            background-color: #f44336; 
        }
        .task-actions .delete-btn:hover {
            background-color: #d32f2f;
        }
        .task-actions input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #f90;
        }
        .no-tasks {
            text-align: center;
            color: #888;
            margin-top: 50px;
        }
        .message.success {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.error {
            background-color: #f44336;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestor de Tareas</h1>
            <div class="header-actions">
                <a href="perfil.php"><button>Volver a Perfil</button></a>
            </div>
        </header>

        <?php if ($success_message): ?>
            <div class="message success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Crear Nueva Tarea</h2>
            <form id="add-task-form" method="POST" action="">
                <input type="text" name="titulo" placeholder="Título de la tarea" required>
                <textarea name="descripcion" placeholder="Descripción de la tarea (opcional)"></textarea>
                <select name="prioridad" required>
                    <option value="baja">Prioridad: Baja</option>
                    <option value="media" selected>Prioridad: Media</option>
                    <option value="alta">Prioridad: Alta</option>
                </select>
                <input type="datetime-local" name="fecha_vencimiento" placeholder="Fecha de Vencimiento">
                <button type="submit">Agregar Tarea</button>
            </form>
        </div>

        <div class="task-list-container">
            <h2>Mis Tareas</h2>
            <ul id="task-list">
                <?php if (empty($tareas)): ?>
                    <p class="no-tasks">No tienes tareas. ¡Hora de crear una! 🚀</p>
                <?php else: ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <li class="task-item <?php echo ($tarea['estado'] == 'completada') ? 'completed' : ''; ?>" data-id="<?php echo $tarea['id']; ?>">
                            <div class="task-details">
                                <span class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></span>
                                <p class="task-description"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                <small><strong>Prioridad:</strong> <?php echo htmlspecialchars($tarea['prioridad']); ?> | <strong>Estado:</strong> <?php echo htmlspecialchars($tarea['estado']); ?></small>
                            </div>
                            <div class="task-actions">
                                <button class="edit-btn">Editar</button>
                                <button class="delete-btn">Eliminar</button>
                                <input type="checkbox" class="complete-checkbox" <?php echo ($tarea['estado'] == 'completada') ? 'checked' : ''; ?>>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>