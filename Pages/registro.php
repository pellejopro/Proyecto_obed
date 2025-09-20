<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
include '../Config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';

    // Validar que todos los campos estén llenos
    if (empty($nombre) || empty($usuario) || empty($password) || empty($password_confirm) || empty($correo) || empty($telefono)) {
        $error = "Llena todos los campos.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            // Hash de la contraseña antes de guardarla
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Preparar e insertar todos los datos en la base de datos
            $stmt_ins = $conn->prepare("INSERT INTO usuarios (nombre, usuario, password, correo, telefono) VALUES (?, ?, ?, ?, ?)");
            $stmt_ins->bind_param("sssss", $nombre, $usuario, $hashed_password, $correo, $telefono);

            if ($stmt_ins->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Error al registrar: " . $conn->error;
            }
            $stmt_ins->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <style>
        /* Variables para el modo claro (por defecto) */
        :root {
            --color-principal: #f90;
            --color-texto: #333;
            --color-fondo: #f0f2f5;
            --color-contenedor: #ffffff;
            --color-input: #f0f0f0;
            --color-placeholder: #888;
            --color-sombra: rgba(0,0,0,0.1);
        }

        /* Variables para el modo oscuro, aplicadas a la clase 'dark-mode' */
        .dark-mode {
            --color-principal: #ffa500;
            --color-texto: #e0e0e0;
            --color-fondo: #121212;
            --color-contenedor: #1f1f1f;
            --color-input: #222;
            --color-placeholder: #ccc;
            --color-sombra: rgba(0,0,0,0.9);
        }

        /* Estilos generales que usan las variables CSS */
        body {
            background-color: var(--color-fondo);
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--color-texto);
            transition: background-color 0.4s, color 0.4s;
        }
        .container {
            background: var(--color-contenedor);
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px var(--color-sombra);
            width: 340px;
            text-align: center;
            transition: background 0.4s, box-shadow 0.4s;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        h2 span {
            background: var(--color-principal);
            color: var(--color-contenedor);
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            transition: background 0.4s, color 0.4s;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: none;
            border-radius: 6px;
            background: var(--color-input);
            color: var(--color-texto);
            font-size: 16px;
            transition: background 0.4s, color 0.4s;
        }
        input::placeholder {
            color: var(--color-placeholder);
        }
        button {
            background: var(--color-principal);
            color: var(--color-contenedor);
            padding: 14px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #ffa733;
        }
        .error {
            color: #ff6b6b;
            margin-bottom: 15px;
            font-weight: bold;
        }
        p, a {
            color: var(--color-texto);
            transition: color 0.4s;
        }
        a {
            font-weight: bold;
            text-decoration: none;
            color: var(--color-principal);
            transition: color 0.4s;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="container">
    <h2><span>Registro</span></h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="email" name="correo" placeholder="Correo" required>
        <input type="tel" name="telefono" placeholder="Teléfono" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="password" name="password_confirm" placeholder="Confirmar Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
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

    // Al cargar la página, comprueba si hay un tema guardado
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // Si no hay tema guardado, usa la preferencia del sistema
        applyTheme('dark');
    } else {
        // Por defecto, usa el tema claro si no hay preferencias
        applyTheme('light');
    }
</script>
</body>
</html>