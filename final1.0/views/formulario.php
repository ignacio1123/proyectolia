<?php
session_start();
require_once 'db_connection.php';

// Obtener dispositivos disponibles de la base de datos
$sql_dispositivos = "SELECT id_dispositivo, nombre_dispositivo FROM dispositivos";
$result_dispositivos = $conn->query($sql_dispositivos);
$dispositivos = [];
if ($result_dispositivos->num_rows > 0) {
    while ($row = $result_dispositivos->fetch_assoc()) {
        $dispositivos[] = $row;
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
        $id_dispositivo = $_POST['id_dispositivo']; // Dispositivo seleccionado
        $cantidad = $_POST['cantidad']; // Cantidad del dispositivo

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

            // Insertar dispositivo solicitado en la tabla solicitud_dispositivos
            if (!empty($id_dispositivo) && !empty($cantidad)) {
                $sql_dispositivo = "INSERT INTO solicitud_dispositivos (id_solicitud, id_dispositivo, cantidad) 
                                VALUES (?, ?, ?)";
                $stmt_dispositivo = $conn->prepare($sql_dispositivo);
                $stmt_dispositivo->bind_param("iii", $id_solicitud, $id_dispositivo, $cantidad);
                $stmt_dispositivo->execute();
            }

            echo "<p class='text-green-500'>Datos insertados correctamente</p>";
        } else {
            echo "<p class='text-red-500'>Error al insertar datos: " . $conn->error . "</p>";
        }

        $stmt->close();
    }
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

    <nav class="absolute left-0 flex flex-col justify-between items-center bg-black text-white w-[12rem] h-[145rem] py-8 z-10 shadow-lg">
        <!-- Logo -->
        <div>
            <a href="/" class="text-[1.2rem] font-bold">
                Laboratorio<span class="text-yellow-400 text-[1.5rem] font-bold">LIA</span>
            </a>
        </div>

        <!-- Enlace Cerrar Sesión -->
        <div>
            <a href="../logout.php" class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded transition duration-300">
                Cerrar Sesión
            </a>
        </div>
    </nav>

    <div class="w-full h-auto flex justify-center items-center pl-40  pt-20 pb-24 flex-col">

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
                        <a href="../views/pantallaEstudiante.php" class="text-blue-500 hover:underline">Volver atrás</a>
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
                            <span> Describa y proporcione antecedentes que permitan conocer y comprender en qué consiste el nuevo producto o servicio innovador, y cómo este soluciona un problema o constituye una oportunidad de negocios en un determinado mercado objetivo (sus posibles clientes). Maximo 300 Palabras</span>
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

                    <div class="mb-4">
                        <label for="dispositivoSelect" class="block text-lg font-semibold">Selecciona un dispositivo</label>

                        <span>Seleccione los dispositivos a utilizar para su proyecto, recuerda seleccionar la cantidad de dispositivos a utilizar.</span>
                        <div class="custom-select">
                            <select id="dispositivoSelect" name="id_dispositivo" class="w-full p-2 mt-2 border border-gray-300 rounded">
                                <option value="">Selecciona un dispositivo</option>
                                <?php foreach ($dispositivos as $dispositivo): ?>
                                    <option value="<?php echo htmlspecialchars($dispositivo['id_dispositivo']); ?>">
                                        <?php echo htmlspecialchars($dispositivo['nombre_dispositivo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>



                    <style>
                        .custom-select {
                            position: relative;
                            display: inline-block;
                            width: 100%;
                        }

                        .custom-select select {
                            display: none;

                        }

                        .select-selected {
                            background-color: #fff;
                            border: 1px solid #ccc;
                            padding: 10px;
                            border-radius: 4px;
                            cursor: pointer;
                        }

                        .select-items {
                            position: absolute;
                            background-color: #fff;
                            border: 1px solid #ccc;
                            border-radius: 4px;
                            z-index: 99;
                            max-height: 150px;
                            overflow-y: auto;
                            width: 100%;
                            display: none;
                        }

                        .select-items div {
                            padding: 10px;
                            cursor: pointer;
                        }

                        .select-items div:hover {
                            background-color: #f0f0f0;
                        }
                    </style>


                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const select = document.getElementById('dispositivoSelect');
                            const selectedDiv = document.createElement('div');
                            selectedDiv.className = 'select-selected';
                            selectedDiv.innerHTML = select.options[select.selectedIndex].innerHTML;
                            select.parentNode.insertBefore(selectedDiv, select);

                            const itemsDiv = document.createElement('div');
                            itemsDiv.className = 'select-items';

                            for (let i = 0; i < select.options.length; i++) {
                                const itemDiv = document.createElement('div');
                                itemDiv.innerHTML = select.options[i].innerHTML;
                                itemDiv.addEventListener('click', function() {
                                    selectedDiv.innerHTML = this.innerHTML;
                                    select.selectedIndex = i;
                                    itemsDiv.style.display = 'none';
                                });
                                itemsDiv.appendChild(itemDiv);
                            }

                            selectedDiv.addEventListener('click', function() {
                                itemsDiv.style.display = itemsDiv.style.display === 'block' ? 'none' : 'block';
                            });

                            select.parentNode.appendChild(itemsDiv);

                            document.addEventListener('click', function(e) {
                                if (!selectedDiv.contains(e.target)) {
                                    itemsDiv.style.display = 'none';
                                }
                            });
                        });
                    </script>


                    <div class="mb-4">
                        <label for="cantidad">Cantidad:</label>
                        <input type="number" id="cantidad" name="cantidad" required min="1" class="w-full p-2 mt-2 border border-gray-300 rounded" placeholder="Ingresa la cantidad de dispositivos">
                    </div>


                </div>


                <button class=" px-4 py-2 w-full block bg-black hover:bg-black/90  text-white">Enviar</button>

            </div>
        </form>
    </div>

</body>

</html>