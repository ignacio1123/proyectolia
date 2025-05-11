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
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
        }
        input, select, button {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: red;
            color: #fff;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 10px;
            text-align: center;
            color: red; 
        }

    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
    <div class="w-full sm:w-auto text-center sm:text-right mt-4 sm:mt-0">
        <a href="/proyectolia/final1.0/index.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
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
            <!--    <option value="administrador">Administrador</option>
                <option value="director">Director</option>!-->
                <option value="estudiante">Estudiante</option>
            </select>
            
            <label for="rut">RUT:</label>
            <input type="text" name="rut" id="rut" required>
            
            <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
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
