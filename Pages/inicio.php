<?php
session_start();

// Redirigir al login si no hay sesión iniciada
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Página de inicio</title>
    <style>
        body {
            background-color: #000;
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
            text-align: center;
        }
        .container {
            background: #111;
            padding: 50px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.9);
            width: 500px;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        h2 span {
            background: #f90;
            color: #000;
            padding: 4px 10px;
            border-radius: 4px;
            font-weight: bold;
        }
        p {
            font-size: 18px;
            margin: 20px 0;
        }
        a {
            font-weight: bold;
            text-decoration: none;
            color: #f90;
        }
        a:hover {
            text-decoration: underline;
        }
        button {
            background: #f90;
            color: #000;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
            margin-top: 20px;
        }
        button:hover {
            background: #ffa733;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>¡Bienvenido, <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>!</h2>
    <p>Has iniciado sesión correctamente.</p>
    <p>Esta es tu página de inicio. Aquí puedes ver tu información, tareas pendientes, etc.</p>
    <a href="perfil.php"><button>Ir a mi Perfil</button></a>
    <p>¿No eres tú? <a href="login.php">Cerrar sesión</a></p>
</div>
</body>
</html>