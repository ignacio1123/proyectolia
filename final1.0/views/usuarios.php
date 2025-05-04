<?php



session_start();

if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}


?>



<!DOCTYPE html>
<html lang="en">



<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal (Alumno)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./public/css/pantallaestudiante.css">


</head>




<body>




    <?php include './utils/nav.php'; ?>


    <table style="border: 1px solid black; ">


        <thead>
            <tr>
                <td>Solicitud</td>
            </tr>
        </thead>


        <tbody>

            <?php

                foreach ($usuarios as $usuario) {
                    echo $usuario['id_usuario'];
                }
 
            ?>

        </tbody>




    </table>












</body>



</html>
</body>



</html>