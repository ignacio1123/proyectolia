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
        s.nombre_proyecto 
    FROM dispositivos_faltante df
    LEFT JOIN solicitudes s ON df.id_solicitud = s.id_solicitud
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
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 20px auto;
            max-width: 80%;
            text-align: center;
        }
        table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #FFD700; /* Changed to darker gold color */
            color: black;
        }
        .btn {
            background-color: #FFD700; /* Changed to match header */
            color: black;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-block;
            margin: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div style="text-align: left; margin-left: 20px;">
            <a href="pantallaDirector.php" class="btn">Volver al Panel</a>
            <button id="downloadPDF" class="btn">Descargar PDF</button>
            <button id="downloadXLSX" class="btn">Descargar XLSX</button>
        </div>
        <h1>Dispositivos Faltantes</h1>
        <table id="tablaDispositivosFaltantes">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre del Dispositivo</th>
                    <th>Cantidad Faltante</th>
                    <th>Numero de Solicitud</th>
                    <th>Nombre de Proyecto</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_dispositivo']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_dispositivo']); ?></td>
                        <td><?php echo htmlspecialchars($row['cantidad_dispositivo']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_solicitud']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre_proyecto']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            $('#tablaDispositivosFaltantes').DataTable({
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

        // Descargar como PDF
        document.getElementById('downloadPDF').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            doc.text("Dispositivos Faltantes", 14, 10);
            doc.autoTable({
                html: '#tablaDispositivosFaltantes',
                startY: 20,
                styles: { fontSize: 10 },
                headStyles: { fillColor: [255, 255, 0], textColor: [0, 0, 0] } // Fondo amarillo, texto negro
            });

            doc.save('Dispositivos_Faltantes.pdf');
        });

        // Descargar como XLSX
        document.getElementById('downloadXLSX').addEventListener('click', function() {
            const table = document.getElementById('tablaDispositivosFaltantes');
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Dispositivos Faltantes" });
            XLSX.writeFile(workbook, 'Dispositivos_Faltantes.xlsx');
        });
    </script>
</body>
</html>