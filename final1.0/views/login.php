<?php
session_start();
require 'db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['iniciar_sesion'])) {

    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $contraseña = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            // Verificar el estado del usuario
            if ($user['estado'] === 'Pendiente' || $user['estado'] === 'Inactivo') {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Acceso denegado',
                                text: 'Tu cuenta está en un estado no válido (Pendiente o Inactivo). Por favor, contacta al administrador.',
                                confirmButtonText: 'Aceptar'
                            });
                        });
                      </script>";
            } else if (($user['estado'] === 'Aprobado' || $user['estado'] === 'Activo') && password_verify($contraseña, $user['password'])) {

                $_SESSION['correo'] = $correo;
                $_SESSION['role'] = $user['rol'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['id_usuario'] = $user['id_usuario'];

                switch ($user['rol']) {
                    case 'estudiante':
                        header("Location: views/pantallaEstudiante.php");
                        break;
                    case 'director':
                        header("Location: views/pantallaDirector.php");
                        break;
                    case 'administrador':
                        header("Location: views/pantallaAdmin.php");
                        break;
                    default:
                        $error = "Rol no reconocido.";
                }

                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }

        $stmt->close();
    } else {
        $error = "Error en la preparación de la consulta.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- Importar SweetAlert2 -->
    <link rel="stylesheet" href="../static/css/pantallaestudiante.css">
    <link rel="stylesheet" href="../static/css/form.css">
    <link rel="stylesheet" href="../static/css/base.css">

    <style>
        .containerr {
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg,
                    #121212 25%,
                    #1a1a1a 25%,
                    #1a1a1a 50%,
                    #121212 50%,
                    #121212 75%,
                    #1a1a1a 75%,
                    #1a1a1a);
            background-size: 40px 40px;
            animation: move 4s linear infinite;
        }

        @keyframes move {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 40px 40px;
            }
        }

        .form-container {
            border-radius: 0.1rem;
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding-left: 2em;
            padding-right: 2em;
            padding-bottom: 0.4em;
            background-color: #171717;
            border-radius: 25px;
            transition: .4s ease-in-out;
        }

        #heading {
            text-align: center;
            margin: 2em;
            color: rgb(255, 255, 255);
            font-size: 1.2em;
        }

        .field {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5em;
            border-radius: 25px;
            padding: 0.6em;
            border: none;
            outline: none;
            color: white;
            background-color: #171717;
            box-shadow: inset 2px 5px 10px rgb(5, 5, 5);
        }

        .input-icon {
            height: 1.3em;
            width: 1.3em;
            fill: white;
        }

        .input-field {
            background: none;
            border: none;
            outline: none;
            width: 100%;
            color: #fff; /* Cambia de #d3d3d3 a blanco */
        }

        .input-field::placeholder {
            color: #bdbdbd; /* O el color que prefieras */
            opacity: 1;
        }

        .form .btn {
            display: flex;
            justify-content: center;
            flex-direction: row;
            margin-top: 2.5em;
        }

        .button1 {
            padding: 0.5em;
            padding-left: 1.1em;
            padding-right: 1.1em;
            border-radius: 5px;
            margin-right: 0.5em;
            border: none;
            outline: none;
            transition: .4s ease-in-out;
            background-color: #252525;
            color: white;
        }

        .button1:hover {
            background-color: black;
            color: white;
        }

        .button2 {
            padding: 0.5em;
            padding-left: 2.3em;
            padding-right: 2.3em;
            border-radius: 5px;
            border: none;
            outline: none;
            transition: .4s ease-in-out;
            background-color: #252525;
            color: white;
        }

        .button2:hover {
            background-color: black;
            color: white;
        }

        .button3 {
            margin-bottom: 3em;
            padding: 0.5em;
            border-radius: 5px;
            border: none;
            outline: none;
            transition: .4s ease-in-out;
            background-color: #252525;
            color: white;
        }

        .button3:hover {
            background-color: #4a7dff;
            color: white;
        }
    </style>
</head>

<body class="flex justify-center items-center min-h-screen bg-gray-900" id="asd">
    <div class="form-container p-8 w-96">
        <?php if (!empty($error)): ?>
            <div class="bg-red-500 text-white p-3 mb-4 rounded text-center">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="w-full text-center">
                <img src="public/img/asd.jpg" alt="Logo" class="w-[6rem] mx-auto">
                <h2 class="text-center text-2xl font-semibold text-white mt-6">Inicia Sesión</h2>
            </div>

            <div class="field">
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2zm13 2.383-4.708 2.825L15 12.118V5.383zm-.034 7.434-5.482-3.29-5.482 3.29A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.183zM1 12.118l4.708-2.91L1 5.383v6.735z"/>
                </svg>
                <input autocomplete="off" type="email" name="correo" id="correo" placeholder="Ingrese su correo" required class="input-field" value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>">
            </div>

            <div class="field">
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                </svg>
                <input class="input-field" name="password" id="contraseña" placeholder="Ingrese su contraseña" required type="password">
            </div>

            <button type="submit" name="iniciar_sesion" class="button3">Iniciar Sesión</button>
            
            <p class="text-center mt-4">
                <a href="views/registro.php" class="text-blue-500 hover:underline">¿Registrarte?</a>
            </p>
            <p class="text-center mt-4">
                <a href="views/olvideContraseña.php" class="text-blue-500 hover:underline">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
    </div>
</body>
</html>