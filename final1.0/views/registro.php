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

</head>
<body>
    <div class="container">
    <a href="login.php" class="arrow">&larr;</a>
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
            
            <button type="submit">Registrar</button>
        </form>

        <?php
        // Variable para almacenar mensajes
        $message = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Configuración de la conexión a la base de datos
            $host = 'localhost';
            $dbname = 'proyectolab';
            $username = 'root';
            $password = ''; // Por defecto, la contraseña es vacía en XAMPP

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

                        // Estado inicial del usuario (en revisión)
                        $estado = 'en revision';

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

                        $message = "Registro exitoso. Tu cuenta está en revisión. Por favor, espera la aprobación del administrador.";
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
</body>
</html>

<?php
$sql = "SELECT id_usuario, nombre, apellido, email, rol, rut, estado FROM usuarios WHERE estado = 'en revision'";
$result = $conn->query($sql);

$sql = "SELECT * FROM usuarios WHERE email = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $email, $password);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    if ($user['estado'] === 'en revision') {
        echo "Tu cuenta está en revisión. Por favor, espera la aprobación del administrador.";
        exit;
    } elseif ($user['estado'] === 'rechazado') {
        echo "Tu cuenta ha sido rechazada. Contacta al administrador.";
        exit;
    } else {
        // Continuar con el inicio de sesión
        $_SESSION['usuario'] = $user;
        header("Location: dashboard.php");
        exit;
    }
} else {
    echo "Credenciales incorrectas.";
}
