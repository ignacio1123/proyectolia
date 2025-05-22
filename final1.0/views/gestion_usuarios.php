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

    if (isset($_POST['aprobar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_aprobar = "UPDATE usuarios SET estado = 'aprobado' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_aprobar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                echo "Usuario aprobado exitosamente.";
            } else {
                echo "Error al aprobar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }

    if (isset($_POST['rechazar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_rechazar = "UPDATE usuarios SET estado = 'rechazado' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_rechazar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                echo "Usuario rechazado exitosamente.";
            } else {
                echo "Error al rechazar: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Error en la preparación de la consulta: " . $conn->error;
        }
    }

    if (isset($_POST['activar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_activar = "UPDATE usuarios SET estado = 'activo' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_activar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                header("Location: gestion_usuarios.php");
                exit;
            }
            $stmt->close();
        }
    }

    if (isset($_POST['desactivar_usuario'])) {
        $id_usuario = intval($_POST['id_usuario']);
        $sql_desactivar = "UPDATE usuarios SET estado = 'inactivo' WHERE id_usuario = ?";
        if ($stmt = $conn->prepare($sql_desactivar)) {
            $stmt->bind_param("i", $id_usuario);
            if ($stmt->execute()) {
                header("Location: gestion_usuarios.php");
                exit;
            }
            $stmt->close();
        }
    }
}


$sql = "SELECT id_usuario, nombre, apellido, email, rol, rut, estado FROM usuarios WHERE estado IN ('activo', 'inactivo')";
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
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
</head>

<body class="bg-gray-100">

    <div style="background-color:#00796b" class="text-white p-4 shadow-md flex justify-between items-center">
        <h1 class="text-2xl font-semibold">Administrador</h1>
        <div>
            <button onclick="window.location.href='pantallaAdmin.php'"
                class="bg-green-400 hover:bg-green-500 text-black font-semibold py-2 px-4 rounded transition duration-300 mr-2">
                Volver a dispositivos
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
                                        <form method='POST' action='' class='inline cambiar-estado-form'>
                                            <input type='hidden' name='id_usuario' value='{$row['id_usuario']}'>
                                            <button 
                                                type='submit' 
                                                name='activar_usuario' 
                                                class='bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600'
                                                data-nombre='{$row['nombre']}'
                                                data-apellido='{$row['apellido']}'
                                                data-rut='{$row['rut']}'
                                                data-rol='{$row['rol']}'
                                            >
                                                Activo
                                            </button>
                                            <button 
                                                type='submit' 
                                                name='desactivar_usuario' 
                                                class='bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600'
                                                data-nombre='{$row['nombre']}'
                                                data-apellido='{$row['apellido']}'
                                                data-rut='{$row['rut']}'
                                                data-rol='{$row['rol']}'
                                            >
                                                Inactivo
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

    <!-- Modal de confirmación -->
    <div id="modalConfirmacion" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50 hidden">
        <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-xl border-t-8 border-[#00796b]">
            <h3 class="text-2xl font-bold mb-2 text-[#00796b]" id="modalTitulo">¿Está seguro que desea cambiar el estado del usuario?</h3>
            <p class="mb-6 text-[#263238]" id="modalDescripcion">Por favor, confirme que desea realizar esta acción. Revise los datos del usuario:</p>
            <div class="mb-6" id="modalMensaje"></div>
            <div class="flex justify-end gap-2">
                <button id="btnCancelar" class="px-6 py-2 bg-gray-200 text-[#263238] font-semibold rounded hover:bg-gray-300 transition duration-300">Cancelar</button>
                <button id="btnConfirmar" class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded transition duration-300">Confirmar</button>
            </div>
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

    <script>
        let formPendiente = null;
        let btnPendiente = null;

        document.querySelectorAll('.cambiar-estado-form button').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                formPendiente = btn.closest('form');
                btnPendiente = btn;
                var nombre = btn.getAttribute('data-nombre');
                var apellido = btn.getAttribute('data-apellido');
                var rut = btn.getAttribute('data-rut');
                var rol = btn.getAttribute('data-rol');
                var accion = btn.name === 'activar_usuario' ? 'Activar' : 'Desactivar';

                document.getElementById('modalTitulo').innerText = `¿Está seguro que desea ${accion.toLowerCase()} este usuario?`;
                document.getElementById('modalDescripcion').innerText = `Por favor, confirme que desea realizar esta acción. Revise los datos del usuario:`;

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
                            <span class="block text-xs text-gray-500 font-semibold">RUT</span>
                            <span class="block text-base text-[#263238] font-bold">${rut}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3 col-span-2">
                            <span class="block text-xs text-gray-500 font-semibold">Rol</span>
                            <span class="block text-base text-[#263238] font-bold">${rol}</span>
                        </div>
                        <div class="bg-gray-100 rounded p-3 col-span-2">
                            <span class="block text-xs text-gray-500 font-semibold">Acción</span>
                            <span class="block text-base font-bold" style="color:${accion === 'Activar' ? '#4CAF50' : '#ef4444'}">${accion}</span>
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