<?php
session_start();
include '../Config/conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['id'];

// Obtener todas las tareas del usuario actual
$sql = "SELECT * FROM tareas WHERE usuario_id = ? ORDER BY orden ASC";
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
        #add-task-form {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        #add-task-form input[type="text"] {
            flex-grow: 1;
            padding: 15px;
            border: 2px solid #555;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: #e0e0e0;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }
        #add-task-form input[type="text"]:focus {
            outline: none;
            border-color: #f90;
        }
        #add-task-form button {
            background-color: #4CAF50; /* Green */
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        #add-task-form button:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
        .filters input[type="text"], .filters select {
            padding: 12px;
            border: 2px solid #555;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: #e0e0e0;
            font-size: 1em;
            flex-grow: 1;
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
            cursor: grab;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .task-item:hover {
            background-color: #444;
            transform: translateY(-3px);
        }
        .task-item.completed {
            background-color: #2b2b2b;
            text-decoration: line-through;
            opacity: 0.7;
            border-left: 5px solid #4CAF50;
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
            background-color: #f44336; /* Red */
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Gestor de Tareas</h1>
            <div class="header-actions">
                <a href="login.php"><button>Cerrar Sesión</button></a>
            </div>
        </header>

        <form id="add-task-form">
            <input type="text" id="task-title" placeholder="Añadir una nueva tarea..." required>
            <button type="submit">Agregar</button>
        </form>

        <div class="filters">
            <input type="text" id="search-input" placeholder="Buscar tareas...">
            <select id="filter-status">
                <option value="all">Todos</option>
                <option value="pendiente">Pendientes</option>
                <option value="completada">Completadas</option>
            </select>
            <select id="filter-priority">
                <option value="all">Prioridad</option>
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
        </div>

        <ul id="task-list">
            <?php foreach ($tareas as $tarea): ?>
                <li class="task-item" data-id="<?php echo $tarea['id']; ?>" data-status="<?php echo $tarea['estado']; ?>">
                    <div class="task-details">
                        <span class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></span>
                        <p class="task-description"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                    </div>
                    <div class="task-actions">
                        <button class="edit-btn">Editar</button>
                        <button class="delete-btn">Eliminar</button>
                        <input type="checkbox" class="complete-checkbox" <?php echo ($tarea['estado'] == 'completada') ? 'checked' : ''; ?>>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <script>
        // Aquí irá tu código JavaScript para el CRUD, arrastrar y soltar, etc.
    </script>
</body>
</html>