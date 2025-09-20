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
        /* Definición de variables CSS para ambos modos */
        :root {
            /* Modo Claro (por defecto) */
            --color-principal: #f90;
            --color-texto: #333;
            --color-fondo: #f0f2f5;
            --color-contenedor: #ffffff;
            --color-sombra: rgba(0,0,0,0.1);
        }

        /* Estilos para el modo oscuro, que se aplican con la clase 'dark-mode' */
        .dark-mode {
            --color-principal: #ffa500;
            --color-texto: #e0e0e0;
            --color-fondo: #121212;
            --color-contenedor: #1f1f1f;
            --color-sombra: rgba(0,0,0,0.9);
        }

        /* Estilos generales que usan las variables */
        body {
            background-color: var(--color-fondo);
            font-family: Arial, Helvetica, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            color: var(--color-texto);
            text-align: center;
            transition: background-color 0.4s, color 0.4s;
        }
        .container {
            background: var(--color-contenedor);
            padding: 50px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px var(--color-sombra);
            width: 500px;
            transition: background 0.4s, box-shadow 0.4s;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 20px;
        }
        h2 span {
            background: var(--color-principal);
            color: var(--color-contenedor);
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
            color: var(--color-principal);
            transition: color 0.4s;
        }
        a:hover {
            text-decoration: underline;
        }
        button { /* Mantengo los estilos del botón si quieres añadir uno más tarde */
            background: var(--color-principal);
            color: var(--color-contenedor);
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.4s ease;
            margin-top: 20px;
        }
        button:hover {
            background: #ffa733; /* Color de hover para el botón */
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

    <script>
        const body = document.body;

        // Función para aplicar el tema
        function applyTheme(theme) {
            if (theme === 'dark') {
                body.classList.add('dark-mode');
            } else {
                body.classList.remove('dark-mode');
            }
        }

        // 1. Al cargar la página, comprueba si hay un tema guardado en localStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            applyTheme(savedTheme);
        } else {
            // 2. Si no hay tema guardado, usa la preferencia del sistema operativo
            const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            applyTheme(prefersDark ? 'dark' : 'light');
            // Opcional: guardar esta preferencia inicial para futuras visitas
            localStorage.setItem('theme', prefersDark ? 'dark' : 'light');
        }

        // Opcional: Escuchar cambios en la preferencia del sistema en tiempo real
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
            const newTheme = event.matches ? 'dark' : 'light';
            applyTheme(newTheme);
            localStorage.setItem('theme', newTheme);
        });
    </script>
</body>
</html>