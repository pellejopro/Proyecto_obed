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
    $etiquetas = isset($_POST['etiquetas']) ? trim($_POST['etiquetas']) : '';
    $estado = 'pendiente';
    $fecha_creacion = date('Y-m-d H:i:s');
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;

    if (empty($titulo)) {
        $error_message = "El título de la tarea no puede estar vacío.";
    } else {
        $stmt = $conn->prepare("INSERT INTO tareas (usuario_id, titulo, descripcion, prioridad, estado, fecha_creacion, fecha_vencimiento, etiquetas) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $usuario_id, $titulo, $descripcion, $prioridad, $estado, $fecha_creacion, $fecha_vencimiento, $etiquetas);

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
        /* Definición de variables CSS para el modo claro (valores por defecto) */
        :root {
            --color-principal: #f90;
            --color-texto-claro: #e0e0e0;
            --color-texto-oscuro: #1a1a1a;
            --color-fondo-claro: #f0f2f5;
            --color-fondo-oscuro: #121212;
            --color-contenedor-claro: #ffffff;
            --color-contenedor-oscuro: #1f1f1f;
            --color-tarea-claro: #f9f9f9;
            --color-tarea-oscuro: #333;
            --color-borde-claro: #ddd;
            --color-borde-oscuro: #555;
            --color-sombra-claro: rgba(0,0,0,0.1);
            --color-sombra-oscuro: rgba(0,0,0,0.5);
            --color-verde: #4CAF50;
            --color-rojo: #f44336;
        }

        /* Variables para el modo oscuro, aplicadas a la clase 'dark-mode' */
        .dark-mode {
            --color-principal: #ffa500;
            --color-texto-claro: #e0e0e0;
            --color-texto-oscuro: #e0e0e0; /* El texto principal ahora será claro */
            --color-fondo-claro: #121212; /* El fondo principal será oscuro */
            --color-fondo-oscuro: #2a2a2a;
            --color-contenedor-claro: #1f1f1f;
            --color-contenedor-oscuro: #2a2a2a;
            --color-tarea-claro: #222;
            --color-tarea-oscuro: #333;
            --color-borde-claro: #3c3c3c;
            --color-borde-oscuro: #555;
            --color-sombra-claro: rgba(0,0,0,0.9);
            --color-sombra-oscuro: rgba(0,0,0,0.5);
            --color-verde: #45a049;
            --color-rojo: #d32f2f;
        }

        /* Estilos generales usando las variables CSS */
        body {
            background-color: var(--color-fondo-claro);
            font-family: 'Arial', sans-serif;
            color: var(--color-texto-oscuro);
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 50px;
            transition: background-color 0.4s, color 0.4s;
        }
        .container {
            max-width: 800px;
            width: 90%;
            background: var(--color-contenedor-claro);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px var(--color-sombra-claro);
            transition: background 0.4s, box-shadow 0.4s;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--color-borde-claro);
            padding-bottom: 20px;
        }
        h1 {
            color: var(--color-principal);
            font-size: 2.5em;
            margin: 0;
        }
        .header-actions {
            display: flex;
            gap: 15px;
        }
        .header-actions button {
            background-color: var(--color-principal);
            color: var(--color-contenedor-claro);
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .header-actions button:hover {
            background-color: var(--color-principal);
            filter: brightness(1.2);
            transform: translateY(-2px);
        }
        .form-container {
            background: var(--color-fondo-oscuro);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .form-container h2 {
            margin-top: 0;
            color: var(--color-principal);
        }
        #add-task-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        #add-task-form input, #add-task-form textarea, #add-task-form select {
            padding: 15px;
            border: 2px solid var(--color-borde-oscuro);
            border-radius: 8px;
            background-color: var(--color-tarea-oscuro);
            color: var(--color-texto-claro);
            font-size: 1em;
            width: 100%;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }
        #add-task-form input:focus, #add-task-form textarea:focus, #add-task-form select:focus {
            outline: none;
            border-color: var(--color-principal);
        }
        #add-task-form button {
            background-color: var(--color-verde);
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
            background-color: var(--color-verde);
            filter: brightness(0.9);
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
            background: var(--color-tarea-oscuro);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 5px var(--color-sombra-claro);
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
            color: var(--color-texto-claro);
            margin: 0;
        }
        .task-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .task-actions button {
            background: var(--color-borde-oscuro);
            border: none;
            color: var(--color-texto-claro);
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .task-actions button:hover {
            background: var(--color-principal);
            color: var(--color-contenedor-claro);
        }
        .task-actions .delete-btn {
            background-color: var(--color-rojo);
        }
        .task-actions .delete-btn:hover {
            background-color: var(--color-rojo);
            filter: brightness(0.9);
        }
        .task-actions input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: var(--color-principal);
        }
        .no-tasks {
            text-align: center;
            color: #888;
            margin-top: 50px;
        }
        .message.success {
            background-color: var(--color-verde);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .message.error {
            background-color: var(--color-rojo);
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
                <input type="text" name="etiquetas" placeholder="Etiquetas (ej: trabajo, personal)">
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
                                <small>
                                    <strong>Prioridad:</strong> <?php echo htmlspecialchars($tarea['prioridad']); ?> |
                                    <strong>Estado:</strong> <?php echo htmlspecialchars($tarea['estado']); ?>
                                    <?php if (!empty($tarea['etiquetas'])): ?>
                                    | <strong>Etiquetas:</strong> <?php echo htmlspecialchars($tarea['etiquetas']); ?>
                                    <?php endif; ?>
                                </small>
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
    
    <script>
        const body = document.body;

        // Función para aplicar el tema y guardarlo en el almacenamiento local
        function applyTheme(theme) {
            if (theme === 'dark') {
                body.classList.add('dark-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('theme', 'light');
            }
        }

        // 1. Al cargar la página, comprueba si hay un tema guardado en localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            applyTheme(savedTheme);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            // 2. Si no hay tema guardado, usa la preferencia del sistema operativo
            applyTheme('dark');
        } else {
            // 3. Si no hay preferencia del sistema ni tema guardado, usa el tema claro por defecto.
            applyTheme('light');
        }
    </script>
</body>
</html>