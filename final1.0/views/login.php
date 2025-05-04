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


            if (password_verify($contraseña, $user['password'])) {

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

            /* Fondo del formulario */
            border-radius: 0.1rem;
            /* Bordes redondeados */

            /* Sombra */
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
            color: #d3d3d3;
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
            background-color: red;
            color: white;
        }
    </style>

</head>

<body class="flex justify-center items-center" id="asd">




    <div class="form-container p-8 w-96 h-full">


        <?php if (!empty($error)) echo "<p class='text-red-500 text-center'>$error</p>"; ?>



        <form class="form" action="" method="post">


            <div class="w-full sm:w-auto text-center sm:text-left">
                <img src="public/img/asd.jpg" alt="Logo" class="w-[6rem]">
                <h2 class="text-center text-2xl font-semibold text-white mt-6">Inicia Sesión</h2>
            </div>



            <div class="field">
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z"></path>
                </svg>
                <input autocomplete="off" type="email" name="correo" id="correo" placeholder="Ingrese su correo" required class="input-field">
            </div>


            <div class="field">
                <svg class="input-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path>
                </svg>
                <input placeholder="Password" class="input-field" name="password" id="contraseña" placeholder="Ingrese su contraseña" required type="password">
            </div>

            <button type="submit" name="iniciar_sesion" class="button3">Iniciar Sesion</button>
            <p class="text-center mt-4">
                <a href="views/olvideContraseña.php" class="text-blue-500 hover:underline">¿Olvidaste tu contraseña?</a>
            </p>
        </form>



    </div>



</body>

</html>