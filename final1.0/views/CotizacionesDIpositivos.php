<?php
include 'db_connection.php';

// Obtener filtros desde GET
$nombre_proyecto = isset($_GET['nombre_proyecto']) ? $_GET['nombre_proyecto'] : '';
$id_dispositivo = isset($_GET['id_dispositivo']) ? $_GET['id_dispositivo'] : '';
$nombre_dispositivo = isset($_GET['nombre_dispositivo']) ? $_GET['nombre_dispositivo'] : '';
$proveedor = isset($_GET['proveedor']) ? $_GET['proveedor'] : '';
$ubicacion = isset($_GET['ubicacion']) ? $_GET['ubicacion'] : '';
$lider_proyecto = isset($_GET['lider_proyecto']) ? $_GET['lider_proyecto'] : '';

// Consulta principal con JOIN a usuarios
$sql = "SELECT 
            df.id_solicitud, 
            s.nombre_proyecto, 
            s.id_usuario, 
            u.nombre AS nombre_lider, 
            u.apellido AS apellido_lider, 
            df.id_dispositivo, 
            df.nombre_dispositivo, 
            df.cantidad_dispositivo, 
            df.Proveedor, 
            df.Ubicacion, 
            df.LinkDispositivoFaltante
        FROM dispositivos_faltante df
        LEFT JOIN solicitudes s ON df.id_solicitud = s.id_solicitud
        LEFT JOIN usuarios u ON s.id_usuario = u.id_usuario
        WHERE 1=1";
$params = [];
$types = "";

if ($nombre_proyecto !== "") {
    $sql .= " AND s.nombre_proyecto LIKE ?";
    $params[] = "%$nombre_proyecto%";
    $types .= "s";
}
if ($id_dispositivo !== "") {
    $sql .= " AND df.id_dispositivo = ?";
    $params[] = $id_dispositivo;
    $types .= "i";
}
if ($nombre_dispositivo !== "") {
    $sql .= " AND df.nombre_dispositivo LIKE ?";
    $params[] = "%$nombre_dispositivo%";
    $types .= "s";
}
if ($proveedor !== "") {
    $sql .= " AND df.Proveedor LIKE ?";
    $params[] = "%$proveedor%";
    $types .= "s";
}
if ($ubicacion !== "") {
    $sql .= " AND df.Ubicacion = ?";
    $params[] = $ubicacion;
    $types .= "s";
}
if ($lider_proyecto !== "") {
    $sql .= " AND CONCAT(u.nombre, ' ', u.apellido) LIKE ?";
    $params[] = "%$lider_proyecto%";
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Obtener proveedores únicos para el filtro y el formulario
$proveedores = [];
$proveedorQuery = $conn->query("SELECT DISTINCT Proveedor FROM dispositivos_faltante WHERE Proveedor IS NOT NULL AND Proveedor != ''");
while ($rowProv = $proveedorQuery->fetch_assoc()) {
    $proveedores[] = $rowProv['Proveedor'];
}

// Obtener proyectos únicos para el filtro
$proyectos = [];
$proyectoQuery = $conn->query("SELECT DISTINCT nombre_proyecto FROM solicitudes WHERE nombre_proyecto IS NOT NULL AND nombre_proyecto != ''");
while ($rowProyecto = $proyectoQuery->fetch_assoc()) {
    $proyectos[] = $rowProyecto['nombre_proyecto'];
}

// Obtener líderes únicos para el filtro
$lideres = [];
$liderQuery = $conn->query("SELECT DISTINCT u.nombre, u.apellido FROM solicitudes s LEFT JOIN usuarios u ON s.id_usuario = u.id_usuario WHERE u.nombre IS NOT NULL AND u.apellido IS NOT NULL");
while ($rowLider = $liderQuery->fetch_assoc()) {
    $nombreCompleto = $rowLider['nombre'] . ' ' . $rowLider['apellido'];
    $lideres[] = $nombreCompleto;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Cotización Dispositivos Faltantes</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <div class="w-full max-w-6xl mt-10 bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-center mb-6 text-[#00796b]">Cotización de Dispositivos Faltantes</h1>
        <!-- Formulario de filtrado -->
        <form method="get" class="mb-6 flex flex-wrap gap-4 justify-center">
            <select name="nombre_proyecto" class="px-3 py-2 border rounded bg-white text-black">
                <option value="" class="text-black">Nombre Proyecto</option>
                <?php foreach ($proyectos as $proy): ?>
                    <option value="<?php echo htmlspecialchars($proy); ?>" <?php if ($nombre_proyecto == $proy) echo "selected"; ?> class="text-black">
                        <?php echo htmlspecialchars($proy); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="proveedor" class="px-3 py-2 border rounded bg-white text-black">
                <option value="" class="text-black">Proveedor</option>
                <?php foreach ($proveedores as $prov): ?>
                    <option value="<?php echo htmlspecialchars($prov); ?>" <?php if ($proveedor == $prov) echo "selected"; ?> class="text-black">
                        <?php echo htmlspecialchars($prov); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="ubicacion" class="px-3 py-2 border rounded bg-white text-black">
                <option value="" class="text-black">Tipo de Estado</option>
                <option value="Por Comprar" <?php if ($ubicacion == "Por Comprar") echo "selected"; ?> class="text-black">Por Comprar</option>
                <option value="Comprado" <?php if ($ubicacion == "Comprado") echo "selected"; ?> class="text-black">Comprado</option>
                <option value="En LIA Entregado" <?php if ($ubicacion == "En LIA Entregado") echo "selected"; ?> class="text-black">En LIA Entregado</option>
                <option value="Rechazado" <?php if ($ubicacion == "Rechazado") echo "selected"; ?> class="text-black">Rechazado</option>
            </select>
            <select name="lider_proyecto" class="px-3 py-2 border rounded bg-white text-black">
                <option value="" class="text-black">Líder de Proyecto</option>
                <?php foreach ($lideres as $lider): ?>
                    <option value="<?php echo htmlspecialchars($lider); ?>" <?php if ($lider_proyecto == $lider) echo "selected"; ?> class="text-black">
                        <?php echo htmlspecialchars($lider); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-[#4CAF50] hover:bg-[#43A047] text-white px-4 py-2 rounded transition">Filtrar</button>
            <a href="CotizacionesDIpositivos.php" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400 text-black">Limpiar</a>
        </form>
        <div class="overflow-x-auto bg-white rounded-lg shadow mt-4">
            <table id="tablaCotizacionDispositivos" class="min-w-full">
                <thead class="bg-[#00796b]">
                    <tr>
                        <th class="text-white">Numero de Solicitud</th>
                        <th class="text-white">Nombre de Proyecto</th>
                        <th class="text-white">Líder Proyecto</th>
                        <th class="text-white">#</th>
                        <th class="text-white">Nombre del Dispositivo</th>
                        <th class="text-white">Cantidad Solicitada</th>
                        <th class="text-white">Proveedor</th>
                        <th class="text-white">Tipo de Estado</th>
                        <th class="text-white">URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="even:bg-[#E0F2F1] text-black">
                                <td><?php echo htmlspecialchars($row['id_solicitud']); ?></td>
                                <td style="max-width: 300px; white-space: normal; word-break: break-word;"><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_lider'] . ' ' . $row['apellido_lider']); ?></td>
                                <td><?php echo htmlspecialchars($row['id_dispositivo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_dispositivo']); ?></td>
                                <td><?php echo htmlspecialchars($row['cantidad_dispositivo']); ?></td>
                                <td><?php echo htmlspecialchars($row['Proveedor']); ?></td>
                                <td><?php echo htmlspecialchars($row['Ubicacion']); ?></td>
                                <td>
                                    <?php if (!empty($row['LinkDispositivoFaltante'])): ?>
                                        <a href="<?php echo htmlspecialchars($row['LinkDispositivoFaltante']); ?>" target="_blank" class="text-blue-600 underline">
                                            Ver enlace
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400">Sin enlace</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-gray-500">No hay dispositivos faltantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex justify-center">
            <a href="pantallaDirector.php"
                class="bg-[#388E3C] hover:bg-[#2e7031] text-white font-semibold py-2 px-6 rounded transition duration-300 shadow text-lg">
                Volver
            </a>
        </div>
    </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#tablaCotizacionDispositivos').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "lengthMenu": "Mostrar _MENU_ registros",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                scrollY: '400px',
                scrollCollapse: true,
                paging: true,
                ordering: true,
                info: true
            });
        });
    </script>
</body>

</html>
<?php $stmt->close(); ?>