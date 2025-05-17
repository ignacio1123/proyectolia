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
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center">
    <div class="w-full max-w-6xl mt-10 bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold text-center mb-6 text-gray-800">Cotización de Dispositivos Faltantes</h1>
        <!-- Formulario de filtrado -->
        <form method="get" class="mb-6 flex flex-wrap gap-4 justify-center">
            <select name="nombre_proyecto" class="px-3 py-2 border rounded">
                <option value="">Nombre Proyecto</option>
                <?php foreach ($proyectos as $proy): ?>
                    <option value="<?php echo htmlspecialchars($proy); ?>" <?php if($nombre_proyecto==$proy) echo "selected"; ?>>
                        <?php echo htmlspecialchars($proy); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="proveedor" class="px-3 py-2 border rounded">
                <option value="">Proveedor</option>
                <?php foreach ($proveedores as $prov): ?>
                    <option value="<?php echo htmlspecialchars($prov); ?>" <?php if($proveedor==$prov) echo "selected"; ?>>
                        <?php echo htmlspecialchars($prov); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="ubicacion" class="px-3 py-2 border rounded">
                <option value="">Tipo de Estado</option>
                <option value="Por Comprar" <?php if($ubicacion=="Por Comprar") echo "selected"; ?>>Por Comprar</option>
                <option value="Comprado" <?php if($ubicacion=="Comprado") echo "selected"; ?>>Comprado</option>
                <option value="En LIA Entregado" <?php if($ubicacion=="En LIA Entregado") echo "selected"; ?>>En LIA Entregado</option>
                <option value="Rechazado" <?php if($ubicacion=="Rechazado") echo "selected"; ?>>Rechazado</option>
            </select>
            <select name="lider_proyecto" class="px-3 py-2 border rounded">
                <option value="">Líder de Proyecto</option>
                <?php foreach ($lideres as $lider): ?>
                    <option value="<?php echo htmlspecialchars($lider); ?>" <?php if($lider_proyecto==$lider) echo "selected"; ?>>
                        <?php echo htmlspecialchars($lider); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">Filtrar</button>
            <a href="CotizacionesDIpositivos.php" class="bg-gray-300 px-4 py-2 rounded hover:bg-gray-400">Limpiar</a>
        </form>
        <div class="overflow-x-auto" style="max-height: 500px; overflow-y:auto;">
            <table class="min-w-full table-auto border-collapse bg-gray-50">
                <thead class="bg-black/90">
                    <tr>
                        <th class="px-4 py-2 text-white"># Solicitud</th>
                        <th class="px-4 py-2 text-white">Nombre Proyecto</th>
                        <th class="px-4 py-2 text-white">Líder Proyecto</th>
                        <th class="px-4 py-2 text-white">ID Dispositivo</th>
                        <th class="px-4 py-2 text-white">Nombre Dispositivo</th>
                        <th class="px-4 py-2 text-white">Cantidad Faltante</th>
                        <th class="px-4 py-2 text-white">Proveedor</th>
                        <th class="px-4 py-2 text-white">Estado</th>
                        <th class="px-4 py-2 text-white">URL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['id_solicitud']); ?></td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                                <td class="px-4 py-2 text-center">
                                    <?php echo htmlspecialchars($row['nombre_lider'] . ' ' . $row['apellido_lider']); ?>
                                </td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['id_dispositivo']); ?></td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['nombre_dispositivo']); ?></td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['cantidad_dispositivo']); ?></td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['Proveedor']); ?></td>
                                <td class="px-4 py-2 text-center"><?php echo htmlspecialchars($row['Ubicacion']); ?></td>
                                <td class="px-4 py-2 text-center">
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
                            <td colspan="9" class="px-4 py-4 text-center text-gray-500">No hay dispositivos faltantes registrados.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-6 flex justify-center">
            <a href="pantallaDirector.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-6 rounded transition duration-300">
                Volver
            </a>
        </div>
    </div>
</body>
</html>
<?php $stmt->close(); ?>