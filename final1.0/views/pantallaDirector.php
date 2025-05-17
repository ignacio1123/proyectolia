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

// Obtener dispositivos faltantes agrupados por id_solicitud
$dispositivosFaltantesPorSolicitud = [];
$consultaFaltantes = $conn->query("SELECT id_solicitud, id_dispositivo, nombre_dispositivo, cantidad_dispositivo FROM dispositivos_faltante");
while ($row = $consultaFaltantes->fetch_assoc()) {
    $idSolicitud = $row['id_solicitud'];
    if (!isset($dispositivosFaltantesPorSolicitud[$idSolicitud])) {
        $dispositivosFaltantesPorSolicitud[$idSolicitud] = [];
    }
    $dispositivosFaltantesPorSolicitud[$idSolicitud][] = [
        'id' => $row['id_dispositivo'],
        'nombre' => $row['nombre_dispositivo'],
        'cantidad' => $row['cantidad_dispositivo'],
        'solicitud' => $idSolicitud
    ];
}

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
// Procesar el formulario para agregar acotaciones y actualizar estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_solicitud']) && isset($_POST['acotaciones']) && isset($_POST['accion'])) {
    $id_solicitud = $_POST['id_solicitud'];
    $acotaciones = $_POST['acotaciones'];
    $accion = $_POST['accion'];

    // Validar que la acotación y el estado no estén vacíos
    if (!empty($acotaciones) && !empty($accion)) {
        // Actualizar la base de datos
        $sql = "UPDATE solicitudes SET acotaciones = ?, estado = ? WHERE id_solicitud = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $acotaciones, $accion, $id_solicitud);

        $stmt->execute();
        $stmt->close();
    } else {
        echo "La acotación y el estado no pueden estar vacíos.";
    }
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

    <!-- Barra lateral izquierda -->
    <nav class="fixed left-0 top-0 flex flex-col justify-between items-center bg-black text-white w-[12rem] h-full py-8 z-20 shadow-lg">
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

    <header class="absolute flex justify-end top-0 right-0 w-full bg-white p-4 shadow-md z-10" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
        <span class="text-black font-medium">Bienvenido, <?php echo htmlspecialchars($nombre); ?></span>
    </header>

    <!-- Ajusta el padding-left para dejar espacio a la barra lateral -->
    <div class="w-full pl-[12rem] flex justify-center items-center h-full">
        <div class="w-[89%] flex flex-col">
            <div class="w-full mt-16"> <!-- Added mt-16 for margin-top -->
                <div class="flex justify-between items-center mb-4">
                    <span class="text-black block text-xl font-semibold">Bienvenido Director, <?php echo $_SESSION['nombre'] ?></span>
                    <div class="flex gap-4 mt-4"> <!-- Se agregó mt-4 para bajar los botones -->
                        <!-- Botón Ver Dispositivos Faltantes -->
                        <a href="vistaDispositivosFaltantes.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-medium rounded-lg px-5 py-2.5 transition duration-300">
                            Ver Dispositivos Faltantes
                        </a>
                        <!-- Botón Cotización dispositivos Faltantes -->
                        <a href="CotizacionesDIpositivos.php" class="bg-yellow-500 hover:bg-yellow-600 text-black font-medium rounded-lg px-5 py-2.5 transition duration-300">
                            Cotización dispositivos Faltantes
                        </a>
                        <!-- Botón Dispositivos Utilizados en Proyectos -->
                        <a href="dispositivosutilizados.php" class="bg-black hover:bg-gray-800 text-white font-medium rounded-lg px-5 py-2.5 transition duration-300">
                            Dispositivos Utilizados en Proyectos
                        </a>
                    </div>
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

                <!-- Contenedor con scroll vertical propio -->
                <div class="rounded-lg shadow-lg bg-white"
                     style="border: 1px solid #ddd; max-height: 400px; overflow-y: auto;">
                    <table class="table-auto w-full text-left text-sm text-gray-700 min-w-[900px]">
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
                                    <td id="estado-<?php echo $solicitud['id_solicitud']; ?>" class="py-2 px-4">
                                        <?php echo htmlspecialchars($solicitud['estado']); ?>
                                    </td>
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

                                                <div class="flex flex-col md:flex-row w-full p-2 space-y-4 md:space-y-0 md:space-x-8 shadow-lg">
                                                    <section class="flex flex-col border border-1 w-full md:w-1/4">
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
                                                            onclick="showDispositivos(
                                                                'info-display-<?php echo $solicitud['id_solicitud']; ?>',
                                                                '<?php echo str_replace("'", "\\'", $solicitud['solicitudes_dispositivos']); ?>',
                                                                <?php echo htmlspecialchars(json_encode($dispositivosFaltantesPorSolicitud[$solicitud['id_solicitud']] ?? []), ENT_QUOTES, 'UTF-8'); ?>
                                                            )"
                                                            class="text-black px-4 py-2 rounded-lg text-left shadow">
                                                            Dispositivos
                                                        </button>
                                                    </section>

                                                    <section class="flex-1 bg-white h-auto w-full">
                                                        <div id="info-display-<?php echo $solicitud['id_solicitud']; ?>"
                                                            class="flex-col mt-4 text-gray-800 p-4 bg-gray-100 rounded-lg w-full h-auto flex justify-center flex-wrap overflow-y-auto"
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

                                                            // Usar un Set para evitar RUTs duplicados
                                                            const rutsVistos = new Set();
                                                            const participantesUnicos = [];

                                                            participantes.forEach(participante => {
                                                                // Extraer los datos del formato: nombre (rut, carrera, rol, tipo)
                                                                const match = participante.match(/(.+?) \((.+?), (.+?), (.+?), (.+?)\)/);
                                                                if (match) {
                                                                    const rut = match[2].trim();
                                                                    if (!rutsVistos.has(rut)) {
                                                                        rutsVistos.add(rut);
                                                                        participantesUnicos.push(match);
                                                                    }
                                                                }
                                                            });

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

                                                            participantesUnicos.forEach(match => {
                                                                const [_, nombre, rut, carrera, rol, tipo] = match;
                                                                tableHTML += `
                                                                    <tr class="border-b hover:bg-gray-50">
                                                                        <td class="py-2 px-3 text-sm">${nombre}</td>
                                                                        <td class="py-2 px-3 text-sm">${rut}</td>
                                                                        <td class="py-2 px-3 text-sm">${carrera}</td>
                                                                        <td class="py-2 px-3 text-sm">${rol}</td>
                                                                        <td class="py-2 px-3 text-sm">${tipo}</td>
                                                                    </tr>`;
                                                            });

                                                            tableHTML += `
                                                                    </tbody>
                                                                </table>`;

                                                            container.innerHTML = tableHTML;
                                                        }

                                                        // Función JavaScript modificada para mostrar el nombre del dispositivo
                                                        function showDispositivos(containerId, dispositivosStr, dispositivosFaltantes) {
                                                            const container = document.getElementById(containerId);
                                                            container.innerHTML = '';

                                                            // --- Títulos ---
                                                            const tituloLab = document.createElement('h3');
                                                            tituloLab.className = 'font-bold text-lg mb-3 text-gray-800 text-center';
                                                            tituloLab.textContent = 'Dispositivos del Laboratorio';

                                                            const tituloComprar = document.createElement('h3');
                                                            tituloComprar.className = 'font-bold text-lg mb-3 text-gray-800 text-center'; // Elimina mt-8 aquí
                                                            tituloComprar.textContent = 'Dispositivos a Comprar';

                                                            // --- Wrappers y tablas ---
                                                            const tablaLabWrapper = document.createElement('div');
                                                            tablaLabWrapper.style.maxHeight = '180px';
                                                            tablaLabWrapper.style.overflowY = 'auto';
                                                            tablaLabWrapper.className = 'w-full min-w-0'; // Quitamos max-w-full y overflowX

                                                            const tablaLab = document.createElement('table');
                                                            tablaLab.className = 'w-full min-w-0 bg-white border text-xs';

                                                            const theadLab = document.createElement('thead');
                                                            theadLab.innerHTML = `
                                                                <tr class="bg-gray-800 text-white">
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">ID</th>
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">Nombre Dispositivo</th>
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">Cantidad</th>
                                                                </tr>`;

                                                            const tbodyLab = document.createElement('tbody');
                                                            let hayLab = false;
                                                            if (dispositivosStr && dispositivosStr !== "null") {
                                                                const dispositivos = dispositivosStr.split(', ');
                                                                const dispositivosUnicos = {};
                                                                dispositivos.forEach(dispositivo => {
                                                                    const match = dispositivo.match(/(\d+):(.+?) \(Cantidad: (\d+)\)/);
                                                                    if (match) {
                                                                        const [_, idDispositivo, nombreDispositivo, cantidad] = match;
                                                                        if (!dispositivosUnicos[idDispositivo]) {
                                                                            dispositivosUnicos[idDispositivo] = {
                                                                                nombre: nombreDispositivo,
                                                                                cantidad: cantidad
                                                                            };
                                                                        }
                                                                        hayLab = true;
                                                                    }
                                                                });
                                                                Object.entries(dispositivosUnicos).forEach(([id, info]) => {
                                                                    const row = document.createElement('tr');
                                                                    row.className = 'border-b hover:bg-gray-50';
                                                                    row.innerHTML = `
                                                                        <td class="py-2 px-2 text-sm">${id}</td>
                                                                        <td class="py-2 px-2 text-sm">${info.nombre}</td>
                                                                        <td class="py-2 px-2 text-sm">${info.cantidad}</td>`;
                                                                    tbodyLab.appendChild(row);
                                                                });
                                                            }
                                                            if (!hayLab) {
                                                                const row = document.createElement('tr');
                                                                row.innerHTML = `
                                                                    <td colspan="3" class="py-2 px-2 text-center text-gray-500">No hay dispositivos solicitados</td>`;
                                                                tbodyLab.appendChild(row);
                                                            }
                                                            tablaLab.appendChild(theadLab);
                                                            tablaLab.appendChild(tbodyLab);
                                                            tablaLabWrapper.appendChild(tablaLab);

                                                            // --- Tabla Dispositivos a Comprar (Gris) ---
                                                            const tablaComprarWrapper = document.createElement('div');
                                                            tablaComprarWrapper.style.maxHeight = '180px';
                                                            tablaComprarWrapper.style.overflowY = 'auto';
                                                            tablaComprarWrapper.className = 'w-full min-w-0'; // Elimina mt-8 aquí

                                                            const tablaComprar = document.createElement('table');
                                                            tablaComprar.className = 'w-full min-w-0 bg-gray-200 border text-xs';

                                                            const theadComprar = document.createElement('thead');
                                                            theadComprar.innerHTML = `
                                                                <tr class="bg-gray-500 text-white">
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">ID</th>
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">Nombre Dispositivo</th>
                                                                    <th class="py-2 px-2 text-left text-xs font-medium uppercase">Cantidad Faltante</th>
                                                                </tr>`;

                                                            const tbodyComprar = document.createElement('tbody');
                                                            if (dispositivosFaltantes && dispositivosFaltantes.length > 0) {
                                                                // Filtrar duplicados por id
                                                                const idsVistos = new Set();
                                                                dispositivosFaltantes.forEach(faltante => {
                                                                    if (!idsVistos.has(faltante.id)) {
                                                                        idsVistos.add(faltante.id);
                                                                        const row = document.createElement('tr');
                                                                        row.className = 'border-b';
                                                                        row.innerHTML = `
                                                                            <td class="py-2 px-2 text-sm">${faltante.id}</td>
                                                                            <td class="py-2 px-2 text-sm">${faltante.nombre}</td>
                                                                            <td class="py-2 px-2 text-sm">${faltante.cantidad}</td>`;
                                                                        tbodyComprar.appendChild(row);
                                                                    }
                                                                });
                                                            } else {
                                                                const row = document.createElement('tr');
                                                                row.innerHTML = `
                                                                    <td colspan="3" class="py-2 px-2 text-center text-gray-500">No hay dispositivos faltantes para esta solicitud.</td>`;
                                                                tbodyComprar.appendChild(row);
                                                            }
                                                            tablaComprar.appendChild(theadComprar);
                                                            tablaComprar.appendChild(tbodyComprar);
                                                            tablaComprarWrapper.appendChild(tablaComprar);

                                                            // --- Contenedor flex para mostrar tablas una al lado de la otra en desktop ---
                                                            const tablasFlex = document.createElement('div');
                                                            tablasFlex.className = 'flex flex-col md:flex-row gap-4 w-full overflow-x-hidden min-w-0';

                                                            // Cada tabla ocupa el 50% en desktop, 100% en móvil
                                                            const tablaLabContainer = document.createElement('div');
                                                            tablaLabContainer.className = 'w-full md:w-1/2 flex flex-col items-center';
                                                            tablaLabContainer.appendChild(tituloLab);
                                                            tablaLabContainer.appendChild(tablaLabWrapper);

                                                            const tablaComprarContainer = document.createElement('div');
                                                            tablaComprarContainer.className = 'w-full md:w-1/2 flex flex-col items-center';
                                                            tablaComprarContainer.appendChild(tituloComprar);
                                                            tablaComprarContainer.appendChild(tablaComprarWrapper);

                                                            tablasFlex.appendChild(tablaLabContainer);
                                                            tablasFlex.appendChild(tablaComprarContainer);

                                                            container.appendChild(tablasFlex);
                                                        }
                                                    </script>
                                                </div>
                                                </section>

                                                <section class="flex-1 bg-white h-auto">
                                                    <div id="info-display-<?php echo $solicitud['id_solicitud']; ?>"
                                                        class="flex-col mt-4 text-gray-800 p-4 bg-gray-100 rounded-lg w-auto h-auto flex justify-center flex-wrap overflow-y-auto"
                                                        style="max-height: 300px; word-break: break-word; white-space: pre-wrap;">
                                                    </div>
                                                </section>

                                                <form id="form-estado-<?php echo $solicitud['id_solicitud']; ?>">
                                                    <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                                                    <input type="hidden" name="accion" id="accion-<?php echo $solicitud['id_solicitud']; ?>" value="">
                                                    <input type="hidden" name="acotaciones" id="acotaciones-<?php echo $solicitud['id_solicitud']; ?>" value="">
                                                    <div class="flex gap-4 mt-16"> <!-- Cambia mt-4 por mt-16 para bajar los botones -->
                                                        <!-- Botones con valores correctos -->
                                                        <button type="button" onclick="abrirModalRetro(<?php echo $solicitud['id_solicitud']; ?>, 'Aprobado')" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg w-full">Aprobar</button>
                                                        <button type="button" onclick="abrirModalRetro(<?php echo $solicitud['id_solicitud']; ?>, 'Rechazado')" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg w-full">Rechazar</button>
                                                        <button type="button" onclick="abrirModalRetro(<?php echo $solicitud['id_solicitud']; ?>, 'Pendiente')" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded-lg w-full">Pendiente</button>
                                                    </div>
                                                </form>

                                                <!-- Modal de retroalimentación -->
                                                <div id="modal-retro-<?php echo $solicitud['id_solicitud']; ?>" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-40">
                                                    <div class="bg-white rounded-lg p-6 w-[90vw] max-w-md shadow-lg">
                                                        <h3 class="text-xl font-semibold mb-4">Ingrese retroalimentación</h3>
                                                        <form method="POST" action="">
                                                            <input type="hidden" name="id_solicitud" value="<?php echo $solicitud['id_solicitud']; ?>">
                                                            <textarea id="textarea-retro-<?php echo $solicitud['id_solicitud']; ?>" name="acotaciones" class="w-full p-2 border rounded-lg" rows="4" placeholder="Escribe tu retroalimentación..."></textarea>
                                                            <div class="flex justify-end gap-2 mt-4">
                                                                <button type="button" onclick="cerrarModalRetro(<?php echo $solicitud['id_solicitud']; ?>)" class="bg-gray-300 hover:bg-gray-400 text-black rounded-lg px-4 py-2">Cancelar</button>
                                                                <button type="button" onclick="enviarEstadoConRetro(<?php echo $solicitud['id_solicitud']; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-4 py-2">Guardar</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>

                                                <script>
                                                    // filepath: c:\xampp\htdocs\proyectolia\final1.0\views\pantallaDirector.php
                                                    let accionSeleccionada = {};

                                                    function abrirModalRetro(id, accion) {
                                                        accionSeleccionada[id] = accion;
                                                        document.getElementById('modal-retro-' + id).classList.remove('hidden');
                                                    }

                                                    function cerrarModalRetro(id) {
                                                        document.getElementById('modal-retro-' + id).classList.add('hidden');
                                                    }

                                                    function enviarEstadoConRetro(id) {
                                                        const retro = document.getElementById('textarea-retro-' + id).value.trim();
                                                        if (!retro) {
                                                            alert('Por favor, ingrese una retroalimentación.');
                                                            return;
                                                        }
                                                        // Setear valores en el formulario
                                                        document.getElementById('accion-' + id).value = accionSeleccionada[id];
                                                        document.getElementById('acotaciones-' + id).value = retro;

                                                        // Enviar por AJAX
                                                        const form = document.getElementById('form-estado-' + id);
                                                        const formData = new FormData(form);

                                                        fetch('', {
                                                                method: 'POST',
                                                                body: formData,
                                                            })
                                                            .then(response => response.text())
                                                            .then(data => {
                                                                // Actualizar el estado en la página
                                                                document.querySelector(`#estado-${id}`).textContent = accionSeleccionada[id];
                                                                cerrarModalRetro(id);
                                                                // Opcional: mostrar mensaje de éxito
                                                                // alert('Estado y retroalimentación guardados');
                                                            })
                                                            .catch(error => {
                                                                console.error('Error al actualizar el estado:', error);
                                                            });
                                                    }
                                                </script>

                                                <script>
                                                    function abrirModalRetro(id, accion) {
                                                        accionSeleccionada[id] = accion;
                                                        document.getElementById('modal-retro-' + id).classList.remove('hidden');
                                                    }

                                                    function cerrarModalRetro(id) {
                                                        document.getElementById('modal-retro-' + id).classList.add('hidden');
                                                    }

                                                    function cerrarVisualizacion(id) {
                                                        // Cierra el modal de visualización principal
                                                        document.getElementById('authentication-modal-' + id).classList.add('hidden');
                                                    }

                                                    function enviarEstadoConRetro(id) {
                                                        const retro = document.getElementById('textarea-retro-' + id).value.trim();
                                                        if (!retro) {
                                                            alert('Por favor, ingrese una retroalimentación.');
                                                            return;
                                                        }
                                                        // Setear valores en el formulario
                                                        document.getElementById('accion-' + id).value = accionSeleccionada[id];
                                                        document.getElementById('acotaciones-' + id).value = retro;

                                                        // Enviar por AJAX
                                                        const form = document.getElementById('form-estado-' + id);
                                                        const formData = new FormData(form);

                                                        fetch('', {
                                                                method: 'POST',
                                                                body: formData,
                                                            })
                                                            .then(response => response.text())
                                                            .then(data => {
                                                                // Actualizar el estado en la página
                                                                document.querySelector(`#estado-${id}`).textContent = accionSeleccionada[id];
                                                                cerrarModalRetro(id);

                                                                // Mostrar mensaje personalizado según la acción
                                                                let mensaje = '';
                                                                if (accionSeleccionada[id] === 'Aprobar') {
                                                                    mensaje = 'La solicitud ha sido aprobada.\n\nRetroalimentación: ' + retro;
                                                                } else if (accionSeleccionada[id] === 'Rechazar') {
                                                                    mensaje = 'La solicitud ha sido rechazada.\n\nRetroalimentación: ' + retro;
                                                                } else if (accionSeleccionada[id] === 'Pendiente') {
                                                                    mensaje = 'La solicitud ha quedado pendiente.\n\nRetroalimentación: ' + retro;
                                                                }
                                                                alert(mensaje);

                                                                // Cerrar la visualización principal
                                                                cerrarVisualizacion(id);
                                                            })
                                                            .catch(error => {
                                                                console.error('Error al actualizar el estado:', error);
                                                            });
                                                    }
                                                </script>

                                                <?php
                                                // Procesar el formulario para agregar acotaciones (conservar esta lógica)
                                                if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_solicitud']) && isset($_POST['acotaciones'])) {
                                                    $id_solicitud = $_POST['id_solicitud'];
                                                    $acotaciones = $_POST['acotaciones'];

                                                    // Validar que la acotación no esté vacía
                                                    if (!empty($acotaciones)) {
                                                        // Actualizar la base de datos
                                                        $sql = "UPDATE solicitudes SET acotaciones = ? WHERE id_solicitud = ?";
                                                        $stmt = $conn->prepare($sql);
                                                        $stmt->bind_param("si", $acotaciones, $id_solicitud);

                                                        $stmt->execute();

                                                        $stmt->close();
                                                    } else {
                                                        echo "La acotación no puede estar vacía.";
                                                    }
                                                }
                                                ?>
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

    <!-- Agrega este modal al final del body -->
    <div id="mensaje-estado-modal" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black bg-opacity-40">
        <div class="bg-white rounded-lg p-8 max-w-md w-full shadow-lg flex flex-col items-center">
            <svg id="icono-estado" class="w-16 h-16 mb-4" fill="none" viewBox="0 0 24 24"></svg>
            <h3 id="titulo-estado" class="text-2xl font-bold mb-2 text-center"></h3>
            <p id="texto-estado" class="text-gray-700 text-center mb-6"></p>
            <button onclick="cerrarMensajeEstado()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-lg px-6 py-2 font-semibold">Cerrar</button>
        </div>
    </div>

    <script>
    function abrirModalRetro(id, accion) {
        accionSeleccionada[id] = accion;
        document.getElementById('modal-retro-' + id).classList.remove('hidden');
    }

    function cerrarModalRetro(id) {
        document.getElementById('modal-retro-' + id).classList.add('hidden');
    }

    function cerrarVisualizacion(id) {
        const modal = document.getElementById('authentication-modal-' + id);
        if (modal) modal.classList.add('hidden');
    }

    function mostrarMensajeEstado(titulo, texto, tipo) {
        const icono = document.getElementById('icono-estado');
        if (tipo === 'Aprobado') {
            icono.innerHTML = `<circle cx="12" cy="12" r="10" fill="#22c55e"/><path d="M8 12l2 2l4-4" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`;
        } else if (tipo === 'Rechazado') {
            icono.innerHTML = `<circle cx="12" cy="12" r="10" fill="#ef4444"/><path d="M15 9l-6 6M9 9l6 6" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`;
        } else {
            icono.innerHTML = `<circle cx="12" cy="12" r="10" fill="#f59e42"/><path d="M12 8v4m0 4h.01" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`;
        }
        document.getElementById('titulo-estado').textContent = titulo;
        document.getElementById('texto-estado').textContent = texto;
        document.getElementById('mensaje-estado-modal').classList.remove('hidden');
    }

    function cerrarMensajeEstado() {
        document.getElementById('mensaje-estado-modal').classList.add('hidden');
        // Cierra la visualización principal si hay una activa
        if (window.ultimoIdVisualizado !== undefined && window.ultimoIdVisualizado !== null) {
            cerrarVisualizacion(window.ultimoIdVisualizado);
            window.ultimoIdVisualizado = null;
        }
        // Recarga la página para actualizar la tabla y evitar la pantalla gris
        location.reload();
    }

    function enviarEstadoConRetro(id) {
        const retro = document.getElementById('textarea-retro-' + id).value.trim();
        if (!retro) {
            alert('Por favor, ingrese una retroalimentación.');
            return;
        }
        document.getElementById('accion-' + id).value = accionSeleccionada[id];
        document.getElementById('acotaciones-' + id).value = retro;

        const form = document.getElementById('form-estado-' + id);
        const formData = new FormData(form);

        fetch('', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(data => {
                document.querySelector(`#estado-${id}`).textContent = accionSeleccionada[id];
                cerrarModalRetro(id);

                window.ultimoIdVisualizado = id;

                // Mostrar modal personalizado SOLO con el estado
                let titulo = '';
                let tipo = accionSeleccionada[id];
                if (tipo === 'Aprobado') {
                    titulo = 'Solicitud Aprobada';
                } else if (tipo === 'Rechazado') {
                    titulo = 'Solicitud Rechazada';
                } else {
                    titulo = 'Solicitud Pendiente';
                }
                mostrarMensajeEstado(
                    titulo,
                    '', // No mostrar retroalimentación aquí
                    tipo
                );
            })
            .catch(error => {
                console.error('Error al actualizar el estado:', error);
            });
    }
    </script>

    <style>
    #authentication-modal-<?php echo $solicitud['id_solicitud']; ?>,
    #authentication-modal-<?php echo $solicitud['id_solicitud']; ?> .relative,
    #authentication-modal-<?php echo $solicitud['id_solicitud']; ?> .flex,
    #authentication-modal-<?php echo $solicitud['id_solicitud']; ?> .w-full {
        overflow-x: hidden !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }
    </style>
</body>

</html>