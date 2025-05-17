<?php
session_start();
require_once 'db_connection.php';

// Obtener dispositivos disponibles de la base de datos
$sql_dispositivos = "SELECT id_dispositivo, nombre_dispositivo, estado, cantidad FROM dispositivos";
$result_dispositivos = $conn->query($sql_dispositivos);
$dispositivos = [];
if ($result_dispositivos->num_rows > 0) {
    while ($row = $result_dispositivos->fetch_assoc()) {
        $dispositivos[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['id_usuario'];
    $nombre_proyecto = $_POST['nombre_proyecto'];
    $descripcion = $_POST['descripcion'];
    $propuesta_valor = $_POST['propuesta_valor'];
    $merito_innovativo = $_POST['merito_innovativo'];
    $redes_apoyo = $_POST['redes_apoyo'];
    $factores_criticos = $_POST['factores_criticos'];
    $oportunidad_mercado = $_POST['oportunidad_mercado'];
    $potencial_mercado = $_POST['potencial_mercado'];
    $aspectos_validar = $_POST['aspectos_validar'];
    $presupuesto_preliminar = $_POST['presupuesto_preliminar'];

    // Insertar datos en la tabla de solicitudes
    $sql = "INSERT INTO solicitudes(id_usuario, nombre_proyecto, descripcion, propuesta_valor, merito_innovativo, redes_apoyo, factores_criticos, oportunidad_mercado, potencial_mercado, aspectos_validar, presupuesto_preliminar) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sssssssssss",
        $id_usuario,
        $nombre_proyecto,
        $descripcion,
        $propuesta_valor,
        $merito_innovativo,
        $redes_apoyo,
        $factores_criticos,
        $oportunidad_mercado,
        $potencial_mercado,
        $aspectos_validar,
        $presupuesto_preliminar
    );

    if ($stmt->execute()) {
        $id_solicitud = $conn->insert_id; // Obtener el ID de la solicitud recién insertada

        // Verificar si se enviaron datos de participantes
        if (!empty($_POST['participante']['nombre'])) {
            // Recorremos todos los participantes
            $participantes = count($_POST['participante']['nombre']);
            for ($i = 0; $i < $participantes; $i++) {
                $nombre = $_POST['participante']['nombre'][$i];
                $rut = $_POST['participante']['rut'][$i];
                $carrera = $_POST['participante']['carrera'][$i];
                $rol = $_POST['participante']['rol'][$i];
                $tipo = $_POST['participante']['tipo'][$i];

                // Insertar datos del participante en la tabla de participantes
                $sql_participante = "INSERT INTO participantes (id_solicitud, nombre, rut, carrera, rol, tipo) 
                                 VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_participante = $conn->prepare($sql_participante);
                $stmt_participante->bind_param(
                    "isssss",
                    $id_solicitud,
                    $nombre,
                    $rut,
                    $carrera,
                    $rol,
                    $tipo
                );
                $stmt_participante->execute();
            }
        }

        // Insertar dispositivos solicitados en la tabla solicitud_dispositivos y actualizar la cantidad en la tabla dispositivos
        if (!empty($_POST['dispositivos']['id_dispositivo']) && !empty($_POST['dispositivos']['cantidad'])) {
            $dispositivos = $_POST['dispositivos']['id_dispositivo'];
            $cantidades = $_POST['dispositivos']['cantidad'];

            for ($i = 0; $i < count($dispositivos); $i++) {
                $id_dispositivo = $dispositivos[$i];
                $cantidad = $cantidades[$i];

                if (!empty($id_dispositivo) && !empty($cantidad)) {
                    // Insertar cada dispositivo en la tabla solicitud_dispositivos
                    $sql_dispositivo = "INSERT INTO solicitud_dispositivos (id_solicitud, id_dispositivo, cantidad) 
                                        VALUES (?, ?, ?)";
                    $stmt_dispositivo = $conn->prepare($sql_dispositivo);
                    $stmt_dispositivo->bind_param("iii", $id_solicitud, $id_dispositivo, $cantidad);
                    $stmt_dispositivo->execute();

                    // Actualizar la cantidad en la tabla dispositivos
                    $sql_update_dispositivo = "UPDATE dispositivos SET cantidad = cantidad - ? WHERE id_dispositivo = ?";
                    $stmt_update_dispositivo = $conn->prepare($sql_update_dispositivo);
                    $stmt_update_dispositivo->bind_param("ii", $cantidad, $id_dispositivo);
                    $stmt_update_dispositivo->execute();
                }
            }
        }

        // Insertar dispositivos faltantes
        if (!empty($_POST['dispositivos_nuevos']['nombre']) && !empty($_POST['dispositivos_nuevos']['cantidad'])) {
            $nombres = $_POST['dispositivos_nuevos']['nombre'];
            $cantidades = $_POST['dispositivos_nuevos']['cantidad'];

            // Filtrar duplicados por nombre (ignorando mayúsculas/minúsculas y espacios)
            $nombresUnicos = [];
            $indicesUnicos = [];
            foreach ($nombres as $i => $nombre) {
                $nombreKey = strtolower(trim($nombre));
                if ($nombreKey !== '' && !in_array($nombreKey, $nombresUnicos)) {
                    $nombresUnicos[] = $nombreKey;
                    $indicesUnicos[] = $i;
                }
            }

            foreach ($indicesUnicos as $i) {
                $nombre = $nombres[$i];
                $cantidad = $cantidades[$i];

                if (!empty($nombre) && !empty($cantidad)) {
                    // Insertar en la tabla dispositivos_faltante con id_solicitud
                    $sql_faltante = "INSERT INTO dispositivos_faltante (id_solicitud, nombre_dispositivo, cantidad_dispositivo) 
                                    VALUES (?, ?, ?)";
                    $stmt_faltante = $conn->prepare($sql_faltante);
                    $stmt_faltante->bind_param("isi", $id_solicitud, $nombre, $cantidad);
                    $stmt_faltante->execute();
                }
            }
        }

        // Redirigir a pantallaEstudiante.php
        header("Location: pantallaEstudiante.php");
        exit();
    } else {
        echo "<p class='text-red-500'>Error al insertar datos: " . $conn->error . "</p>";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <title>Solicitud de Ingreso de Proyecto a Laboratorio de Innovación Aplicada</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            height: 100%;
        }

        form {
            background: white;

            border-radius: 5px;

            width: 100%;
        }

        .form-container {
            display: grid;
            grid-template-columns: (1fr);
            /* Esto crea 3 columnas de igual tamaño */
            gap: 30px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {

            color: white;
            border: none;

            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            width: 100%;
        }


        .section {
            margin-bottom: 20px;
        }

        @media (min-width: 640px) {
            .form-container {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>

    <?php
    require_once 'db_connection.php';

    if (!isset($_SESSION['id_usuario'])) {
        echo "Error: No se ha identificado al usuario. Por favor, inicia sesión.";
        header("Location: login.php");
        exit();
    }

    $id_usuario = $_SESSION['id_usuario'];

    if (!is_numeric($id_usuario)) {
        echo "Error: ID de usuario no válido.";
        exit();
    }

    $nombre = $_SESSION['nombre'];
    ?>

    <header class="absolute flex justify-end top-0 right-0 w-full bg-white p-4 shadow-md z-10" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
        <span class="text-black font-medium">Bienvenido, <?php echo htmlspecialchars($nombre); ?></span>
    </header>

    <!-- Menú lateral adaptado -->
    <nav class="fixed left-0 top-0 flex flex-col justify-between items-center bg-black text-white w-[12rem] min-h-screen py-8 z-10 shadow-lg">
        <!-- Logo -->
        <div>
            <a href="/" class="text-[1.2rem] font-bold">
                Laboratorio<span class="text-yellow-400 text-[1.5rem] font-bold">LIA</span>
            </a>
        </div>
    </nav>

    <!-- Ajusta el padding-left para que el contenido no se solape con el menú -->
    <div class="w-full h-auto flex justify-center items-center pt-20 pb-24 flex-col" style="padding-left: 12rem;">
        <form class="w-[80%] flex justify-center items-center" action="" method="POST">
            <div class="w-full">

                <div class="flex flex-wrap w-full justify-between items-center pb-10">
                    <div class="w-full sm:w-auto text-center sm:text-left">
                        <img src="../public/img/image.png" alt="Logo" class="mx-auto sm:mx-0">
                    </div>

                    <div class="w-full sm:w-auto text-center sm:text-left sm:mx-8 mt-4 sm:mt-0">
                        <h2 class="text-xl sm:text-2xl font-bold">Laboratorio de Innovación Aplicada</h2>
                        <p class="text-sm sm:text-base">Área Informática Concepción, Santo Tomás Concepción.</p>
                        <p class="text-sm sm:text-base">Solicitud de Ingreso de Proyecto a Laboratorio de Innovación Aplicada</p>
                    </div>

                    <div class="w-full sm:w-auto text-center sm:text-right mt-4 sm:mt-0">
                        <a href="../views/pantallaEstudiante.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                            Volver atrás
                        </a>
                    </div>
                </div>

                <div class="">


                    <div class="section">
                        <label for="nombre_proyecto">Nombre del proyecto</label>
                        <input required type="text" id="nombre_proyecto" name="nombre_proyecto" placeholder="Nombre del proyecto">
                    </div>

                    <div class="section">
                        <label for="descripcion">Descripción</label>
                        <input required type="text" id="descripcion" name="descripcion" placeholder="Descripción">
                    </div>

                    <div class="section">

                        <div class="flex flex-col">
                            <span class="text-black font-bold">Participantes del Proyecto</span>
                            <span>(nota: pueden participar hasta 5 alumnos o docentes de distintas áreas de IP, CFT o UST. El responsable del proyecto debe ser alumno o docente de área informática Concepción).</span>
                        </div>

                        <div id="participante">
                            <table class="min-w-full table-auto border-collapse" id="participanteTable">
                                <thead>
                                    <tr>
                                        <th class="border px-4 py-2">Nombre</th>
                                        <th class="border px-4 py-2">RUT</th>
                                        <th class="border px-4 py-2">Carrera</th>
                                        <th class="border px-4 py-2">Rol</th>
                                        <th class="border px-4 py-2">Tipo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="participanteRow">
                                        <td class="border px-4 py-2"><input type="text" placeholder="Nombre" name="participante[nombre][]" required></td>
                                        <td class="border px-4 py-2"><input type="text" placeholder="Rut" name="participante[rut][]" required></td>
                                        <td class="border px-4 py-2"><input type="text" placeholder="Carrera" name="participante[carrera][]" required></td>
                                        <td class="border px-4 py-2"><input type="text" placeholder="Rol" name="participante[rol][]" required></td>
                                        <td class="border px-4 py-2">
                                            <select name="participante[tipo][]" required>
                                                <option value="Alumno">Alumno</option>
                                                <option value="Docente">Docente</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>


                            <div class="flex justify-end gap-2 m-2">
                                <button type="button" id="addParticipante" class="w-auto  text-white  p-[0.2rem] bg-black">+</button>
                                <button type="button" id="deleteParticipante" class="w-auto  text-white p-1 bg-red-700">-</button>
                            </div>
                        </div>

                        <script>
                            document.addEventListener("DOMContentLoaded", function() {
                                const addParticipanteButton = document.getElementById("addParticipante");
                                const deleteParticipanteButton = document.getElementById("deleteParticipante");
                                const participanteTable = document.getElementById("participanteTable");


                                addParticipanteButton.addEventListener("click", function() {
                                    const newRow = participanteTable.querySelector(".participanteRow").cloneNode(true);
                                    const inputs = newRow.querySelectorAll("input, select");
                                    inputs.forEach(input => input.value = "");
                                    participanteTable.querySelector("tbody").appendChild(newRow);
                                });


                                deleteParticipanteButton.addEventListener("click", function() {
                                    const rows = participanteTable.querySelectorAll("tbody tr");
                                    if (rows.length > 1) {
                                        rows[rows.length - 1].remove();
                                    }
                                });
                            });
                        </script>

                    </div>

                    <div class="section">

                        <div class="flex flex-col">
                            <span class="text-black font-bold">Propuesta de Valor</span>
                            <span> Describa y proporcione antecedentes que permitan conocer y comprender en qué consiste el nuevo producto o servicio innovador, y cómo este soluciona un problema o constituye una oportunidad de negocios en un determinado mercado objetivo (sus posibles clientes). Maximo 300 Palabras</span>
                        </div>

                        <input required type="text" id="propuesta_valor" name="propuesta_valor" placeholder="Propuesta de Valor">
                    </div>

                    <div class="section">
                        <label for="merito_innovativo">Mérito innovativo</label>
                        <span>Describa las características o atributos que permiten diferenciar el producto/servicio propuesto, con respecto a lo que ya existe a nivel regional y nacional. Ingrese cada factor, característica o atributo diferenciador describiendo como se obtiene o fundamenta dicho atributo diferenciador y cómo este se traduce en una barrera de entrada (o ventajas competitivas), para impedir que la propuesta de negocio sea fácilmente copiable por la competencia. Indicar, además, si esta diferenciación es A NIVEL REGIONAL O NACIONAL. Ingrese un mínimo de 3 y un máximo de 5 factores diferenciadores.</span>
                        <input required type="text" id="merito_innovativo" name="merito_innovativo" placeholder="Mérito innovativo">
                    </div>

                    <div class="section">
                        <label for="redes_apoyo">Redes de apoyo</label>
                        <span>Identifique las redes de apoyo que posee y describa cómo estas pueden aportar y/o apoyar en el desarrollo y éxito del proyecto. Mencione, si existe algún grado de compromiso o formalización del posible apoyo. Maximo 200 Palabras</span>
                        <input required type="text" id="redes_apoyo" name="redes_apoyo" placeholder="Redes de apoyo">
                    </div>

                    <div class="section">
                        <label for="factores_criticos">Factores críticos</label>
                        <span>Identifique y describa las posibles dificultades que pudieran afectar el buen desarrollo y ejecución del nuevo producto o servicio innovador, cómo estas variables criticas serán abordadas por el equipo emprendedor. Maximo 200 Palabras</span>
                        <input required type="text" id="factores_criticos" name="factores_criticos" placeholder="Factores criticos">
                    </div>

                    <div class="section">
                        <label for="oportunidad_mercado">Oportunidad de mercado</label>
                        <span>Identifique y justifique la oportunidad de mercado que da origen a esta nueva propuesta de negocios. Indique si el mercado objetivo nacional o internacional.</span>
                        <textarea required type="text" id="oportunidad_mercado" name="oportunidad_mercado" placeholder="Oportunidad de mercado"></textarea>
                    </div>

                    <div class="section">
                        <label for="potencial_mercado">Potencial de mercado</label>
                        <span>Describa y determine el tamaño (datos numéricos) del mercado especifico al cual desea llegar con esta nueva propuesta de negocios.</span>
                        <input required type="text" id="potencial_mercado" name="potencial_mercado" placeholder="Potencial de mercado">
                    </div>

                    <div class="section">
                        <label for="aspectos_validar">Aspectos a validar</label>
                        <span>Identifique y describa que tipo de pruebas, ensayos o certificaciones técnicas debe realizar para validar técnica y comercialmente el producto.</span>
                        <input required type="text" id="aspectos_validar" name="aspectos_validar" placeholder="Aspectos a validar">
                    </div>

                    <div class="section">
                        <label for="presupuesto_preliminar">Presupuesto preliminar</label>
                        <span>Establezca una relación de los componentes a adquirir y/o servicios necesarios de contratar para llevar a cabo la implementación del prototipo deseado.</span>
                        <input required type="text" id="presupuesto_preliminar" name="presupuesto_preliminar" placeholder="Presupuesto preliminar">
                    </div>

                    <div class="section">
                        <div class="flex flex-col">
                            <span class="text-black font-bold">Dispositivos a utilizar</span>
                            <span>Seleccione los dispositivos a utilizar para su proyecto y especifique la cantidad de cada uno.</span>
                        </div>

                        <div id="dispositivos">
                            <table class="min-w-full table-auto border-collapse" id="dispositivosTable">
                                <thead>
                                    <tr>
                                        <th class="border px-4 py-2">Nombre del dispositivo</th>
                                        <th class="border px-4 py-2">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="dispositivoRow">
                                        <td class="border px-4 py-2">
                                            <select name="dispositivos[id_dispositivo][]" required>
                                                <option value="">Selecciona un dispositivo</option>
                                                <?php foreach ($dispositivos as $dispositivo): ?>
                                                    <?php
                                                    $estado = $dispositivo['estado'];
                                                    $disabled = $estado === 'inactivo' ? 'disabled' : '';
                                                    $style = $estado === 'inactivo' ? 'style="color: gray;"' : '';
                                                    $cantidad = $dispositivo['cantidad'];
                                                    ?>
                                                    <option value="<?php echo htmlspecialchars($dispositivo['id_dispositivo']); ?>"
                                                        data-cantidad="<?php echo $cantidad; ?>"
                                                        <?php echo $disabled; ?> <?php echo $style; ?>>
                                                        <?php echo htmlspecialchars($dispositivo['nombre_dispositivo']); ?> (<?php echo $estado; ?>, Stock: <?php echo $cantidad; ?>)
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="border px-4 py-2">
                                            <input type="number" name="dispositivos[cantidad][]" min="1" placeholder="Cantidad" required>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="flex justify-end gap-2 m-2">
                                <button type="button" id="addDispositivo" class="w-auto text-white p-[0.2rem] bg-black">+</button>
                                <button type="button" id="deleteDispositivo" class="w-auto text-white p-1 bg-red-700">-</button>
                            </div>
                            <!-- Botón para mostrar dispositivos no encontrados -->
                            <div class="flex justify-start gap-2 m-2">
                                <button type="button" id="mostrarDispositivosNuevos" class="w-auto text-black border border-black bg-white hover:bg-gray-100">¿No encuentras tu dispositivo?</button>
                            </div>
                        </div>
                    </div>

                    <!-- Oculta la sección por defecto -->
                    <div class="section" id="dispositivos-nuevos-section" style="display:none;">
                        <div class="flex flex-col">
                            <span class="text-black font-bold">Dispositivos que no se encuentran</span>
                            <span>Agregue nombre del dispositivo que no encuentre y la cantidad que necesita</span>
                        </div>
                        <div id="dispositivos-nuevos">
                            <table class="min-w-full table-auto border-collapse" id="dispositivosNuevosTable">
                                <thead>
                                    <tr>
                                        <th class="border px-4 py-2">Nombre del dispositivo</th>
                                        <th class="border px-4 py-2">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="dispositivoNuevoRow">
                                        <td class="border px-4 py-2">
                                            <input type="text" name="dispositivos_nuevos[nombre][]" placeholder="Nombre del dispositivo">
                                        </td>
                                        <td class="border px-4 py-2">
                                            <input type="number" name="dispositivos_nuevos[cantidad][]" min="1" placeholder="Cantidad">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="flex justify-end gap-2 m-2">
                                <button type="button" id="addDispositivoNuevo" class="w-auto text-white p-[0.2rem] bg-black">+</button>
                                <button type="button" id="deleteDispositivoNuevo" class="w-auto text-white p-1 bg-red-700">-</button>
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const addDispositivoButton = document.getElementById("addDispositivo");
                            const deleteDispositivoButton = document.getElementById("deleteDispositivo");
                            const dispositivosTable = document.getElementById("dispositivosTable");

                            // Función para actualizar los selects y evitar duplicados
                            function actualizarSelects() {
                                const selects = dispositivosTable.querySelectorAll('select[name="dispositivos[id_dispositivo][]"]');
                                const seleccionados = Array.from(selects).map(s => s.value).filter(v => v);

                                selects.forEach(select => {
                                    Array.from(select.options).forEach(option => {
                                        if (option.value && seleccionados.includes(option.value) && select.value !== option.value) {
                                            option.disabled = true;
                                        } else {
                                            option.disabled = false;
                                        }
                                    });
                                });
                            }

                            // Función para validar cantidad según stock
                            function validarCantidad(input) {
                                const select = input.closest('tr').querySelector('select[name="dispositivos[id_dispositivo][]"]');
                                const selectedOption = select.options[select.selectedIndex];
                                const maxCantidad = parseInt(selectedOption.getAttribute('data-cantidad'), 10);
                                const valor = parseInt(input.value, 10);

                                if (!isNaN(maxCantidad) && valor > maxCantidad) {
                                    swal("Cantidad excedida", "No hay suficiente stock para este dispositivo.", "warning");
                                    input.value = maxCantidad > 0 ? maxCantidad : '';
                                }
                            }

                            // Evento para agregar fila
                            addDispositivoButton.addEventListener("click", function() {
                                const newRow = dispositivosTable.querySelector(".dispositivoRow").cloneNode(true);
                                const inputs = newRow.querySelectorAll("input, select");
                                inputs.forEach(input => input.value = "");
                                dispositivosTable.querySelector("tbody").appendChild(newRow);
                                actualizarSelects();
                                // Agrega eventos a los nuevos inputs/selects
                                newRow.querySelector('select').addEventListener('change', actualizarSelects);
                                newRow.querySelector('input[type="number"]').addEventListener('input', function() {
                                    validarCantidad(this);
                                });
                            });

                            // Evento para eliminar fila
                            deleteDispositivoButton.addEventListener("click", function() {
                                const rows = dispositivosTable.querySelectorAll("tbody tr");
                                if (rows.length > 1) {
                                    rows[rows.length - 1].remove();
                                    actualizarSelects();
                                }
                            });

                            // Eventos iniciales para selects y cantidad
                            dispositivosTable.querySelectorAll('select[name="dispositivos[id_dispositivo][]"]').forEach(select => {
                                select.addEventListener('change', actualizarSelects);
                            });
                            dispositivosTable.querySelectorAll('input[type="number"]').forEach(input => {
                                input.addEventListener('input', function() {
                                    validarCantidad(this);
                                });
                            });

                            // ...resto de tu código para dispositivos nuevos...
                            const addDispositivoNuevoButton = document.getElementById("addDispositivoNuevo");
                            const deleteDispositivoNuevoButton = document.getElementById("deleteDispositivoNuevo");
                            const dispositivosNuevosTable = document.getElementById("dispositivosNuevosTable");

                            addDispositivoNuevoButton.addEventListener("click", function() {
                                const newRow = dispositivosNuevosTable.querySelector(".dispositivoNuevoRow").cloneNode(true);
                                const inputs = newRow.querySelectorAll("input");
                                inputs.forEach(input => input.value = "");
                                dispositivosNuevosTable.querySelector("tbody").appendChild(newRow);
                                // Agregar evento de validación al nuevo input
                                newRow.querySelector('input[name="dispositivos_nuevos[nombre][]"]').addEventListener('change', validarNombresNuevos);
                            });

                            deleteDispositivoNuevoButton.addEventListener("click", function() {
                                const rows = dispositivosNuevosTable.querySelectorAll("tbody tr");
                                if (rows.length > 1) {
                                    rows[rows.length - 1].remove();
                                }
                            });

                            // Validar que no se repita el nombre en dispositivos no encontrados
                            function validarNombresNuevos() {
                                const inputs = Array.from(document.querySelectorAll('input[name="dispositivos_nuevos[nombre][]"]'));
                                const nombres = inputs.map(input => input.value.trim().toLowerCase()).filter(v => v);
                                const duplicados = nombres.filter((item, idx) => nombres.indexOf(item) !== idx);
                                if (duplicados.length > 0) {
                                    swal("Nombre duplicado", "No puedes ingresar el mismo dispositivo más de una vez.", "warning");
                                    return false;
                                }
                                return true;
                            }

                            // Al escribir en los inputs, validar duplicados
                            document.querySelectorAll('input[name="dispositivos_nuevos[nombre][]"]').forEach(input => {
                                input.addEventListener('change', validarNombresNuevos);
                            });

                            // Opcional: Validar antes de enviar el formulario
                            document.querySelector('form').addEventListener('submit', function(e) {
                                if (!validarNombresNuevos()) {
                                    e.preventDefault();
                                }
                            });

                            // Mostrar sección de dispositivos nuevos al hacer clic en el botón
                            document.getElementById("mostrarDispositivosNuevos").addEventListener("click", function() {
                                document.getElementById("dispositivos-nuevos-section").style.display = "block";
                                this.style.display = "none";
                            });
                        });
                    </script>
                </div>
                <button class=" px-4 py-2 w-full block bg-black hover:bg-black/90  text-white">Enviar</button>
            </div>
        </form>
    </div>
</body>
</html>