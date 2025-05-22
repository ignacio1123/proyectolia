<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 24px 28px;
            border-radius: 10px;
            box-shadow: 0 0 16px rgba(0,0,0,0.10);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 18px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            color: #263238;
            font-weight: 500;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            background: #f9f9f9;
            color: #263238;
            font-size: 1em;
        }
        input:focus, select:focus {
            outline: 2px solid #4CAF50;
            background: #fff;
        }
        .btn-registrar {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            font-weight: 600;
            cursor: pointer;
            margin-top: 18px;
            padding: 10px 0;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .btn-registrar:hover {
            background-color: #388E3C;
        }
        .btn-volver {
            background-color: #4CAF50;
            color: #263238;
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 4px;
            transition: background 0.2s;
            display: inline-block;
            margin-bottom: 18px;
            text-decoration: none;
        }
        .btn-volver:hover {
            background-color: #388E3C;
        }
        .message {
            margin-top: 10px;
            text-align: center;
            color: #F44336;
            font-weight: 500;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
</head>
<body>
    <div class="container">
        <div class="w-full text-center mb-2">
            <a href="../views/pantallaPrincipal.php" class="btn-volver">
                Volver atrás
            </a>
        </div>
        <h2>Registro de Usuarios</h2>
        <form action="" method="post">
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" required>

            <label for="apellido">Apellido:</label>
            <input type="text" name="apellido" id="apellido" required>

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>

            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="director">Director</option>
                <option value="estudiante">Estudiante</option>
            </select>

            <label for="rut">RUT:</label>
            <input type="text" name="rut" id="rut" required>

            <button type="submit" class="btn-registrar">
                Registrar
            </button>
        </form>

        <?php
        // Variable para almacenar mensajes
        $message = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Configuración de la conexión a la base de datos
            $host = 'localhost';
            $dbname = 'proyectolab';
            $username = 'root';
            $password = ''; 

            try {
                // Crear la conexión con la base de datos
                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Capturar los datos del formulario con verificación de claves
                $nombre = $_POST['nombre'] ?? null;
                $apellido = $_POST['apellido'] ?? null;
                $email = $_POST['email'] ?? null;
                $password_plain = $_POST['password'] ?? null;
                $rol = $_POST['rol'] ?? null;
                $rut = $_POST['rut'] ?? null;

                // Verificar si todos los datos requeridos están presentes
                if ($nombre && $apellido && $email && $password_plain && $rol && $rut) {
                    // Verificar si el RUT ya existe
                    $checkRUTQuery = "SELECT * FROM usuarios WHERE rut = :rut";
                    $checkRUTStmt = $conn->prepare($checkRUTQuery);
                    $checkRUTStmt->execute([':rut' => $rut]);

                    if ($checkRUTStmt->rowCount() > 0) {
                        $message = "Error: El RUT $rut ya está registrado.";
                    } else {
                        // Hash de la contraseña
                        $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);

                        // Fecha de creación
                        $fecha_creacion = date("Y-m-d H:i:s");

                        // Estado inicial del usuario (pendiente)
                        $estado = 'pendiente';

                        // Preparar la consulta de inserción
                        $insertQuery = "INSERT INTO usuarios (nombre, apellido, email, password, rol, rut, fecha_creacion, estado) VALUES (:nombre, :apellido, :email, :password, :rol, :rut, :fecha_creacion, :estado)";
                        $insertStmt = $conn->prepare($insertQuery);
                        $insertStmt->execute([
                            ':nombre' => $nombre,
                            ':apellido' => $apellido,
                            ':email' => $email,
                            ':password' => $password_hashed,
                            ':rol' => $rol,
                            ':rut' => $rut,
                            ':fecha_creacion' => $fecha_creacion,
                            ':estado' => $estado
                        ]);

                        $message = "Registro exitoso. Tu cuenta está pendiente de aprobación por el administrador.";
                    }
                } else {
                    $message = "Error: Todos los campos son obligatorios.";
                }
            } catch (PDOException $e) {
                $message = "Error en la conexión: " . $e->getMessage();
            }
        }
        ?>

        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    </div>
    <?php if (!empty($message)): ?>
    <script>
        window.onload = function() {
            Swal.fire({
                icon: "<?php echo (strpos($message, 'exitoso') !== false) ? 'success' : 'error'; ?>",
                title: "<?php echo (strpos($message, 'exitoso') !== false) ? '¡Registro exitoso!' : '¡Atención!'; ?>",
                text: "<?php echo str_replace(array("\r", "\n"), '', htmlspecialchars($message)); ?>",
                confirmButtonColor: '#facc15'
            });
        }
    </script>
    <?php endif; ?>
</body>
</html>
