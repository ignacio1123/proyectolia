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

$sql = "SELECT s.id_solicitud, s.id_usuario, u.nombre, s.nombre_proyecto, s.estado, s.acotaciones 
        FROM solicitudes s
        INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
        WHERE s.id_usuario = ?";
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
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
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

    <nav class="fixed left-0 top-0 h-full w-[13rem] bg-[#00897b] flex flex-col justify-between items-center z-20">
    <!-- Logo -->
    <div class="w-full pt-8 pb-2 flex flex-col items-center">
        <span class="text-white text-xl font-bold leading-tight">
            Laboratorio<span class="text-[#8BC34A]">LIA</span>
        </span>
    </div>
    <!-- Botón Cerrar Sesión -->
    <div class="w-full pb-6 flex flex-col items-center">
        <a href="../logout.php"
           class="bg-[#4CAF50] hover:bg-[#388E3C] text-white font-semibold py-2 px-6 rounded transition duration-200 shadow text-base">
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



            <a href="./formulario.php" class="w-[15rem] p-2 mt-4 bg-[#4CAF50] hover:bg-[#388E3C] text-white font-bold text-center rounded-md transition duration-200">Postula a Proyectos</a>
        </div>

        <div class="w-full flex justify-center items-center   gap-2  h-[70%] ">

            <div class="flex flex-col gap-4 w-[50%]  max-md:w-auto  ">



                <div class="w-full  flex flex-col">

                    <h1 class="text-black font-bold text-lg pb-4 pt-4">Tus estados de solicitud</h1>

                    <div class="w-full" style="max-height: 300px; overflow-y: auto;">
                        <table class="w-full text-left border-collapse shadow-md rounded-lg overflow-hidden">
                            <thead class="bg-[#00796b] text-white sticky top-0 z-10">
                                <tr>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700 text-center">ID Solicitud</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700 text-center">líder de Proyecto</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700 text-center">Nombre Proyecto</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700 text-center">Estado</th>
                                    <th class="py-3 px-4 text-sm font-semibold uppercase border-b border-gray-700 text-center">Retroalimentación</th>
                                </tr>
                            </thead>
                            <tbody id="tabla-estados" class="bg-gray-50 divide-y divide-gray-300">
                                <!-- Aquí se llenarán los datos por JS -->
                                  <script>
        function mostrarAcotaciones(acotaciones) {
            const modal = document.getElementById('modalAcotaciones');
            const contenido = document.getElementById('contenidoAcotaciones');
            contenido.value = acotaciones || 'No hay retroalimentación disponible.';
            modal.classList.remove('hidden');
        }

        function cerrarModal() {
            // Ocultar el modal
            const modal = document.getElementById('modalAcotaciones');
            modal.classList.add('hidden');
        }

        function cargarEstados() {
            fetch('obtener_estados.php')
                .then(response => response.json())
                .then(data => {
                    const tbody = document.getElementById('tabla-estados');
                    tbody.innerHTML = '';
                    data.forEach(avance => {
                        let estadoHtml = '';
                        switch (avance.estado) {
                            case 'Aprobado':
                                estadoHtml = '<span class="px-3 py-1 bg-[#C8E6C9] text-[#388E3C] rounded-full text-xs font-medium">Aprobado</span>';
                                break;
                            case 'Rechazado':
                                estadoHtml = '<span class="px-3 py-1 bg-[#FFCDD2] text-[#C62828] rounded-full text-xs font-medium">Rechazado</span>';
                                break;
                            case 'Pendiente':
                                estadoHtml = '<span class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs font-medium">Pendiente</span>';
                                break;
                            default:
                                estadoHtml = '<span class="px-3 py-1 bg-yellow-200 text-yellow-800 rounded-full text-xs font-medium">En Revisión</span>';
                        }
                        tbody.innerHTML += `
                            <tr class="hover:bg-gray-100 transition-colors">
                                <td class="py-3 px-4 text-sm text-gray-700">${avance.id_solicitud}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">${avance.nombre}</td>
                                <td class="py-3 px-4 text-sm text-gray-700" title="${avance.nombre_proyecto}">
                                    ${avance.nombre_proyecto.length > 60 ? avance.nombre_proyecto.substring(0, 60) + '...' : avance.nombre_proyecto}
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-700">${estadoHtml}</td>
                                <td class="py-3 px-4 text-sm text-gray-700">
                                    <button
                                        type="button"
                                        class="bg-[#4CAF50] text-white px-2 py-0.5 rounded-sm hover:bg-[#388E3C] text-xs font-medium"
                                        style="min-width: 0; height: 1.5rem; line-height: 1rem;"
                                        onclick="mostrarAcotaciones('${avance.acotaciones ? avance.acotaciones.replace(/'/g, "\\'") : 'Espere su retroalimentación...'}')">
                                        Ver Retroalimentación
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                });
        }

        // Cargar al inicio y cada 5 segundos
        cargarEstados();
        setInterval(cargarEstados, 5000);
    </script>
                            </tbody>
                        </table>
                    </div>

                </div>


                <div class="w-full  flex   flex-col">
                    <h1 class="text-black font-bold text-lg pb-4 opacity-90">Avances de tus Proyectos</h1>

                    <div style="max-height: 400px; overflow-y: scroll;">
                        <table class="w-full text-left border-collapse shadow-md rounded-lg overflow-hidden">
                            <thead class="bg-[#00796b] text-white">
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
                            class="w-full bg-[#4CAF50] hover:bg-[#388E3C] text-white font-bold py-3 rounded-md transition duration-200">
                            Subir Avance
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal para mostrar las acotaciones -->
        <div id="modalAcotaciones" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden z-50">
            <div class="bg-white p-8 rounded-xl shadow-2xl w-[90%] max-w-md border-t-8 border-[#4CAF50] relative">
                <h2 class="text-2xl font-bold mb-4 text-center text-[#00796b] tracking-wide">Retroalimentación</h2>
                <div class="flex flex-col items-center">
                    <textarea
                        id="contenidoAcotaciones"
                        maxlength="250"
                        class="w-full bg-white border-2 border-[#4CAF50] text-[#263238] p-4 rounded-lg shadow-inner min-h-[100px] resize-none text-base leading-relaxed font-medium focus:outline-none"
                        readonly
                    ></textarea>
                </div>
                <div class="mt-6 flex justify-center">
                    <button
                        type="button"
                        class="bg-green-400 hover:bg-green-500 text-[#263238] px-6 py-2 rounded-lg font-bold shadow transition duration-200"
                        onclick="cerrarModal()">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </section>

</body>

</html>