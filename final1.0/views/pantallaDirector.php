<?php
include 'db_connection.php';

$almacenes = isset($_GET['almacen']) ? $_GET['almacen'] : '';

$query = "SELECT id_dispositivo, nombre_dispositivo, cantidad, estado, almacen FROM dispositivos";
if ($almacenes != '') {
    $query .= " WHERE almacen = ?";
}

$stmt = $conn->prepare($query);
if ($almacenes != '') {
    $stmt->bind_param("s", $almacenes);
}

$stmt->execute();
$result = $stmt->get_result();
$dispositivos = $result->fetch_all(MYSQLI_ASSOC);

$query_almacen = "SELECT DISTINCT almacen FROM dispositivos";
$result_almacen = $conn->query($query_almacen);
$almacenes = $result_almacen->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: No se ha identificado al usuario. Por favor, inicia sesión.";
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$nombre = $_SESSION['nombre'];
?>

<?php
include 'db_connection.php';

$sql = " SELECT 
    solicitudes.id_solicitud, 
    solicitudes.id_usuario, 
    solicitudes.nombre_proyecto, 
    solicitudes.descripcion,
    solicitudes.propuesta_valor, 
    solicitudes.merito_innovativo, 
    solicitudes.redes_apoyo,
    solicitudes.factores_criticos, 
    solicitudes.oportunidad_mercado, 
    solicitudes.potencial_mercado,
    solicitudes.aspectos_validar, 
    solicitudes.presupuesto_preliminar, 
    solicitudes.fecha_solicitud,
    solicitudes.acotaciones,
    solicitudes.estado,

    GROUP_CONCAT(
        CONCAT(participantes.nombre, ' (', participantes.rut, ', ', participantes.carrera, ', ', participantes.rol, ', ', participantes.tipo, ')')
        ORDER BY participantes.nombre ASC SEPARATOR '|||'
    ) AS participantes_grupo,
    GROUP_CONCAT(
        CONCAT(solicitud_dispositivos.id_dispositivo, ':', dispositivos.nombre_dispositivo, ' (Cantidad: ', solicitud_dispositivos.cantidad, ')')
        ORDER BY solicitud_dispositivos.id_dispositivo ASC SEPARATOR ', '
    ) AS solicitudes_dispositivos

    FROM solicitudes
    LEFT JOIN participantes ON solicitudes.id_solicitud = participantes.id_solicitud
    LEFT JOIN solicitud_dispositivos ON solicitudes.id_solicitud = solicitud_dispositivos.id_solicitud
    LEFT JOIN dispositivos ON solicitud_dispositivos.id_dispositivo = dispositivos.id_dispositivo
    GROUP BY solicitudes.id_solicitud;
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    echo "Error en la consulta preparada: " . $conn->error;
    exit();
}
?>

<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_solicitud = $_POST['id_solicitud'];
    $accion = $_POST['accion'];

    $estado = ($accion === 'Aprobar') ? 'Aprobada' : (($accion === 'Rechazar') ? 'Rechazada' : (($accion === 'Pendiente') ? 'Pendiente' : ''));

    if ($estado === '') {
        echo "Acción no válida.";
        exit();
    }

    $sql = "UPDATE solicitudes SET estado = ? WHERE id_solicitud = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("si", $estado, $id_solicitud);
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
        } else {
            echo "Error al actualizar la solicitud: " . $conn->error;
        }
    } else {
        echo "Error en la consulta preparada: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pantalla Director</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
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

<body class="bg-white flex">

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

    <div class="w-full pl-[10rem] flex justify-center items-center h-full">
        <div class="w-full h-full flex justify-center items-center">
            <div class="w-[89%] flex flex-col">
                <div class="w-full mt-16"> <!-- Added mt-16 for margin-top -->
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-black block text-xl font-semibold">Bienvenido Director, <?php echo $_SESSION['nombre'] ?></span>
                        <a href="vistaDispositivosFaltantes.php" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg px-5 py-2.5">
                            Ver Dispositivos Faltantes
                        </a>
                    </div>

                    <?php
                    $almacenSeleccionada = isset($_GET['almacen']) ? $_GET['almacen'] : '';
                    ?>

                    <form method="get" action="" class="mb-6">
                        <label for="almacen" class="text-black block text-sm mb-2">Filtrar por almacen:</label>
                        <div class="flex justify-between items-center space-x-4 gap-10">
                            <select name="almacen" id="almacen" class="w-full bg-black/90 rounded-md text-white p-3 focus:outline-none focus:ring-2 focus:ring-blue-300">
                                <option value="">Selecciona una Almacen</option>
                                <?php foreach ($almacenes as $almacen): ?>
                                    <option value="<?php echo htmlspecialchars($almacen['almacen']); ?>"
                                        <?php echo ($almacenSeleccionada == $almacen['almacen']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($almacen['almacen']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="p-3 bg-red-700 text-white rounded-md hover:bg-orange-500 transition duration-200">
                                Filtrar
                            </button>
                        </div>
                    </form>

                    <h1 class="text-lg text-black font-bold p-2">Almacén de <?php echo $almacenSeleccionada ? htmlspecialchars($almacenSeleccionada) : 'todos'; ?></h1>

                    <div class="rounded-lg shadow-lg" style="max-height: 400px; overflow-y: scroll; border: 1px solid #ddd;">
                        <table class="min-w-full table-auto border-collapse bg-gray-100">
                            <thead class="bg-black/90">
                                <tr>
                                    <th class="px-4 py-2 text-center text-white">#</th>
                                    <th class="px-4 py-2 text-center font-bold text-white">Dispositivo</th>
                                    <th class="px-4 py-2 text-center text-white">Cantidad</th>
                                    <th class="px-4 py-2 text-center text-white">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-700">
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <tr class="">
                                        <td class="px-4 py-2 text-black text-center"><?php echo htmlspecialchars($dispositivo['id_dispositivo']); ?></td>
                                        <td class="px-4 py-2 text-black text-center"><?php echo htmlspecialchars($dispositivo['nombre_dispositivo']); ?></td>
                                        <td class="px-4 py-2 text-black text-center"><?php echo htmlspecialchars($dispositivo['cantidad']); ?></td>
                                        <td class="px-4 py-2 text-black text-center"><?php echo htmlspecialchars($dispositivo['estado']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="w-full">
                    <h1 class="text-black font-bold p-5 text-lg">Solicitudes de Estudiantes</h1>

                    <div class="flex-col w-full h-auto rounded-lg shadow-lg" style="border: 1px solid #ddd; max-height: 400px; overflow-y: scroll;">
                        <table class="table-auto w-full text-left text-sm text-gray-700">
                            <thead class="bg-black/90">
                                <tr>
                                    <th class="border-b py-2 px-4 text-white">#</th>
                                    <th class="border-b py-2 px-4 text-white">Nombre Proyecto</th>
                                    <th class="border-b py-2 px-4 text-white">Fecha</th>
                                    <th class="border-b py-2 px-4 text-white">Estado</th>
                                    <th class="border-b py-2 px-4 text-white">Visualizar</th>
                                </tr>
                            </thead>
                            <tbody class="p-2">
                                <?php while ($solicitud = $resultado->fetch_assoc()) { ?>
                                    <tr class="border-b">
                                        <td class="py-2 px-4"><?php echo htmlspecialchars($solicitud['id_solicitud']); ?></td>
                                        <td class="py-2 px-4"><?php echo htmlspecialchars($solicitud['nombre_proyecto']); ?></td>
                                        <td class="py-2 px-4"><?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></td>
                                        <td class="py-2 px-4"><?php echo htmlspecialchars($solicitud['estado']); ?></td>
                                        <td class="py-2 px-4">
                                            <button data-modal-target="authentication-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                data-modal-toggle="authentication-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg text-sm px-5 py-2.5"
                                                type="button">
                                                Visualizar
                                            </button>

                                            <div id="authentication-modal-<?php echo $solicitud['id_solicitud']; ?>" tabindex="-1" aria-hidden="true"
                                            class="hidden fixed inset-0 z-50 flex justify-center items-center bg-opacity-50">
                                            <div class="relative w-[60%] max-h-[90vh] p-6 bg-white rounded-lg shadow-lg">
                                                <div class="flex items-center justify-between p-4 border-b">
                                                    <h3 class="text-xl font-semibold text-gray-900">
                                                        Numero de Proyecto: <?php echo htmlspecialchars($solicitud['id_solicitud']); ?>
                                                    </h3>
                                                    <button type="button"
                                                        class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg p-2 inline-flex justify-center items-center"
                                                        data-modal-hide="authentication-modal-<?php echo $solicitud['id_solicitud']; ?>">
                                                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                    <div class="flex flex-col md:flex-row w-auto p-2 space-y-4 md:space-y-0 md:space-x-8 shadow-lg">
                                                        <section class="flex flex-col border border-1">
                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php 
                                                                    $liderQuery = "SELECT nombre, apellido FROM usuarios WHERE id_usuario = ?";
                                                                    $liderStmt = $conn->prepare($liderQuery);
                                                                    $liderStmt->bind_param("i", $solicitud['id_usuario']);
                                                                    $liderStmt->execute();
                                                                    $liderResult = $liderStmt->get_result();
                                                                    if ($liderRow = $liderResult->fetch_assoc()) {
                                                                        echo htmlspecialchars($liderRow['nombre'] . ' ' . $liderRow['apellido']);
                                                                    } else {
                                                                        echo "Líder no encontrado";
                                                                    }
                                                                    $liderStmt->close();
                                                                ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Líder de Proyecto
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['nombre_proyecto']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Nombre del Proyecto
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['descripcion']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Descripción
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['propuesta_valor']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Propuesta Valor
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['merito_innovativo']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Mérito Innovativo
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['redes_apoyo']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Redes de Apoyo
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['factores_criticos']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Factores Críticos
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['oportunidad_mercado']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Oportunidad de Mercado
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['potencial_mercado']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Potencial Mercado
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['aspectos_validar']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Aspectos a Validar
                                                            </button>

                                                            <button type="button"
                                                                onclick="updateInfo('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo htmlspecialchars($solicitud['presupuesto_preliminar']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Presupuesto Preliminar
                                                            </button>

                                                            <button type="button"
                                                                onclick="showParticipantes('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo str_replace("'", "\\'", $solicitud['participantes_grupo']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Participantes
                                                            </button>

                                                            <button type="button"
                                                                onclick="showDispositivos('info-display-<?php echo $solicitud['id_solicitud']; ?>', '<?php echo str_replace("'", "\\'", $solicitud['solicitudes_dispositivos']); ?>')"
                                                                class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                                Dispositivos
                                                            </button>
                                                        </section>

                                                        <section class="flex-1 bg-white h-auto">
                                                            <div id="info-display-<?php echo $solicitud['id_solicitud']; ?>"
                                                                class="flex-col mt-4 text-gray-800 p-4 bg-gray-100 rounded-lg w-auto h-auto flex justify-center flex-wrap overflow-y-auto"
                                                                style="max-height: 300px; word-break: break-word; white-space: pre-wrap;">
                                                            </div>
                                                        </section>

                                                        <script>
                                                            function updateInfo(containerId, content) {
                                                                const container = document.getElementById(containerId);
                                                                container.innerHTML = content;
                                                            }
                                                            
                                                            function showParticipantes(containerId, participantesStr) {
                                                                const container = document.getElementById(containerId);
                                                                
                                                                // Si no hay participantes
                                                                if (!participantesStr || participantesStr === "null") {
                                                                    container.innerHTML = "No hay participantes registrados";
                                                                    return;
                                                                }
                                                                
                                                                // Dividir la cadena en participantes individuales
                                                                const participantes = participantesStr.split('|||');
                                                                
                                                                // Crear tabla HTML
                                                                let tableHTML = `
                                                                <table class="min-w-full bg-white border">
                                                                    <thead class="bg-gray-800 text-white">
                                                                        <tr>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Nombre</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">RUT</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Carrera</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Rol</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Tipo</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;
                                                                
                                                                // Agregar filas para cada participante
                                                                participantes.forEach(participante => {
                                                                    // Extraer los datos del formato: nombre (rut, carrera, rol, tipo)
                                                                    const match = participante.match(/(.+?) \((.+?), (.+?), (.+?), (.+?)\)/);
                                                                    
                                                                    if (match) {
                                                                        const [_, nombre, rut, carrera, rol, tipo] = match;
                                                                        tableHTML += `
                                                                        <tr class="border-b hover:bg-gray-50">
                                                                            <td class="py-2 px-3 text-sm">${nombre}</td>
                                                                            <td class="py-2 px-3 text-sm">${rut}</td>
                                                                            <td class="py-2 px-3 text-sm">${carrera}</td>
                                                                            <td class="py-2 px-3 text-sm">${rol}</td>
                                                                            <td class="py-2 px-3 text-sm">${tipo}</td>
                                                                        </tr>`;
                                                                    }
                                                                });
                                                                
                                                                tableHTML += `
                                                                    </tbody>
                                                                </table>`;
                                                                
                                                                container.innerHTML = tableHTML;
                                                            }
                                                          // Función JavaScript modificada para mostrar el nombre del dispositivo
                                                            function showDispositivos(containerId, dispositivosStr) {
                                                                const container = document.getElementById(containerId);
                                                                
                                                                // Si no hay dispositivos
                                                                if (!dispositivosStr || dispositivosStr === "null") {
                                                                    container.innerHTML = "No hay dispositivos solicitados";
                                                                    return;
                                                                }
                                                                
                                                                // Dividir la cadena en dispositivos individuales
                                                                const dispositivos = dispositivosStr.split(', ');
                                                                
                                                                // Crear tabla HTML
                                                                let tableHTML = `
                                                                <table class="min-w-full bg-white border">
                                                                    <thead class="bg-gray-800 text-white">
                                                                        <tr>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">ID</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Nombre Dispositivo</th>
                                                                            <th class="py-2 px-3 text-left text-xs font-medium uppercase">Cantidad</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>`;
                                                                
                                                                // Agregar filas para cada dispositivo
                                                                dispositivos.forEach(dispositivo => {
                                                                    // Extraer los datos del formato: id_dispositivo:nombre_dispositivo (Cantidad: X)
                                                                    const match = dispositivo.match(/(\d+):(.+?) \(Cantidad: (\d+)\)/);
                                                                    
                                                                    if (match) {
                                                                        const [_, idDispositivo, nombreDispositivo, cantidad] = match;
                                                                        tableHTML += `
                                                                        <tr class="border-b hover:bg-gray-50">
                                                                            <td class="py-2 px-3 text-sm">${idDispositivo}</td>
                                                                            <td class="py-2 px-3 text-sm">${nombreDispositivo}</td>
                                                                            <td class="py-2 px-3 text-sm">${cantidad}</td>
                                                                        </tr>`;
                                                                    }
                                                                });
                                                                
                                                                tableHTML += `
                                                                    </tbody>
                                                                </table>`;
                                                                
                                                                container.innerHTML = tableHTML;
                                                            }
                                                        </script>
                                                    </div>

                                                    <form method="POST" action="">
                                                        <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                                                        <div class="flex gap-4 mt-4">
                                                            <button type="button" onclick="setAccion('<?php echo $solicitud['id_solicitud']; ?>', 'Aprobar')"
                                                                data-modal-target="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                data-modal-toggle="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg w-full">
                                                                Aprobar
                                                            </button>
                                                            <button type="button" onclick="setAccion('<?php echo $solicitud['id_solicitud']; ?>', 'Rechazar')"
                                                                data-modal-target="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                data-modal-toggle="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg w-full">
                                                                Rechazar
                                                            </button>
                                                            <button type="button" onclick="setAccion('<?php echo $solicitud['id_solicitud']; ?>', 'Pendiente')"
                                                                data-modal-target="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                data-modal-toggle="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>"
                                                                class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg w-full">
                                                                Pendiente
                                                            </button>
                                                            
                                                            <div id="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>" tabindex="-1" aria-hidden="true"
                                                                class="hidden fixed inset-0 z-50 flex justify-center items-center bg-opacity-50">
                                                                <div class="relative w-[40%] max-h-[90vh] p-4 bg-white rounded-lg shadow-lg">
                                                                    <div class="flex items-center justify-between p-4 border-b">
                                                                        <h3 class="text-xl font-semibold text-gray-900">Acotaciones</h3>
                                                                        <button type="button"
                                                                            class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg p-2"
                                                                            data-modal-hide="feedback-modal-<?php echo $solicitud['id_solicitud']; ?>">
                                                                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                                    d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                                                                            </svg>
                                                                        </button>
                                                                    </div>
                                                                    <form method="POST" action="">
                                                                        <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                                                                        <textarea name="acotaciones" class="w-full p-2 border rounded-lg" rows="5" placeholder="Escribe tus acotaciones..."></textarea>
                                                                        <input type="hidden" name="accion" id="accion-<?php echo $solicitud['id_solicitud']; ?>" value="">
                                                                        <div class="flex justify-end mt-4">
                                                                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg px-5 py-2.5">
                                                                                Guardar
                                                                            </button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>

                                                    <?php
                                                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_solicitud'])) {
                                                        $id_solicitud = $_POST['id_solicitud'];
                                                        $accion = $_POST['accion'];
                                                        $acotaciones = $_POST['acotaciones'];
                                                        
                                                        $estado = ($accion === 'Aprobar') ? 'Aprobada' : (($accion === 'Rechazar') ? 'Rechazada' : (($accion === 'Pendiente') ? 'Pendiente' : ''));
                                                        
                                                        if ($estado !== '') {
                                                            $sql = "UPDATE solicitudes SET estado = ?, acotaciones = ? WHERE id_solicitud = ?";
                                                            $stmt = $conn->prepare($sql);
                                                            $stmt->bind_param("ssi", $estado, $acotaciones, $id_solicitud);
                                                            
                                                            if ($stmt->execute()) {
                                                                header("Location: " . $_SERVER['PHP_SELF']);
                                                            } else {
                                                                echo "Error al actualizar la solicitud: " . $conn->error;
                                                            }
                                                            $stmt->close();
                                                        }
                                                    }
                                                    ?>

                                                    <script>
                                                        function setAccion(idSolicitud, accion) {
                                                            document.getElementById('accion-' + idSolicitud).value = accion;
                                                        }
                                                    </script>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>