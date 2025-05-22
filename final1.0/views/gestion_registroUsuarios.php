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
        $sql_aprobar = "UPDATE usuarios SET estado = 'Activo' WHERE id_usuario = ?"; // Cambiado a 'Activo'
        
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
        $sql_rechazar = "UPDATE usuarios SET estado = 'Inactivo' WHERE id_usuario = ?"; // Cambiado a 'Inactivo'
        
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

    // Procesar activación de usuario
    if (isset($_POST['activar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_activar = "UPDATE usuarios SET estado = 'Activo' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_activar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                echo "Usuario activado exitosamente.";
            } else {
                echo "Error al Activar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }

    // Procesar desactivación de usuario
    if (isset($_POST['desactivar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_desactivar = "UPDATE usuarios SET estado = 'inactivo' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_desactivar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                echo "Usuario desactivado exitosamente.";
            } else {
                echo "Error al desactivar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }
}

// Consultar usuarios
$sql = "SELECT id_usuario, nombre, apellido, email, rol, rut, estado FROM usuarios WHERE estado = 'pendiente'";
$result = $conn->query($sql);

// Verificar si hay errores en la consulta
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios Pendientes</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación superior -->
    <div style="background-color:#00796b" class="text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Registro de Usuarios Pendientes</h1>
        <div>
            <button onclick="window.location.href='pantallaAdmin.php'" class="bg-green-400 hover:bg-green-500 text-black font-semibold py-2 px-4 rounded transition duration-300 mr-2">
                Volver al Panel
            </button>
        </div>
    </div>

    <div class="p-8">
        <!-- Mostrar mensajes de éxito o error -->
        <?php echo $mensaje; ?>
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-[#00796b] mb-4">Usuarios Solicitando Acceso al Sistema</h2>
            
            <?php if ($result->num_rows > 0): ?>
                <table id="tablaUsuarios" class="w-full border-collapse">
                    <thead>
                        <tr class="bg-[#00796b] text-black">
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
                                <tr class="border-b even:bg-gray-50 hover:bg-gray-100">
                                    <td class="px-4 py-2"><?php echo $row['id_usuario']; ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['apellido']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['rol']); ?></td>
                                    <td class="px-4 py-2"><?php echo htmlspecialchars($row['rut']); ?></td>
                                    <td class="px-4 py-2">
                                        <span class="bg-yellow-400 text-black px-2 py-1 rounded-full text-sm font-semibold">
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
                                <td colspan="8" class="text-center py-4">No hay usuarios pendientes.</td>
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

    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-xl border-t-8 border-[#00796b]">
            <h3 class="text-2xl font-bold mb-2 text-[#00796b]" id="modalTitulo">¿Está seguro?</h3>
            <p class="mb-6 text-[#263238]" id="modalDescripcion">Confirme la acción a realizar sobre el usuario:</p>
            <div class="mb-6" id="modalMensaje"></div>
            <div class="flex justify-end gap-2">
                <button id="btnCancelar" class="px-6 py-2 bg-gray-200 text-[#263238] font-semibold rounded hover:bg-gray-300 transition duration-300">Cancelar</button>
                <button id="btnConfirmar" class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded transition duration-300">Confirmar</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                "language": {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "No hay datos disponibles en la tabla",
                    "sInfo":           "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando 0 a 0 de 0 registros",
                    "sInfoFiltered":   "(filtrado de _MAX_ registros totales)",
                    "sSearch":         "Buscar:",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    }
                },
                "responsive": true,
                "order": [[0, "desc"]]
            });
        });
    </script>
    <script>
        let formPendiente = null;
        let btnPendiente = null;

        document.querySelectorAll('form.inline-flex button').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formPendiente = btn.closest('form');
                btnPendiente = btn;
                // Obtén los datos del usuario de la fila
                let fila = btn.closest('tr');
                let nombre = fila.children[1].innerText;
                let apellido = fila.children[2].innerText;
                let email = fila.children[3].innerText;
                let rol = fila.children[4].innerText;
                let rut = fila.children[5].innerText;
                let accion = btn.name === 'aprobar_usuario' ? 'Aprobar' : 'Rechazar';

                // Cambia el título y descripción según la acción
                document.getElementById('modalTitulo').innerText = `¿Está seguro que desea ${accion.toLowerCase()} este usuario?`;
                document.getElementById('modalDescripcion').innerText = `Por favor, confirme que desea realizar esta acción. Revise los datos del usuario:`;

                // Mensaje con los datos del usuario
                var mensaje = `
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-gray-100 rounded p-3">
                            <span class="block text-xs text-gray-500 font-semibold">Nombre</span>
                            <span class="block text-base text-[#263238] font-bold">${nombre}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3">
                            <span class="block text-xs text-gray-500 font-semibold">Apellido</span>
                            <span class="block text-base text-[#263238] font-bold">${apellido}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3 col-span-2">
                            <span class="block text-xs text-gray-500 font-semibold">Email</span>
                            <span class="block text-base text-[#263238] font-bold">${email}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3">
                            <span class="block text-xs text-gray-500 font-semibold">RUT</span>
                            <span class="block text-base text-[#263238] font-bold">${rut}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3">
                            <span class="block text-xs text-gray-500 font-semibold">Rol</span>
                            <span class="block text-base text-[#263238] font-bold">${rol}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3 col-span-2">
                            <span class="block text-xs text-gray-500 font-semibold">Acción</span>
                            <span class="block text-base font-bold" style="color:${accion === 'Aprobar' ? '#4CAF50' : '#ef4444'}">${accion}</span>
                        </div>
                    </div>
                `;
                document.getElementById('modalMensaje').innerHTML = mensaje;
                document.getElementById('modalConfirmacion').classList.remove('hidden');
            });
        });

        document.getElementById('btnCancelar').onclick = function() {
            document.getElementById('modalConfirmacion').classList.add('hidden');
            formPendiente = null;
            btnPendiente = null;
        };

        document.getElementById('btnConfirmar').onclick = function() {
            if (formPendiente && btnPendiente) {
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = btnPendiente.name;
                formPendiente.appendChild(input);
                formPendiente.submit();
            }
            document.getElementById('modalConfirmacion').classList.add('hidden');
        };
    </script>
</body>
</html>
<?php $conn->close(); // Cerrar la conexión a la base de datos ?>