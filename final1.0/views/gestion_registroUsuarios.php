<?php
// Configuración de la conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "proyectolab";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Mensaje de estado para mostrar al usuario
$mensaje = "";

// Manejar acciones de aprobar o rechazar usuarios
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Procesar aprobación de usuario
    if (isset($_POST['aprobar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_aprobar = "UPDATE usuarios SET estado = 'aprobado' WHERE id_usuario = ?";
        
        if ($stmt = $conn->prepare($sql_aprobar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                $mensaje = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>Usuario aprobado exitosamente.</div>";
            } else {
                $mensaje = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al aprobar usuario: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $mensaje = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error en la consulta: " . $conn->error . "</div>";
        }
    }

    // Procesar rechazo de usuario
    if (isset($_POST['rechazar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_rechazar = "UPDATE usuarios SET estado = 'rechazado' WHERE id_usuario = ?";
        
        if ($stmt = $conn->prepare($sql_rechazar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                $mensaje = "<div class='bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4'>Usuario rechazado exitosamente.</div>";
            } else {
                $mensaje = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error al rechazar usuario: " . $stmt->error . "</div>";
            }
            $stmt->close();
        } else {
            $mensaje = "<div class='bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4'>Error en la consulta: " . $conn->error . "</div>";
        }
    }
}

// Consultar usuarios en revisión
$sql = "SELECT id_usuario, nombre, apellido, email, rol, rut, estado FROM usuarios WHERE estado = 'en revision' ORDER BY id_usuario DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios Pendientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación superior -->
    <div class="bg-black text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Usuarios Pendientes de Aprobación</h1>
        <div>
            <button onclick="window.location.href='pantallaAdmin.php'" class="bg-white text-black px-3 py-1 rounded hover:bg-gray-200 transition">
                Volver al Panel
            </button>
        </div>
    </div>

    <div class="p-8">
        <!-- Mostrar mensajes de éxito o error -->
        <?php echo $mensaje; ?>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Usuarios Solicitando Acceso al Sistema</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table id="tablaUsuarios" class="w-full border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Apellido</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Rol</th>
                            <th class="px-4 py-2 text-left">RUT</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-left">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2"><?php echo $row['id_usuario']; ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['apellido']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['rol']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['rut']); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="<?php echo $row['estado'] === 'aprobado' ? 'bg-green-500' : ($row['estado'] === 'rechazado' ? 'bg-red-500' : 'bg-yellow-500'); ?> text-white px-2 py-1 rounded-full text-sm">
                                            <?php echo $row['estado']; ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <form method="POST" action="" class="inline-flex space-x-2">
                                            <input type="hidden" name="id_usuario" value="<?php echo $row['id_usuario']; ?>">
                                            <button type="submit" name="aprobar_usuario" class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 transition">
                                                Aprobar
                                            </button>
                                            <button type="submit" name="rechazar_usuario" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition">
                                                Rechazar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No hay usuarios registrados en este momento.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4">
                    No hay usuarios pendientes de aprobación en este momento.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                "responsive": true,
                "order": [[0, "desc"]]
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); // Cerrar la conexión a la base de datos ?>