<?php

$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "proyectolab";

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['eliminar_usuario'])) {
  
        $id_usuario = intval($_POST['id_usuario']); 

        $sql_delete = "DELETE FROM usuarios WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_delete)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                echo "Usuario eliminado exitosamente.";
            } else {
                echo "Error al eliminar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }
}


$sql = "SELECT id_usuario, nombre, apellido, email, rol, rut, estado FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>
<body class="bg-gray-100">

   
    <div class="bg-black text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Administrador</h1>
        <div>
            <button onclick="window.location.href='pantallaAdmin.php'" class="bg-white text-black px-2 py-1 rounded mr-2 hover:bg-gray-200">
                Volver a dispositivos
            </button>
            <button onclick="window.location.href='login.php'" class="bg-white text-black px-2 py-1 rounded hover:bg-gray-200">
                Cerrar Sesión
            </button>
        </div>
    </div>

 
    <div class="p-8">

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Gestión de Usuarios</h2>
            <table id="tablaUsuarios" class="w-full">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>RUT</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $estado_clase = $row['estado'] === 'activo' ? 'bg-green-500' : 'bg-red-500';
                            echo "<tr>
                                    <td>{$row['id_usuario']}</td>
                                    <td>{$row['nombre']}</td>
                                    <td>{$row['apellido']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['rol']}</td>
                                    <td>{$row['rut']}</td>
                                    <td><span class='$estado_clase text-white px-2 py-1 rounded'>{$row['estado']}</span></td>
                                    <td>
                                        <form method='POST' action='' class='inline'>
                                            <input type='hidden' name='id_usuario' value='{$row['id_usuario']}'>
                                            <!-- Botón Eliminar Usuario -->
                                            <button type='submit' name='eliminar_usuario' class='bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600'>
                                                Eliminar usuario
                                            </button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No hay usuarios registrados.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

  
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
  
        $(document).ready(function() {
            $('#tablaUsuarios').DataTable({
                language: {
                    "sProcessing": "Procesando...",
                    "sLengthMenu": "",
                    "sZeroRecords": "No se encontraron resultados",
                    "sEmptyTable": "Ningún dato disponible en esta tabla",
                    "sInfo": "Mostrando registros",
                    "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered": "(filtrado de un total de MAX registros)",
                    "sInfoPostFix": "",
                    "sSearch": "Buscar:",
                    "sUrl": "",
                    "sInfoThousands": ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst": "Primero",
                        "sLast": "Último",
                        "sNext": "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });
        });

       
              $(document).ready(function() {
            $('#tablaUsuarios').DataTable();
        });

    </script>

</body>
</html>