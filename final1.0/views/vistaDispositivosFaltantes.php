<?php
// Iniciar sesión y conexión a la base de datos
session_start();
require_once 'db_connection.php';

// Consulta para obtener los dispositivos faltantes junto con id_solicitud y nombre_proyecto
$query = "
    SELECT 
        df.id_dispositivo, 
        df.nombre_dispositivo, 
        df.cantidad_dispositivo, 
        s.id_solicitud, 
        s.nombre_proyecto,
        df.Ubicacion
    FROM dispositivos_faltante df
    LEFT JOIN solicitudes s ON df.id_solicitud = s.id_solicitud
    ORDER BY s.id_solicitud, df.id_dispositivo
";
$result = $conn->query($query);

if (!$result) {
    die("Error al ejecutar la consulta: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispositivos Faltantes</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Estilos personalizados para parecerse a la imagen */
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
        
        .pagination-info {
            font-size: 0.9rem;
            color: #666;
            margin-top: 15px;
        }
        
        .pagination-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 15px;
            gap: 5px;
        }
        
        .pagination-button {
            border: 1px solid #ddd;
            background-color: #fff;
            color: #333;
            padding: 5px 12px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .pagination-button.active {
            background-color: #f0f0f0;
        }
        
        .action-button {
            background-color: white;
            border: 1px solid #ccc;
            color: black;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .action-button:hover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Barra de navegación superior -->
    <div class="bg-black text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Dispositivos Faltantes</h1>
        <div>
            <a href="/proyectolia/final1.0/views/pantallaDirector.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                Volver al Panel
            </a>
        </div>
    </div>

    <!-- Contenedor principal -->
    <div class="p-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Listado de Dispositivos Faltantes</h2>
        
        <!-- Botones de acción alineados a la izquierda -->
        <div class="mb-4 flex gap-2">
            <button id="downloadXLSX" class="action-button">
                Descargar XLSX
            </button>
            <button id="downloadPDF" class="action-button">
                Descargar PDF
            </button>
            <button id="printTable" class="action-button">
                Imprimir
            </button>
        </div>

        <!-- Tabla de dispositivos faltantes -->
        <div class="bg-white rounded-lg shadow">
            <table id="tablaDispositivosFaltantes" class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th style="width: 80px; min-width: 60px; max-width: 100px; font-size: 0.95em;">Numero de Solicitud</th>
                        <th style="width: 600px; max-width: 900px;">Nombre de Proyecto</th>
                        <th>#</th>
                        <th>Nombre del Dispositivo</th>
                        <th>Cantidad Solicitada</th>
                        <th>Tipo de Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td style="width: 80px; font-size: 0.95em;"><?php echo htmlspecialchars($row['id_solicitud']); ?></td>
                            <td style="max-width: 900px; white-space: normal; word-break: break-word;">
                                <?php echo htmlspecialchars($row['nombre_proyecto']); ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['id_dispositivo']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_dispositivo']); ?></td>
                            <td><?php echo htmlspecialchars($row['cantidad_dispositivo']); ?></td>
                            <td>
                                <select class="estado-dispositivo" data-id="<?php echo $row['id_dispositivo']; ?>">
                                    <option value="Básica" <?php if($row['Ubicacion']=='Básica') echo 'selected'; ?>>Por Comprar</option>
                                    <option value="Comprado" <?php if($row['Ubicacion']=='Comprado') echo 'selected'; ?>>Comprado</option>
                                    <option value="En LIA Entregado" <?php if($row['Ubicacion']=='En LIA Entregado') echo 'selected'; ?>>En LIA Entregado</option>
                                    <option value="Rechazado" <?php if($row['Ubicacion']=='Rechazado') echo 'selected'; ?>>Rechazado</option>
                                </select>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <!-- Información de paginación y botones como en la imagen -->
            <div class="p-4">
                <div class="flex justify-between items-center">
                    <div class="pagination-info">
                        Mostrando <span id="startRecord">1</span> a <span id="endRecord">1</span> de <span id="totalRecords">1</span> registros
                    </div>
                    <div class="pagination-buttons">
                        <button id="btnAnterior" class="pagination-button">Anterior</button>
                        <button id="btnPagina" class="pagination-button active">1</button>
                        <button id="btnSiguiente" class="pagination-button">Siguiente</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estilos de impresión -->
    <style type="text/css" media="print">
        @page {
            size: auto;
            margin: 15mm;
        }
        
        body * {
            visibility: hidden;
        }
        
        #printArea, #printArea * {
            visibility: visible !important;
        }
        
        #printArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 15px;
        }
        
        #printArea table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-family: Arial, sans-serif;
        }
        
        #printArea table th, #printArea table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        
        #printArea table th {
            background-color: #ffff00 !important; /* Amarillo como en el PDF */
            color: #000;
            font-weight: bold;
        }
        
        #printHeader h2 {
            font-size: 16pt !important;
            margin-bottom: 8px !important;
            font-weight: bold !important;
        }
        
        #printHeader p {
            font-size: 11pt !important;
            margin-bottom: 20px !important;
        }
        
        /* Ocultar elementos de DataTables que no queremos imprimir */
        .dataTables_filter, .dataTables_info, .dataTables_paginate, 
        .dataTables_length, .dataTables_scroll, .no-print {
            display: none !important;
        }
        
        /* Asegurar que la impresión sea en color */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        #printArea table td, #printArea table th {
            white-space: normal !important;
            word-break: break-word !important;
        }
    </style>
    
    <!-- Div oculto que se usará para imprimir -->
    <div id="printArea" style="display: none;">
        <div id="printHeader">
            <h2 style="text-align: center; font-size: 16pt; margin-bottom: 8px;">Dispositivos Faltantes</h2>
            <p style="text-align: center; font-size: 11pt; margin-bottom: 20px;">Dispositivos Faltantes para los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada.</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 80px; font-size: 0.95em; background-color: #ffff00; color: #000;">Numero de Solicitud</th>
                    <th style="max-width: 500px; font-size: 1em; background-color: #ffff00; color: #000;">Nombre de Proyecto</th>
                    <th style="background-color: #ffff00; color: #000;">#</th>
                    <th style="background-color: #ffff00; color: #000;">Nombre del Dispositivo</th>
                    <th style="background-color: #ffff00; color: #000;">Cantidad Solicitada</th>
                    <th style="background-color: #ffff00; color: #000;">Tipo de Estado</th>
                </tr>
            </thead>
            <tbody id="printTableBody">
                <!-- Se llenará dinámicamente con JavaScript -->
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            const table = $('#tablaDispositivosFaltantes').DataTable({
                language: {
                    "decimal": "",
                    "emptyTable": "No hay datos disponibles",
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron registros",
                    "processing": "Procesando..."
                },
                dom: 'ft', // Solo tabla y filtro de búsqueda
                paging: false, // Desactivamos la paginación para mostrar todos los registros
                lengthChange: false,
                info: false, // Desactivamos el info de DataTables
                ordering: true, // Permitir ordenar
                scrollY: '500px', // Scroll vertical para tablas grandes
                scrollCollapse: true // Colapsar el scroll cuando no sea necesario
            });
            
            // Actualizar contadores de registros
            function updateRecordInfo() {
                const totalRecords = table.rows().count();
                $('#startRecord').text('1');
                $('#endRecord').text(totalRecords);
                $('#totalRecords').text(totalRecords);
                $('#btnPagina').text('1');
            }
            
            // Inicializar contadores
            updateRecordInfo();
            
            // Desactivamos botones de paginación ya que mostramos todos los registros
            $('#btnAnterior').prop('disabled', true).addClass('opacity-50');
            $('#btnSiguiente').prop('disabled', true).addClass('opacity-50');
            
            // Actualizar info cuando se filtra
            table.on('search.dt', function() {
                const filteredRecords = table.rows({search:'applied'}).count();
                $('#endRecord').text(filteredRecords);
                $('#totalRecords').text(table.rows().count());
            });
        });

        // Guardar el estado al cambiar el select
        $(document).on('change', '.estado-dispositivo', function() {
            var id = $(this).data('id');
            var estado = $(this).val();
            $.post('actualizar_estado.php', {id: id, estado: estado});
        });

        // Función para imprimir la tabla
        document.getElementById('printTable').addEventListener('click', function() {
            const table = $('#tablaDispositivosFaltantes').DataTable();
            const printTableBody = document.getElementById('printTableBody');
            printTableBody.innerHTML = '';

            // Llenar la tabla de impresión con los datos actuales (incluyendo filtrados)
            table.rows({ search: 'applied' }).every(function(rowIdx) {
                const data = this.node().children;
                const row = document.createElement('tr');
                for (let i = 0; i < data.length; i++) {
                    const cell = document.createElement('td');
                    if (i === 5) { // Columna Tipo de Estado
                        const select = data[i].querySelector('select');
                        cell.textContent = select ? select.options[select.selectedIndex].text : data[i].textContent;
                    } else {
                        cell.textContent = data[i].textContent;
                    }
                    row.appendChild(cell);
                }
                printTableBody.appendChild(row);
            });

            // Mostrar el área de impresión
            const printArea = document.getElementById('printArea');
            printArea.style.display = 'block';

            // Imprimir
            window.print();

            // Ocultar el área de impresión después de imprimir
            printArea.style.display = 'none';
        });

        // Descargar como PDF
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Mensaje personalizado centrado
            const pageWidth = doc.internal.pageSize.getWidth();
            const title = "Dispositivos Faltantes";
            const subtitle = "Dispositivos Faltantes para los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada.";

            doc.setFontSize(16);
            const titleWidth = doc.getTextWidth(title);
            doc.text(title, (pageWidth - titleWidth) / 2, 15);

            doc.setFontSize(11);
            const subtitleWidth = doc.getTextWidth(subtitle);
            doc.text(subtitle, (pageWidth - subtitleWidth) / 2, 23);

            // Construir los datos manualmente para mostrar solo el estado seleccionado
            const table = $('#tablaDispositivosFaltantes').DataTable();
            const headers = [];
            $('#tablaDispositivosFaltantes thead th').each(function() {
                headers.push($(this).text());
            });
            const body = [];
            table.rows({ search: 'applied' }).every(function(rowIdx) {
                const cells = this.node().children;
                const row = [];
                for (let i = 0; i < cells.length; i++) {
                    if (i === 5) {
                        const select = cells[i].querySelector('select');
                        row.push(select ? select.options[select.selectedIndex].text : cells[i].textContent);
                    } else {
                        row.push(cells[i].textContent);
                    }
                }
                body.push(row);
            });

            doc.autoTable({
                head: [headers],
                body: body,
                startY: 30,
                styles: { fontSize: 10 },
                headStyles: { fillColor: [255, 255, 0], textColor: [0, 0, 0] },
                columnStyles: {
                    1: { cellWidth: 80, minCellWidth: 80, maxCellWidth: 120, halign: 'left' } // 1 es la columna "Nombre de Proyecto"
                },
                // Permitir que el texto haga salto de línea
                didParseCell: function (data) {
                    data.cell.styles.cellPadding = 3;
                }
            });

            doc.save('Dispositivos_Faltantes.pdf');
        });

        // Descargar como XLSX
        document.getElementById('downloadXLSX').addEventListener('click', function() {
            const table = $('#tablaDispositivosFaltantes').DataTable();
            const wb = XLSX.utils.book_new();

            // Mensaje personalizado como filas arriba de la tabla
            const mensaje = [
                ["Dispositivos Faltantes"],
                ["Dispositivos Faltantes para los estudiantes de Santo Tomas Cede Concepción, Laboratorio de innovación Aplicada."],
                []
            ];

            // Obtener los datos de la tabla manualmente (incluyendo encabezados)
            const ws_data = [];
            mensaje.forEach(row => ws_data.push(row));
            // Encabezados
            const headers = [];
            $('#tablaDispositivosFaltantes thead th').each(function() {
                headers.push($(this).text());
            });
            ws_data.push(headers);
            // Filas
            table.rows({ search: 'applied' }).every(function(rowIdx) {
                const cells = this.node().children;
                const row = [];
                for (let i = 0; i < cells.length; i++) {
                    if (i === 5) {
                        const select = cells[i].querySelector('select');
                        row.push(select ? select.options[select.selectedIndex].text : cells[i].textContent);
                    } else {
                        row.push(cells[i].textContent);
                    }
                }
                ws_data.push(row);
            });

            // Crear hoja y archivo
            const ws = XLSX.utils.aoa_to_sheet(ws_data);
            XLSX.utils.book_append_sheet(wb, ws, "Dispositivos Faltantes");
            XLSX.writeFile(wb, 'Dispositivos_Faltantes.xlsx');
        });
    </script>
</body>
</html>