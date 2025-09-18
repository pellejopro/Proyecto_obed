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

// Obtener todas las tareas del usuario para mostrarlas, ordenadas por fecha de vencimiento
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
        /* Estilos generales y tema oscuro */
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
        
        /* Navbar */
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
        
        /* Mensajes flash */
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
        }
        .flash-success {
            background-color: #4CAF50;
            color: white;
        }
        .flash-error {
            background-color: #f44336;
            color: white;
        }
        
        /* Notificaciones */
        .notifications-container {
            position: relative;
            display: inline-block;
        }
        #notification-bell {
            background-color: transparent;
            border: none;
            font-size: 1.5em;
            cursor: pointer;
            color: #f90;
            padding: 10px;
            position: relative;
        }
        #notification-count {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: #f44336;
            color: white;
            font-size: 0.7em;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50%;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: #3a3a3a;
            min-width: 250px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1000;
            border-radius: 8px;
            padding: 10px;
            margin-top: 5px;
        }
        .dropdown-content a {
            color: #e0e0e0;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }
        .dropdown-content a:hover {
            background-color: #555;
        }
        .dropdown-content.show {
            display: block;
        }

        /* Saludo y secciones */
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

        /* Botón de exportar PDF */
        .export-pdf-container {
            text-align: right;
            margin-bottom: 20px;
        }
        .export-pdf-container button {
            padding: 12px 25px;
            background-color: #3e8e41; /* Verde más oscuro */
            border: none;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .export-pdf-container button:hover {
            background-color: #367c39;
        }

        /* Filtros */
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

        /* Lista de tareas */
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
        .task-tags {
            font-size: 0.8em;
            color: #f90;
            margin-top: 5px;
        }
        .no-tasks {
            text-align: center;
            color: #888;
            margin-top: 50px;
        }

        /* Acciones de la tarea */
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
        .task-actions .edit-btn { background-color: #4CAF50; }
        .task-actions .edit-btn:hover { background-color: #45a049; }
        .task-actions .delete-btn { background-color: #f44336; }
        .task-actions .delete-btn:hover { background-color: #d32f2f; }
        .task-actions input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #f90;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
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

    <?php if (isset($_SESSION['message'])): ?>
        <div class="flash-message <?php echo (isset($_SESSION['message_type']) && $_SESSION['message_type'] === 'error') ? 'flash-error' : 'flash-success'; ?>">
            <?php 
echo htmlspecialchars($_SESSION['message']); 
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>
        </div>
    <?php endif; ?>

    <div class="navbar">
        <div class="navbar-brand">Gestor de Tareas</div>
        <div class="navbar-menu">
            <div class="notifications-container">
                <button id="notification-bell">
                    🔔 <span id="notification-count">0</span>
                </button>
                <div id="notification-dropdown" class="dropdown-content">
                                        </div>
            </div>
            <a href="inicio.php">Perfil</a>
            <a href="tareas.php">Crear Tarea</a>
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <div class="user-greeting">¡Hola, <?php echo htmlspecialchars($nombre_usuario); ?>! 👋</div>

    <div class="task-management-section">
        <h2>Mis Tareas</h2>

        <div class="export-pdf-container">
            <form action="exportar_pdf.php" method="post" target="_blank">
                <button type="submit">
                    📄 Exportar a PDF
                </button>
            </form>
        </div>

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
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
            <input type="text" id="filter-tags" placeholder="Etiquetas (separadas por comas)">
        </div>

        <div class="task-list-container">
            <ul id="task-list">
                <?php if (count($tareas) > 0): ?>
                    <?php foreach ($tareas as $tarea): ?>
                        <li class="task-item <?php echo ($tarea['estado'] === 'completada') ? 'completed' : ''; ?>" 
                            data-id="<?php echo $tarea['id']; ?>"
                            data-titulo="<?php echo strtolower(htmlspecialchars($tarea['titulo'])); ?>"
                            data-descripcion="<?php echo strtolower(htmlspecialchars($tarea['descripcion'])); ?>"
                            data-estado="<?php echo htmlspecialchars($tarea['estado']); ?>"
                            data-prioridad="<?php echo htmlspecialchars($tarea['prioridad']); ?>"
                            data-etiquetas="<?php echo strtolower(htmlspecialchars($tarea['etiquetas'])); ?>">
                            <div class="task-details">
                                <div class="task-title"><?php echo htmlspecialchars($tarea['titulo']); ?></div>
                                <p class="task-description"><?php echo htmlspecialchars($tarea['descripcion']); ?></p>
                                <p>Vence: <?php echo htmlspecialchars($tarea['fecha_vencimiento']); ?></p>
                                <p>Prioridad: <?php echo htmlspecialchars($tarea['prioridad']); ?></p>
                                <p class="task-tags">Etiquetas: <?php echo htmlspecialchars($tarea['etiquetas']); ?></p>
                            </div>
                            <div class="task-actions">
                                <input type="checkbox" class="complete-checkbox" data-id="<?php echo $tarea['id']; ?>" <?php echo ($tarea['estado'] === 'completada') ? 'checked' : ''; ?>>
                                <button class="edit-btn" data-id="<?php echo $tarea['id']; ?>">Editar</button>
                                <button class="delete-btn" data-id="<?php echo $tarea['id']; ?>">Eliminar</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-tasks">No tienes tareas registradas. ¡Hora de crear una! 🚀</p>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closeModal">&times;</span>
        <h2>Editar Tarea</h2>
        <form id="editForm">
            <input type="hidden" id="edit-id" name="id">
            <input type="text" id="edit-title" name="titulo" placeholder="Título" required>
            <textarea id="edit-description" name="descripcion" placeholder="Descripción" required></textarea>
            <input type="date" id="edit-date" name="fecha_vencimiento" required>
            <select id="edit-priority" name="prioridad" required>
                <option value="alta">Alta</option>
                <option value="media">Media</option>
                <option value="baja">Baja</option>
            </select>
            <input type="text" id="edit-tags" name="etiquetas" placeholder="Etiquetas (separadas por comas)">
            <button type="submit">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('search-input');
        const filterStatus = document.getElementById('filter-status');
        const filterPriority = document.getElementById('filter-priority');
        const filterTagsInput = document.getElementById('filter-tags');
        const taskList = document.getElementById('task-list');
        const allTasks = Array.from(taskList.getElementsByClassName('task-item'));
        const editModal = document.getElementById('editModal');
        const closeModalBtn = document.getElementById('closeModal');
        const editForm = document.getElementById('editForm');

        // NOTIFICACIONES
        const bell = document.getElementById('notification-bell');
        const count = document.getElementById('notification-count');
        const dropdown = document.getElementById('notification-dropdown');

        function fetchNotifications() {
            fetch('obtener_notificaciones.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const notifications = data.notificaciones;
                        if (notifications.length > 0) {
                            count.textContent = notifications.length;
                            count.style.display = 'block';
                            dropdown.innerHTML = '';
                            notifications.forEach(tarea => {
                                const notificationItem = document.createElement('a');
                                notificationItem.href = '#'; 
                                notificationItem.textContent = `${tarea.titulo} (Vence el ${tarea.fecha_entrega})`;
                                notificationItem.style.cssText = 'color: #e0e0e0; padding: 12px 16px; text-decoration: none; display: block;';
                                notificationItem.onmouseover = () => notificationItem.style.backgroundColor = '#555';
                                notificationItem.onmouseout = () => notificationItem.style.backgroundColor = 'transparent';
                                dropdown.appendChild(notificationItem);
                            });
                        } else {
                            count.style.display = 'none';
                            dropdown.innerHTML = '<p style="text-align: center; margin: 10px 0; color: #ccc;">No hay notificaciones</p>';
                        }
                    } else {
                        console.error("Error al obtener notificaciones:", data.error);
                        count.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error de conexión:', error);
                });
        }
        fetchNotifications();
        bell.addEventListener('click', (event) => {
            event.stopPropagation();
            dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        });
        window.addEventListener('click', (event) => {
            if (!dropdown.contains(event.target) && event.target !== bell) {
                dropdown.style.display = 'none';
            }
        });

        // LÓGICA EXISTENTE PARA FILTROS Y ACCIONES DE TAREAS
        function filterTasks() {
            const searchTerm = searchInput.value.toLowerCase();
            const status = filterStatus.value;
            const priority = filterPriority.value;
            const filterTags = filterTagsInput.value.toLowerCase().split(',').map(tag => tag.trim()).filter(tag => tag.length > 0);

            allTasks.forEach(task => {
                const taskTitle = task.getAttribute('data-titulo');
                const taskDesc = task.getAttribute('data-descripcion');
                const taskStatus = task.getAttribute('data-estado');
                const taskPriority = task.getAttribute('data-prioridad');
                const taskTags = task.getAttribute('data-etiquetas').toLowerCase().split(',').map(tag => tag.trim());

                const matchesSearch = taskTitle.includes(searchTerm) || taskDesc.includes(searchTerm);
                const matchesStatus = status === 'all' || taskStatus === status;
                const matchesPriority = priority === 'all' || taskPriority === priority;
                const matchesTags = filterTags.length === 0 || filterTags.some(tag => taskTags.includes(tag));

                if (matchesSearch && matchesStatus && matchesPriority && matchesTags) {
                    task.style.display = 'flex';
                } else {
                    task.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('keyup', filterTasks);
        filterStatus.addEventListener('change', filterTasks);
        filterPriority.addEventListener('change', filterTasks);
        filterTagsInput.addEventListener('keyup', filterTasks);

        taskList.addEventListener('click', async (event) => {
            const target = event.target;
            const taskItem = target.closest('.task-item');
            if (!taskItem) return;

            const taskId = taskItem.getAttribute('data-id');

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
            
            if (target.classList.contains('edit-btn')) {
                const taskData = {
                    id: taskItem.getAttribute('data-id'),
                    titulo: taskItem.getAttribute('data-titulo'),
                    descripcion: taskItem.getAttribute('data-descripcion'),
                    fecha_vencimiento: taskItem.querySelector('p:nth-of-type(1)').textContent.replace('Vence: ', '').trim(),
                    prioridad: taskItem.getAttribute('data-prioridad'),
                    etiquetas: taskItem.getAttribute('data-etiquetas')
                };

                document.getElementById('edit-id').value = taskData.id;
                document.getElementById('edit-title').value = taskData.titulo;
                document.getElementById('edit-description').value = taskData.descripcion;
                document.getElementById('edit-date').value = taskData.fecha_vencimiento;
                document.getElementById('edit-priority').value = taskData.prioridad;
                document.getElementById('edit-tags').value = taskData.etiquetas;
                
                editModal.style.display = 'flex';
            }

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
                        target.checked = !target.checked;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Ocurrió un error al actualizar el estado de la tarea.');
                    target.checked = !target.checked;
                }
            }
        });

        closeModalBtn.addEventListener('click', () => {
            editModal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target == editModal) {
                editModal.style.display = 'none';
            }
        });

        editForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const taskId = document.getElementById('edit-id').value;
            const titulo = document.getElementById('edit-title').value;
            const descripcion = document.getElementById('edit-description').value;
            const fechaVencimiento = document.getElementById('edit-date').value;
            const prioridad = document.getElementById('edit-priority').value;
            const etiquetas = document.getElementById('edit-tags').value;

            try {
                const response = await fetch('update_tarea.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        id: taskId, 
                        titulo: titulo, 
                        descripcion: descripcion, 
                        fecha_vencimiento: fechaVencimiento, 
                        prioridad: prioridad, 
                        etiquetas: etiquetas 
                    })
                });
                const data = await response.json();
                if (data.success) {
                    const taskItem = document.querySelector(`.task-item[data-id="${taskId}"]`);
                    taskItem.querySelector('.task-title').textContent = titulo;
                    taskItem.querySelector('.task-description').textContent = descripcion;
                    taskItem.querySelector('p:nth-of-type(1)').textContent = `Vence: ${fechaVencimiento}`;
                    taskItem.querySelector('p:nth-of-type(2)').textContent = `Prioridad: ${prioridad}`;
                    taskItem.querySelector('.task-tags').textContent = `Etiquetas: ${etiquetas}`;
                    
                    taskItem.setAttribute('data-titulo', titulo.toLowerCase());
                    taskItem.setAttribute('data-descripcion', descripcion.toLowerCase());
                    taskItem.setAttribute('data-prioridad', prioridad);
                    taskItem.setAttribute('data-etiquetas', etiquetas.toLowerCase());
                    
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