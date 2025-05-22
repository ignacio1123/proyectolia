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
    <style>
    /* Diseño de tabla similar a vistaDispositivosFaltantes.php */
    .dataTables_wrapper {
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 15px;
        margin-top: 10px;
    }
    table.dataTable thead th {
        background-color: #f9f9f9;
        border-bottom: 1px solid #ddd;
        padding: 10px 18px;
        text-align: left;
        font-weight: 600;
        color: #333;
    }
    table.dataTable tbody td {
        padding: 12px 18px;
        border-bottom: 1px solid #f0f0f0;
        color: #444;
    }
    .dataTables_filter {
        margin-bottom: 15px;
        text-align: right;
        font-weight: normal;
    }
    .dataTables_filter input {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 6px 10px;
        margin-left: 8px;
    }
    </style>
    <style>
    @media print {
        body, html {
            background: #fff !important;
        }
        body * {
            visibility: hidden !important;
        }
        .print-area, .print-area * {
            visibility: visible !important;
        }
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100vw;
            margin: 0;
            padding: 0;
            background: #fff !important;
        }
        .print-area .titulo-impresion {
            text-align: center !important;
            font-size: 24px !important;
            font-weight: bold !important;
            color: #1e293b !important;
            margin-top: 30px !important;
            margin-bottom: 5px !important;
        }
        .print-area .subtitulo-impresion {
            text-align: center !important;
            margin-bottom: 20px !important;
            font-size: 15px !important;
            font-weight: normal !important;
        }
        .print-area table {
            width: 90% !important;
            border-collapse: collapse !important;
            font-size: 15px !important;
            margin: 0 auto !important;
        }
        .print-area th, .print-area td {
            border: 1px solid #222 !important;
            padding: 6px 12px !important;
            text-align: left !important;
        }
        .print-area th {
            background: #f5f5f5 !important;
            color: #222 !important;
            font-weight: normal !important;
        }
        .dt-buttons, .dataTables_filter, .dataTables_info, .dataTables_paginate, .dataTables_length, .bg-black, .volver-panel {
            display: none !important;
        }
    }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación superior -->
    <div class="bg-[#00796b] text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Dispositivos Utilizados</h1>
        <div>
            <a href="/proyectolia/final1.0/views/pantallaDirector.php" class="bg-[#388E3C] hover:bg-[#2e7031] text-white font-semibold py-2 px-4 rounded transition duration-300">
                Volver al Panel
            </a>
        </div>
    </div>
    <!-- Contenedor principal -->
    <div class="p-8 print-area">
        <h2 class="titulo-impresion text-xl font-semibold text-[#00796b] mb-4">Listado de Dispositivos Utilizados en Proyectos</h2>
        <div class="subtitulo-impresion text-[#263238] mb-4">
            Dispositivos utilizados por los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada.
        </div>
        <!-- Tabla de dispositivos solicitados -->
        <div class="bg-white rounded-lg shadow mt-12 p-4">
            <table id="tablaDispositivosSolicitados" class="min-w-full divide-y divide-gray-200" style="width:100%">
                <thead class="bg-[#00796b]">
                    <tr>
                        <th class="text-white">Numero de Solicitud</th>
                        <th class="text-white">Nombre de Proyecto</th>
                        <th class="text-white">#</th>
                        <th class="text-white">Nombre del Dispositivo</th>
                        <th class="text-white">Cantidad Solicitada</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="even:bg-[#E0F2F1] text-black">
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
    </div>
    <script>
        $(document).ready(function() {
            var tabla = $('#tablaDispositivosSolicitados').DataTable({
                dom: 'Bfrtip',
                paging: false,
                lengthChange: false,
                info: true,
                scrollY: '350px',
                scrollCollapse: true,
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: 'Descargar XLSX',
                        className: 'bg-[#4CAF50] hover:bg-[#43A047] text-white font-semibold py-2 px-4 rounded mr-2',
                        title: 'Dispositivos Utilizados',
                        messageTop: 'Dispositivos utilizados por los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada. '
                    },
                    {
                        extend: 'pdfHtml5',
                        text: 'Descargar PDF',
                        className: 'bg-[#388E3C] hover:bg-[#2e7031] text-white font-semibold py-2 px-4 rounded mr-2',
                        title: 'Listado de Dispositivos Utilizados en Proyectos',
                        messageTop: 'Dispositivos utilizados por los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada.',
                        orientation: 'portrait',
                        pageSize: 'A4',
                        customize: function (doc) {
                            doc.styles.title = {
                                fontSize: 16,
                                bold: true,
                                alignment: 'center',
                                color: '#00796b',
                                margin: [0, 0, 0, 10]
                            };
                            if (doc.content[1] && doc.content[1].text) {
                                doc.content[1].fontSize = 11;
                                doc.content[1].margin = [0, 0, 0, 10];
                                doc.content[1].alignment = 'center';
                            }
                            doc.styles.tableHeader = {
                                bold: true,
                                fontSize: 10,
                                color: 'white',
                                fillColor: '#00796b',
                                alignment: 'center'
                            };
                            doc.content[2].table.widths = ['12%', '22%', '8%', '32%', '10%'];
                            doc.styles.tableBodyEven = { fontSize: 10 };
                            doc.styles.tableBodyOdd = { fontSize: 10 };
                            var body = doc.content[2].table.body;
                            for (var i = 1; i < body.length; i++) {
                                if (i % 2 === 0) {
                                    for (var j = 0; j < body[i].length; j++) {
                                        body[i][j].fillColor = '#E0F2F1';
                                    }
                                } else {
                                    for (var j = 0; j < body[i].length; j++) {
                                        body[i][j].fillColor = '#ffffff';
                                    }
                                }
                            }
                        }
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
                        "first": "",
                        "last": "",
                        "next": "",
                        "previous": ""
                    }
                }
            });
            $('#btnImprimirPDF').on('click', function() {
                tabla.button('.buttons-pdf').trigger();
            });
        });
    </script>
</body>
</html>