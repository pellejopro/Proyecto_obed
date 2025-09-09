<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
include '../Config/conexion.php';

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = "Por favor llena todos los campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $hash_password);
            $stmt->fetch();

            if (password_verify($password, $hash_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['usuario'] = $usuario;

                header("Location: inicio.php");
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
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
        }
        .login-container {
            background: #111;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.9);
            width: 340px;
            text-align: center;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        h2 span {
            background: #f90;
            color: #000;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            background: #222;
            color: #fff;
            font-size: 16px;
        }
        input::placeholder {
            color: #888;
        }
        button {
            background: #f90;
            color: #000;
            padding: 14px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
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
            color: #ccc;
        }
        a {
            font-weight: bold;
            text-decoration: none;
            color: #f90;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2><span>Login</span></h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Iniciar sesión</button>
    </form>
    <p>¿No tienes cuenta? <a href="registro.php">Regístrate aquí</a></p>
</div>
</body>
</html>