<?php
session_start();
include 'conexion.php';
//comentario vacio
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($usuario) || empty($password) || empty($password_confirm)) {
        $error = "Llena todos los campos.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "El usuario ya existe.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_ins = $conn->prepare("INSERT INTO usuarios (usuario, password) VALUES (?, ?)");
            $stmt_ins->bind_param("ss", $usuario, $hashed_password);

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
        .container {
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
            margin: 12px 0;
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
<div class="container">
    <h2><span>Registro</span></h2>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="POST" action="">
        <input type="text" name="usuario" placeholder="Usuario" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <input type="password" name="password_confirm" placeholder="Confirmar Contraseña" required>
        <button type="submit">Registrarse</button>
    </form>
    <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a></p>
</div>
</body>
</html>
