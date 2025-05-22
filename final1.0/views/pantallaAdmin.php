<?php
// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectolab";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejo de formularios
$mostrarModalExiste = false; // Variable para controlar el modal

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reducir'])) {
        $id_dispositivo = $_POST['id_dispositivo'];
        $cantidad_eliminar = intval($_POST['cantidad_eliminar']);

        $sql_check = "SELECT cantidad FROM dispositivos WHERE id_dispositivo = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("i", $id_dispositivo);
            $stmt_check->execute();
            $stmt_check->bind_result($cantidad_actual);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($cantidad_eliminar > 0 && $cantidad_eliminar <= $cantidad_actual) {
                $sql_update = "UPDATE dispositivos SET cantidad = cantidad - ? WHERE id_dispositivo = ?";
                if ($stmt_update = $conn->prepare($sql_update)) {
                    $stmt_update->bind_param("ii", $cantidad_eliminar, $id_dispositivo);
                    $stmt_update->execute();
                    $stmt_update->close();
                }

                // Si la cantidad llega a 0, poner en inactivo en vez de eliminar
                if ($cantidad_actual - $cantidad_eliminar <= 0) {
                    $sql_inactivo = "UPDATE dispositivos SET estado = 'inactivo', cantidad = 0 WHERE id_dispositivo = ?";
                    if ($stmt_inactivo = $conn->prepare($sql_inactivo)) {
                        $stmt_inactivo->bind_param("i", $id_dispositivo);
                        $stmt_inactivo->execute();
                        $stmt_inactivo->close();
                    }
                }
            }
        }
    } elseif (isset($_POST['aumentar'])) {
        // Aumentar cantidad del dispositivo
        $id_dispositivo = $_POST['id_dispositivo'];
        $cantidad_agregar = intval($_POST['cantidad_agregar']);
        if ($cantidad_agregar > 0) {
            // Consultar el estado actual
            $sql_estado = "SELECT estado FROM dispositivos WHERE id_dispositivo = ?";
            if ($stmt_estado = $conn->prepare($sql_estado)) {
                $stmt_estado->bind_param("i", $id_dispositivo);
                $stmt_estado->execute();
                $stmt_estado->bind_result($estado_actual);
                $stmt_estado->fetch();
                $stmt_estado->close();
            }

            // Si está inactivo, activar al aumentar cantidad
            if ($estado_actual === 'inactivo') {
                $sql_update = "UPDATE dispositivos SET cantidad = cantidad + ?, estado = 'activo' WHERE id_dispositivo = ?";
            } else {
                $sql_update = "UPDATE dispositivos SET cantidad = cantidad + ? WHERE id_dispositivo = ?";
            }
            if ($stmt_update = $conn->prepare($sql_update)) {
                $stmt_update->bind_param("ii", $cantidad_agregar, $id_dispositivo);
                $stmt_update->execute();
                $stmt_update->close();
            }
        }
    } elseif (isset($_POST['cambiar_estado'])) {
        $id_dispositivo = $_POST['id_dispositivo'];
        $nuevo_estado = $_POST['nuevo_estado'];
        $sql_estado = "UPDATE dispositivos SET estado = ? WHERE id_dispositivo = ?";
        if ($stmt_estado = $conn->prepare($sql_estado)) {
            $stmt_estado->bind_param("si", $nuevo_estado, $id_dispositivo);
            $stmt_estado->execute();
            $stmt_estado->close();
        }
    } elseif (isset($_POST['agregar'])) {
        $nombre_dispositivo = trim($_POST['nombre_dispositivo']);
        $cantidad = intval($_POST['cantidad']);
        $estado = $_POST['estado'];
        $almacen = trim($_POST['almacen']);

        // Verificar si ya existe un dispositivo con el mismo nombre (sin importar el almacén)
        $sql_check = "SELECT COUNT(*) FROM dispositivos WHERE nombre_dispositivo = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $nombre_dispositivo);
            $stmt_check->execute();
            $stmt_check->bind_result($existe);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($existe > 0) {
                $mostrarModalExiste = true; // Activar modal
            } else {
                $sql_insert = "INSERT INTO dispositivos (nombre_dispositivo, cantidad, estado, almacen) VALUES (?, ?, ?, ?)";
                if ($stmt = $conn->prepare($sql_insert)) {
                    $stmt->bind_param("siss", $nombre_dispositivo, $cantidad, $estado, $almacen);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
}

// Recuperar dispositivos de la base de datos
$sql = "SELECT id_dispositivo, nombre_dispositivo, cantidad, estado, almacen FROM dispositivos";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel De Administración -gestión-</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
</head>

<body class="bg-gray-100">

    <div class="bg-[#00796b] text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Administrador</h1>
        <div>
            <a href="../logout.php" class="bg-green-400 hover:bg-green-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                Cerrar Sesión
            </a>
        </div>
    </div>

    <div class="p-8">
        <div class="bg-white rounded-lg shadow p-6 mb-6">

            <div class="flex w-full justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-[#00796b] mb-4">Agregar Dispositivo</h2>

                </div>

                <div>
                    <button onclick="window.location.href='gestion_usuarios.php'" class="bg-[#4CAF50] hover:bg-[#388E3C] text-white px-2 py-1 rounded mr-2 transition duration-200">
                        Usuarios Registrados
                    </button>
                    <button onclick="window.location.href='gestion_registroUsuarios.php'" class="bg-[#4CAF50] hover:bg-[#388E3C] text-white px-2 py-1 rounded transition duration-200">
                        Usuarios en Revisión
                    </button>

                </div>

            </div>

            <form method="POST" class="flex  gap-10">

                <input type="text" name="nombre_dispositivo" placeholder="Nombre del dispositivo" required class="p-2 rounded border w-full">
                <input type="number" name="cantidad" placeholder="Cantidad" required class="p-2 rounded border w-full">

                <div class="w-full relative">
                    <div class="absolute -top-10 right-0">

                        <button type="button" class="bg-[#00796b] text-white px-3 py-1 rounded-lg text-sm relative group">

                            <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 w-max bg-gray-800 text-white text-xs rounded-md py-1 px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                                <ul>
                                    <li>
                                        Almacen 1 - Kit Arduino
                                    </li>

                                    <li>
                                        Almacen 2 - Arduino
                                    </li>

                                    <li>
                                        Almacen 3 - Cables
                                    </li>

                                    <li>
                                        Almacen 4 - LEDS
                                    </li>

                                </ul>
                            </div>

                        </button>

                    </div>

                    <input type="text" name="almacen" placeholder="almacen" required class="p-2 rounded border w-full">

                </div>

                <select name="estado" class="p-2 rounded border w-full">
                    <option value="activo">Activo</option>
                    <option value="inactivo">Inactivo</option>
                </select>

                <button type="submit" name="agregar" class="bg-[#4CAF50] hover:bg-[#388E3C] text-white px-4 py-2 rounded transition duration-200">Agregar</button>

            </form>

        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-[#00796b] mb-4">Listado de Dispositivos</h2>
            <table id="tablaDispositivos" class="w-full border-collapse border border-gray-200">

                <thead>
                    <tr class="bg-[#00796b] text-white">
                        <th class=" px-4 py-2 text-left">ID</th>
                        <th class=" px-4 py-2 text-left">Nombre del Dispositivo</th>
                        <th class=" px-4 py-2 text-left">Cantidad</th>
                        <th class=" px-4 py-2 text-left">Estado</th>
                        <th class=" px-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $estado = $row['estado'];
                            $disabled_reducir = $estado === 'inactivo' ? 'disabled' : '';
                            $disabled_aumentar = ''; // Siempre habilitado
                            echo "<tr class='hover:bg-gray-100 even:bg-gray-50'>
                        <td class='border border-gray-100 px-4 py-2'>{$row['id_dispositivo']}</td>
                        <td class='border border-gray-100 px-4 py-2'>{$row['nombre_dispositivo']}</td>
                        <td class='border border-gray-100 px-4 py-2'>{$row['cantidad']}</td>
                        <td class='border border-gray-100 px-4 py-2'>{$estado}</td>
                        <td class='border border-gray-100 px-4 py-2 text-center'>
                            <!-- Botón para reducir cantidad -->
                            <form method='POST' action='' class='inline'>
                                <input type='hidden' name='id_dispositivo' value='{$row['id_dispositivo']}'>
                                <input type='number' name='cantidad_eliminar' min='1' max='{$row['cantidad']}' placeholder='Cantidad' class='p-1 rounded border w-16' $disabled_reducir>
                                <button type='submit' name='reducir' class='bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600' $disabled_reducir>Reducir</button>
                            </form>
                            <!-- Botón para aumentar cantidad -->
                            <form method='POST' action='' class='inline ml-2'>
                                <input type='hidden' name='id_dispositivo' value='{$row['id_dispositivo']}'>
                                <input type='number' name='cantidad_agregar' min='1' placeholder='Cantidad' class='p-1 rounded border w-16' $disabled_aumentar>
                                <button type='submit' name='aumentar' class='bg-[#4CAF50] text-white px-2 py-1 rounded hover:bg-[#388E3C]' $disabled_aumentar>Aumentar</button>
                            </form>
                            <!-- Botones para cambiar estado -->
                            <form method='POST' action='' class='inline ml-2'>
                                <input type='hidden' name='id_dispositivo' value='{$row['id_dispositivo']}'>
                                <input type='hidden' name='nuevo_estado' value='activo'>
                                <button type='submit' name='cambiar_estado' class='bg-[#4CAF50] text-white px-2 py-1 rounded hover:bg-[#388E3C]' " . ($estado === 'activo' ? 'disabled' : '') . ">Activo</button>
                            </form>
                            <form method='POST' action='' class='inline ml-2'>
                                <input type='hidden' name='id_dispositivo' value='{$row['id_dispositivo']}'>
                                <input type='hidden' name='nuevo_estado' value='inactivo'>
                                <button type='submit' name='cambiar_estado' class='bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600' " . ($estado === 'inactivo' ? 'disabled' : '') . ">Inactivo</button>
                            </form>
                        </td>
                    </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center border border-gray-300 px-4 py-2'>No hay dispositivos registrados.</td></tr>";
                    }
                    ?>
                </tbody>

            </table>
        </div>

    </div>

    <!-- Modal de dispositivo existente -->
    <?php if ($mostrarModalExiste): ?>
    <div id="modalExiste" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-sm w-full text-center relative animate-fade-in">
            <button onclick="document.getElementById('modalExiste').style.display='none'" class="absolute top-2 right-2 text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
            <div class="flex flex-col items-center">
                <svg class="w-16 h-16 text-red-400 mb-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="white"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9l-6 6m0-6l6 6" />
                </svg>
                <h2 class="text-xl font-semibold text-red-600 mb-2">¡Atención!</h2>
                <p class="text-gray-700 mb-4">El dispositivo ya existe en este almacén.</p>
                <button onclick="document.getElementById('modalExiste').style.display='none'" class="bg-[#00796b] hover:bg-[#005a4f] text-white px-6 py-2 rounded transition duration-200">Cerrar</button>
            </div>
        </div>
    </div>
    <style>
    @keyframes fade-in {
        from { opacity: 0; transform: scale(0.95);}
        to { opacity: 1; transform: scale(1);}
    }
    .animate-fade-in {
        animation: fade-in 0.2s ease;
    }
    </style>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaDispositivos').DataTable();
        });

        $('#tablaDispositivos').DataTable({
            language: {
                "sProcessing": "Procesando...",
                "sLengthMenu": "",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de MAX registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }
            }
        });
    </script>
</body>
</html>