<?php
// Inicia la sesión de PHP
session_start();

// Redirige al login si no hay sesión iniciada
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Incluye la conexión a la base de datos
include '../Config/conexion.php';

// Obtener el ID y nombre del usuario de la sesión
$usuario_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario_data = $result->fetch_assoc();
$nombre_usuario = $usuario_data['nombre'];
$stmt->close();

// Obtener todas las tareas del usuario para mostrarlas
$sql = "SELECT * FROM tareas WHERE usuario_id = ? ORDER BY fecha_vencimiento ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result_tareas = $stmt->get_result();

$tareas = [];
if ($result_tareas->num_rows > 0) {
    while ($row = $result_tareas->fetch_assoc()) {
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
    <title>Perfil de Usuario - Gestor de Tareas</title>
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
            max-width: 900px;
            width: 90%;
            background: #2a2a2a;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.5);
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 2px solid #3c3c3c;
            margin-bottom: 30px;
        }
        .navbar-brand {
            font-size: 2em;
            color: #f90;
            font-weight: bold;
        }
        .navbar-menu {
            display: flex;
            gap: 15px;
        }
        .navbar-menu a {
            text-decoration: none;
            color: #e0e0e0;
            background-color: #3a3a3a;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .navbar-menu a:hover {
            background-color: #f90;
            color: #1a1a1a;
        }
        
        .user-greeting {
            font-size: 1.5em;
            text-align: center;
            margin-bottom: 30px;
            color: #f90;
        }
        
        .task-management-section {
            padding: 30px;
            background: #222;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }
        .task-management-section h2 {
            font-size: 2em;
            color: #e0e0e0;
            margin-bottom: 20px;
            text-align: center;
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
        .task-item.completed {
            background-color: #2b3a2c;
            opacity: 0.7;
            text-decoration: line-through;
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
        .no-tasks {
            text-align: center;
            color: #888;
            margin-top: 50px;
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
        .task-actions .edit-btn {
            background-color: #3e8e41;
        }
        .task-actions .edit-btn:hover {
            background-color: #367c39;
        }
        .task-actions input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #f90;
        }
        /* Estilos para el formulario de edición (modal) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
            animation: fadeIn 0.3s;
        }
        .modal-content h2 {
            margin-top: 0;
            color: #f90;
        }
        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .modal-content input, .modal-content textarea, .modal-content select {
            padding: 12px;
            border: 2px solid #555;
            border-radius: 8px;
            background-color: #3a3a3a;
            color: #e0e0e0;
            font-size: 1em;
            width: 100%;
            box-sizing: border-box;
        }
        .modal-content button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .modal-content button:hover {
            background-color: #45a049;
        }
        .close-btn {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-btn:hover,
        .close-btn:focus {
            color: white;
            text-decoration: none;
            cursor: pointer;
        }
        @keyframes fadeIn {
            from {opacity: 0;}
            to {opacity: 1;}
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="navbar">
            <div class="navbar-brand">Gestor de Tareas</div>
            <div class="navbar-menu">
                <a href="perfil.php">Perfil</a>
                <a href="tareas.php">Crear Tarea</a>
                <a href="login.php">Cerrar Sesión</a>
            </div>
        </div>

        <h1 class="user-greeting">Hola, <?php echo htmlspecialchars($nombre_usuario); ?></h1>

        <div class="task-management-section">
            <h2>Tus Tareas</h2>
            <div class="filters">
                <input type="text" id="search-input" placeholder="Buscar por título o descripción...">
                <select id="filter-status">
                    <option value="all">Estado: Todos</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="completada">Completada</option>
                </select>
                <select id="filter-priority">
                    <option value="all">Prioridad: Todas</option>
                    <option value="alta">Alta</option>
                    <option value="media">Prioridad: Media</option>
                    <option value="baja">Prioridad: Baja</option>
                </select>
            </div>

            <div class="task-list-container">
                <ul id="task-list">
                    <?php if (empty($tareas)): ?>
                        <p class="no-tasks">No tienes tareas. ¡Hora de crear una! 🚀</p>
                    <?php else: ?>
                        <?php foreach ($tareas as $tarea): ?>
                            <li class="task-item <?php echo ($tarea['estado'] == 'completada') ? 'completed' : ''; ?>" data-id="<?php echo $tarea['id']; ?>" data-estado="<?php echo $tarea['estado']; ?>" data-prioridad="<?php echo $tarea['prioridad']; ?>" data-titulo="<?php echo htmlspecialchars($tarea['titulo']); ?>" data-descripcion="<?php echo htmlspecialchars($tarea['descripcion']); ?>">
                                <div class="task-details">
                                    <span class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></span>
                                    <p class="task-description"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                    <small><strong>Estado:</strong> <?php echo htmlspecialchars($tarea['estado']); ?> | <strong>Prioridad:</strong> <?php echo htmlspecialchars($tarea['prioridad']); ?></small>
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
    </div>
    
    <div id="editModal" class="modal">
      <div class="modal-content">
        <span class="close-btn">&times;</span>
        <h2>Editar Tarea</h2>
        <form id="edit-task-form">
          <input type="hidden" id="edit-task-id">
          <input type="text" id="edit-titulo" placeholder="Título de la tarea" required>
          <textarea id="edit-descripcion" placeholder="Descripción de la tarea (opcional)"></textarea>
          <select id="edit-prioridad" required>
            <option value="baja">Prioridad: Baja</option>
            <option value="media">Prioridad: Media</option>
            <option value="alta">Prioridad: Alta</option>
          </select>
          <button type="submit">Guardar Cambios</button>
        </form>
      </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('search-input');
            const filterStatus = document.getElementById('filter-status');
            const filterPriority = document.getElementById('filter-priority');
            const taskList = document.getElementById('task-list');
            const allTasks = Array.from(taskList.getElementsByClassName('task-item'));
            const editModal = document.getElementById('editModal');
            const closeBtn = document.querySelector('.close-btn');
            const editForm = document.getElementById('edit-task-form');

            function filterTasks() {
                const searchTerm = searchInput.value.toLowerCase();
                const status = filterStatus.value;
                const priority = filterPriority.value;

                allTasks.forEach(task => {
                    const taskTitle = task.getAttribute('data-titulo').toLowerCase();
                    const taskDesc = task.getAttribute('data-descripcion').toLowerCase();
                    const taskStatus = task.getAttribute('data-estado');
                    const taskPriority = task.getAttribute('data-prioridad');

                    const matchesSearch = taskTitle.includes(searchTerm) || taskDesc.includes(searchTerm);
                    const matchesStatus = status === 'all' || taskStatus === status;
                    const matchesPriority = priority === 'all' || taskPriority === priority;

                    if (matchesSearch && matchesStatus && matchesPriority) {
                        task.style.display = 'flex';
                    } else {
                        task.style.display = 'none';
                    }
                });
            }

            searchInput.addEventListener('keyup', filterTasks);
            filterStatus.addEventListener('change', filterTasks);
            filterPriority.addEventListener('change', filterTasks);
            
            // Lógica para los botones de acción
            taskList.addEventListener('click', async (event) => {
                const target = event.target;
                const taskItem = target.closest('.task-item');
                if (!taskItem) return;

                const taskId = taskItem.getAttribute('data-id');

                // Lógica para el botón de ELIMINAR
                if (target.classList.contains('delete-btn')) {
                    if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
                        try {
                            const response = await fetch('delete_tarea.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ id: taskId })
                            });
                            const data = await response.json();
                            if (data.success) {
                                taskItem.remove();
                                // Actualizar la lista de tareas en el array allTasks
                                const index = allTasks.findIndex(task => task.getAttribute('data-id') === taskId);
                                if (index > -1) {
                                    allTasks.splice(index, 1);
                                }
                                alert('Tarea eliminada correctamente.');
                            } else {
                                alert('Error al eliminar la tarea: ' + data.error);
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Ocurrió un error al intentar eliminar la tarea.');
                        }
                    }
                }
                
                // Lógica para el botón de EDITAR
                if (target.classList.contains('edit-btn')) {
                    document.getElementById('edit-task-id').value = taskId;
                    document.getElementById('edit-titulo').value = taskItem.getAttribute('data-titulo');
                    document.getElementById('edit-descripcion').value = taskItem.getAttribute('data-descripcion');
                    document.getElementById('edit-prioridad').value = taskItem.getAttribute('data-prioridad');
                    editModal.style.display = 'flex';
                }

                // Lógica para la casilla de COMPLETADO
                if (target.classList.contains('complete-checkbox')) {
                    const estado = target.checked ? 'completada' : 'pendiente';
                    try {
                        const response = await fetch('complete_tarea.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ id: taskId, estado: estado })
                        });
                        const data = await response.json();
                        if (data.success) {
                            if (estado === 'completada') {
                                taskItem.classList.add('completed');
                                taskItem.setAttribute('data-estado', 'completada');
                            } else {
                                taskItem.classList.remove('completed');
                                taskItem.setAttribute('data-estado', 'pendiente');
                            }
                        } else {
                            alert('Error al actualizar el estado: ' + data.error);
                            target.checked = !target.checked; // Revertir el estado del checkbox
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Ocurrió un error al actualizar el estado de la tarea.');
                        target.checked = !target.checked;
                    }
                }
            });

            // Lógica para el formulario de edición (modal)
            closeBtn.addEventListener('click', () => {
                editModal.style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target == editModal) {
                    editModal.style.display = 'none';
                }
            });

            editForm.addEventListener('submit', async (event) => {
                event.preventDefault();

                const taskId = document.getElementById('edit-task-id').value;
                const titulo = document.getElementById('edit-titulo').value;
                const descripcion = document.getElementById('edit-descripcion').value;
                const prioridad = document.getElementById('edit-prioridad').value;

                try {
                    const response = await fetch('update_tarea.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: taskId, titulo: titulo, descripcion: descripcion, prioridad: prioridad })
                    });
                    const data = await response.json();
                    if (data.success) {
                        const taskItem = document.querySelector(`.task-item[data-id="${taskId}"]`);
                        taskItem.querySelector('.task-title').textContent = titulo;
                        taskItem.querySelector('.task-description').textContent = descripcion;
                        taskItem.querySelector('small').innerHTML = `<strong>Estado:</strong> ${taskItem.getAttribute('data-estado')} | <strong>Prioridad:</strong> ${prioridad}`;
                        taskItem.setAttribute('data-titulo', titulo);
                        taskItem.setAttribute('data-descripcion', descripcion);
                        taskItem.setAttribute('data-prioridad', prioridad);
                        
                        editModal.style.display = 'none';
                        alert('Tarea actualizada correctamente.');
                    } else {
                        alert('Error al actualizar la tarea: ' + data.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error al intentar actualizar la tarea.');
                }
            });
        });
    </script>
</body>
</html>