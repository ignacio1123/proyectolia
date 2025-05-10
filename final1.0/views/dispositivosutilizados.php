<?php
// Iniciar sesión y conexión a la base de datos
session_start();
require_once 'db_connection.php';

// Consulta para obtener los dispositivos solicitados, nombre del proyecto y cantidad
$query = "
    SELECT 
        sd.id_solicitud, 
        s.nombre_proyecto,
        sd.id_dispositivo, 
        d.nombre_dispositivo, 
        sd.cantidad AS cantidad_solicitada
    FROM solicitud_dispositivos sd
    INNER JOIN dispositivos d ON sd.id_dispositivo = d.id_dispositivo
    INNER JOIN solicitudes s ON sd.id_solicitud = s.id_solicitud
";
$result = $conn->query($query);

if (!$result) {
    die('Error al ejecutar la consulta: ' . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispositivos Utilizados</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación superior -->
    <div class="bg-black text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Dispositivos Utilizados</h1>
        <div>
            <a href="/proyectolia/final1.0/views/pantallaDirector.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                Volver al Panel
            </a>
        </div>
    </div>
    <!-- Tabla de dispositivos solicitados -->
    <div class="bg-white rounded-lg shadow overflow-x-auto mt-12 p-4">
        <table id="tablaDispositivosSolicitados" class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th>Numero de Solicitud</th>
                    <th>Nombre de Proyecto</th>
                    <th>#</th>
                    <th>Nombre del Dispositivo</th>
                    <th>Cantidad Solicitada</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_dispositivo']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_dispositivo']); ?></td>
                        <td><?php echo htmlspecialchars($row['cantidad_solicitada']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#tablaDispositivosSolicitados').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar XLSX',
                        className: 'bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded mr-2',
                        title: 'Dispositivos Utilizados',
                        messageTop: 'Dispositivos utilizados por los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada. '
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Descargar PDF',
                        className: 'bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded',
                        title: 'Dispositivos Utilizados',
                        messageTop: 'Dispositivos utilizados por los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada. '
                    }
                ],
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
                }
            });
        });
    </script>
</body>
</html>