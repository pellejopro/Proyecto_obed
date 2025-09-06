<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL);

session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($usuario) || empty($password)) {
        $error = "Por favor llena todos los campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, password, tipo FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s",$usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id,$hash_password,$tipo);
            $stmt->fetch();

            if (password_verify($password,$hash_password)) {
                $_SESSION['id'] = $id;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['tipo'] = $tipo;

                header($tipo==='admin'
                    ? "Location: admin_dashboard.php"
                    : "Location: productos.php");
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
            background-color: #009C3B;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: #fff;
        }
        .login-container {
            background: #002776;
            padding: 30px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.7);
            width: 340px;
            text-align: center;
        }
        h2 {
            color: #FFDF00;
            margin-bottom: 20px;
            font-weight: bold;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 6px;
            background: #fff;
            color: #002776;
            font-size: 16px;
        }
        input::placeholder {
            color: #666;
        }
        button {
            background: #FFDF00;
            color: #002776;
            padding: 14px;
            width: 100%;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #ffe766;
        }
        .error {
            color: #ffcccc;
            margin-bottom: 15px;
            font-weight: bold;
        }
        p, a {
            color: #fff;
        }
        a {
            font-weight: bold;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2>Iniciar Sesión</h2>
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