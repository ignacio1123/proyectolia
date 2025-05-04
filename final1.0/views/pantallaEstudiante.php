<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: No se ha identificado al usuario. Por favor, inicia sesión.";
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if (!is_numeric($id_usuario)) {
    echo "Error: ID de usuario no válido.";
    exit();
}

$sql = "SELECT id_solicitud, id_usuario, nombre_proyecto, estado  FROM solicitudes WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    echo "Error en la consulta preparada: " . $conn->error;
    exit();
}

$nombre = $_SESSION['nombre'];
?>

<?php
require_once 'db_connection.php';


$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT id_avance, id_usuario, nombre_documento, archivo FROM avances_proyectos WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado_avances = $stmt->get_result();
} else {
    echo "Error en la consulta preparada: " . $conn->error;
    exit();
}


?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Principal (Alumno)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../public/css/form.css">
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html,
    body {
        width: 100%;
        height: 100%;
    }
</style>

<body class="text-white font-sans flex">



    <?php
    require_once 'db_connection.php';

    if (!isset($_SESSION['id_usuario'])) {
        echo "Error: No se ha identificado al usuario. Por favor, inicia sesión.";
        header("Location: login.php");
        exit();
    }

    $id_usuario = $_SESSION['id_usuario'];

    if (!is_numeric($id_usuario)) {
        echo "Error: ID de usuario no válido.";
        exit();
    }

    $nombre = $_SESSION['nombre'];
    ?>

    <header class="absolute flex justify-end top-0 right-0 w-full bg-white p-4 shadow-md z-10" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
        <span class="text-black font-medium">Bienvenido, <?php echo htmlspecialchars($nombre); ?></span>
    </header>

    <nav class="absolute left-0 flex flex-col justify-between items-center bg-black text-white w-[12rem] h-full py-8 z-10 shadow-lg">
        <!-- Logo -->
        <div>
            <a href="/" class="text-[1.2rem] font-bold">
                Laboratorio<span class="text-yellow-400 text-[1.5rem] font-bold">LIA</span>
            </a>
        </div>

        <!-- Enlace Cerrar Sesión -->
        <div>
            <a href="../logout.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                Cerrar Sesión
            </a>
        </div>
    </nav>

    <section class="w-full pl-[17rem] flex justify-center items-center  flex-col">




        <div class="w-full flex justify-around items-center   ">
            <div class="flex flex-col">
                <h2 class="text-black  text-xl sm:text-2xl font-bold">Laboratorio de Innovación Aplicada</h2>
                <p class="text-black text-sm sm:text-base">Área Informática Concepción, Santo Tomás Concepción.</p>
            </div>



            <a href="./formulario.php" class=" w-[15rem] p-2  mt-4 bg-black text-white font-bold text-center rounded-md hover:bg-black/80">Postula a proyectos</a>
        </div>

        <div class="w-full flex justify-center items-center   gap-2  h-[70%] ">

            <div class="flex flex-col gap-4 w-[50%]  max-md:w-auto  ">



                <div class="w-full  flex flex-col">

                    <h1 class="text-black font-bold text-lg pb-4 pt-4">Tus estados de solicitud</h1>

                    <div class="w-full" style="  max-height: 200px; overflow-y: scroll;">

                        <table class="w-full text-left border-collapse shadow-md rounded-lg overflow-hidden">
                            <thead class="bg-black text-white">
                                <tr>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">ID Solicitud</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">ID Usuario</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">Nombre Proyecto</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-50 divide-y divide-gray-300">
                                <?php while ($avance = $resultado->fetch_assoc()) { ?>
                                    <tr class="hover:bg-gray-100 transition-colors">
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($avance['id_solicitud']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($avance['id_usuario']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($avance['nombre_proyecto']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700">
                                            <?php
                                            switch ($avance['estado']) {
                                                case 'Aprobada':
                                                    echo '<span class="px-3 py-1 bg-green-200 text-green-800 rounded-full text-xs font-medium">Aprobado</span>';
                                                    break;
                                                case 'Rechazada':
                                                    echo '<span class="px-3 py-1 bg-red-200 text-red-800 rounded-full text-xs font-medium">Rechazado</span>';
                                                    break;
                                                default:
                                                    echo '<span class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs font-medium">En Revisión</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>


                </div>


                <div class="w-full  flex   flex-col">
                    <h1 class="text-black font-bold text-lg pb-4 opacity-90">Avances de tus Proyectos</h1>

                    <div style="max-height: 400px; overflow-y: scroll;">
                        <table class="w-full text-left border-collapse shadow-md rounded-lg overflow-hidden">
                            <thead class="bg-black text-white">
                                <tr>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">ID Avance</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">Nombre Documento</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700">Ver Documento</th>
                                </tr>
                            </thead>
                            <tbody class="bg-gray-50 divide-y divide-gray-300">
                                <?php while ($avance = $resultado_avances->fetch_assoc()) { ?>
                                    <tr class="hover:bg-gray-100 transition-colors">
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($avance['id_avance']); ?></td>
                                        <td class="py-3 px-4 text-sm text-gray-700"><?php echo htmlspecialchars($avance['nombre_documento']); ?></td>
                                        <td class="py-3 px-4 text-sm text-blue-600">
                                            <a href="<?php echo htmlspecialchars($avance['archivo']); ?>" target="_blank" class="hover:underline">
                                                Ver Documento
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>

                    </div>



                </div>





            </div>

            <div class="flex flex-col gap-4 w-[50%] max-md:w-auto max-md:pt-10">
                <div class="w-full flex justify-center items-center flex-col pt-10">
                    <h2 class="text-black text-xl sm:text-2xl font-bold">Agregar Avance del Proyecto</h2>
                    <form action="agregar_avance.php" method="POST" enctype="multipart/form-data" class="w-full max-w-md">
                        <div class="mb-4">
                            <label for="nombre_documento" class="block text-black font-semibold">Nombre del Documento</label>
                            <input
                                type="text"
                                id="nombre_documento"
                                name="nombre_documento"
                                required
                                class="w-full p-3 border border-gray-300 rounded-md text-black"
                                placeholder="Nombre del Documento">
                        </div>

                        <div class="mb-4">
                            <label for="archivo" class="block text-black font-semibold">Subir Archivo</label>
                            <input
                                type="file"
                                id="archivo"
                                name="archivo"
                                accept=".docx, .doc"
                                required
                                class="w-full p-3 border border-gray-300 rounded-md text-black">
                        </div>

                        <button
                            type="submit"
                            class="w-full bg-black text-white font-bold py-3 rounded-md hover:bg-black/80">
                            Subir Avance
                        </button>
                    </form>
                </div>


            </div>

        </div>


    </section>
</body>

</html>