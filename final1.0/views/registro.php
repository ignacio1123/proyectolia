<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 24px 28px;
            border-radius: 10px;
            box-shadow: 0 0 16px rgba(0,0,0,0.10);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            color: #00796b;
            margin-bottom: 18px;
        }
        form {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-top: 10px;
            color: #263238;
            font-weight: 500;
        }
        input, select {
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            background: #f9f9f9;
            color: #263238;
            font-size: 1em;
        }
        input:focus, select:focus {
            outline: 2px solid #4CAF50;
            background: #fff;
        }
        .btn-registrar {
            background-color: #4CAF50;
            color: #fff;
            border: none;
            font-weight: 600;
            cursor: pointer;
            margin-top: 18px;
            padding: 10px 0;
            border-radius: 4px;
            transition: background 0.2s;
        }
        .btn-registrar:hover {
            background-color: #388E3C;
        }
        .btn-volver {
            background-color: #4CAF50;
            color: #263238;
            font-weight: 600;
            padding: 8px 24px;
            border-radius: 4px;
            transition: background 0.2s;
            display: inline-block;
            margin-bottom: 18px;
            text-decoration: none;
        }
        .btn-volver:hover {
            background-color: #388E3C;
        }
        .message {
            margin-top: 10px;
            text-align: center;
            color: #F44336;
            font-weight: 500;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/proyectolia/final1.0/public/css/responsive.css">
</head>
<body>
    <div class="container">
        <div class="w-full text-center mb-2">
            <a href="../views/pantallaPrincipal.php" class="btn-volver">
                Volver atrás
            </a>
        </div>
        <h2>Registro de Usuarios</h2>
        <?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
        <form action="" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="nombre">Nombre Completo:</label>
            <input type="text" name="nombre" id="nombre" required pattern="[A-ZÁÉÍÓÚÑ][a-záéíóúñ ]*" title="Solo letras, la primera letra en mayúscula, sin números">

            <label for="apellido">Apellido Completo:</label>
            <input type="text" name="apellido" id="apellido" required pattern="[A-ZÁÉÍÓÚÑ][a-záéíóúñ ]*" title="Solo letras, la primera letra en mayúscula, sin números">

            <label for="email">Email:</label>
            <input type="email" name="email" id="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required minlength="6" maxlength="20" pattern="[A-Za-z0-9]+" title="Debe tener entre 6 y 20 caracteres, solo letras y números.">

            <label for="rol">Rol:</label>
            <select name="rol" id="rol" required>
                <option value="estudiante">Estudiante</option>
                <option value="director">Director</option>
            </select>

            <label for="rut">RUT:</label>
            <input type="text" name="rut" id="rut" required maxlength="12" placeholder="11.111.111-1" pattern="^\d{1,2}\.?\d{3}\.?\d{3}-[\dkK]{1}$" title="Formato: 11.111.111-1 o 11111111-1, solo K como dígito verificador">

            <button type="submit" class="btn-registrar">
                Registrar
            </button>
        </form>

        <script>
        // Formatea el RUT automáticamente al escribir
        document.getElementById('rut').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9kK]/g, '').toUpperCase();

            // Elimina cualquier 'K' o 'k' que no sea el último carácter
            if (value.length > 1) {
                let cuerpo = value.slice(0, -1).replace(/[K]/g, ''); // Elimina 'K' del cuerpo
                let dv = value.slice(-1);
                // Solo permite K como dígito verificador
                if (dv !== 'K' && !/\d/.test(dv)) {
                    dv = '';
                }
                let rutFormateado = '';
                let i = 0;
                for (let j = cuerpo.length - 1; j >= 0; j--) {
                    rutFormateado = cuerpo[j] + rutFormateado;
                    i++;
                    if (i % 3 === 0 && j !== 0) rutFormateado = '.' + rutFormateado;
                }
                if (dv) rutFormateado += '-' + dv;
                e.target.value = rutFormateado;
            } else {
                // Si solo hay un carácter, solo permite número
                e.target.value = value.replace(/[^0-9]/g, '');
            }
        });

        document.getElementById('nombre').addEventListener('input', function(e) {
            let val = e.target.value.replace(/[0-9]/g, ''); // Elimina números
            e.target.value = val.charAt(0).toUpperCase() + val.slice(1).replace(/[^a-záéíóúñ ]/gi, '');
        });
        document.getElementById('apellido').addEventListener('input', function(e) {
            let val = e.target.value.replace(/[0-9]/g, ''); // Elimina números
            e.target.value = val.charAt(0).toUpperCase() + val.slice(1).replace(/[^a-záéíóúñ ]/gi, '');
        });
        </script>

        <?php
        // Variable para almacenar mensajes
        $message = '';

        // Función para limpiar y formatear el RUT
        function formatearRut($rut) {
            $rut = preg_replace('/[^0-9kK]/', '', $rut);
            $dv = strtoupper(substr($rut, -1));
            $cuerpo = substr($rut, 0, -1);
            $cuerpo = ltrim($cuerpo, '0');
            $cuerpo_formateado = '';
            $len = strlen($cuerpo);
            for ($i = 0; $i < $len; $i++) {
                if (($len - $i) % 3 == 0 && $i != 0) {
                    $cuerpo_formateado .= '.';
                }
                $cuerpo_formateado .= $cuerpo[$i];
            }
            return $cuerpo_formateado . '-' . $dv;
        }

        // Función para validar el RUT chileno
        function validarRut($rut) {
            $rut = preg_replace('/[^0-9kK]/', '', $rut);
            if (strlen($rut) < 2) return false;
            $cuerpo = substr($rut, 0, -1);
            $dv = strtoupper(substr($rut, -1));
            // No permitir 'K' en el cuerpo
            if (strpos($cuerpo, 'k') !== false || strpos($cuerpo, 'K') !== false) return false;
            if (!ctype_digit($cuerpo)) return false;
            if (!preg_match('/^[0-9K]$/', $dv)) return false;
            // Calcular dígito verificador
            $suma = 0;
            $multiplo = 2;
            for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {
                $suma += $cuerpo[$i] * $multiplo;
                $multiplo = $multiplo == 7 ? 2 : $multiplo + 1;
            }
            $resto = $suma % 11;
            $dvEsperado = 11 - $resto;
            if ($dvEsperado == 11) $dvEsperado = '0';
            elseif ($dvEsperado == 10) $dvEsperado = 'K';
            else $dvEsperado = (string)$dvEsperado;
            return $dv == $dvEsperado;
        }

        // Función para validar la contraseña según las reglas del proyecto
        function validarPassword($password) {
            // Solo letras y números, entre 6 y 20 caracteres
            return preg_match('/^[A-Za-z0-9]{6,20}$/', $password);
        }

        // Valida que solo haya letras y la primera letra sea mayúscula
        function validarNombreApellido($valor) {
            // Solo letras (incluye tildes y ñ), primera letra mayúscula, resto minúsculas
            return preg_match('/^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+(?: [A-ZÁÉÍÓÚÑ][a-záéíóúñ]+)*$/u', $valor);
        }

        function nombrePareceAleatorio($nombre) {
            // Si tiene más de 4 letras iguales seguidas o no tiene vocales
            if (preg_match('/([a-záéíóúñ])\1{3,}/i', $nombre)) return true;
            if (!preg_match('/[aeiouáéíóú]/i', $nombre)) return true;
            return false;
        }

        $errores = [];

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            // Configuración de la conexión a la base de datos
            $host = 'localhost';
            $dbname = 'proyectolab';
            $username = 'root';
            $password = ''; 

            try {
                // Crear la conexión con la base de datos
                $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Capturar los datos del formulario con verificación de claves
                $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
                $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : null;
                $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : null;
                $password_plain = isset($_POST['password']) ? $_POST['password'] : null;
                $rol = isset($_POST['rol']) ? trim($_POST['rol']) : null;
                $rut = isset($_POST['rut']) ? trim($_POST['rut']) : null;
                $csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

                // Verificar el token CSRF
                if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
                    $errores[] = "Error de seguridad: Token CSRF inválido.";
                }

                // Validar y formatear el RUT antes de continuar
                if ($rut) {
                    $rut_limpio = preg_replace('/[^0-9kK]/', '', $rut);
                    if (!validarRut($rut_limpio)) {
                        $errores[] = "El RUT ingresado no es válido. Asegúrate de ingresarlo correctamente y con el dígito verificador correcto.";
                    } else {
                        $rut = formatearRut($rut_limpio);
                    }
                } else {
                    $errores[] = "El campo RUT es obligatorio.";
                }

                // Validar la contraseña antes de continuar
                if ($password_plain && !validarPassword($password_plain)) {
                    $errores[] = "La contraseña debe tener entre 6 y 20 caracteres, solo letras y números.";
                } elseif (!$password_plain) {
                    $errores[] = "El campo contraseña es obligatorio.";
                }

                // Validar nombre y apellido
                if ($nombre && !validarNombreApellido($nombre)) {
                    $errores[] = "El nombre debe contener solo letras y empezar con mayúscula.";
                } elseif (!$nombre) {
                    $errores[] = "El campo nombre es obligatorio.";
                }
                if ($apellido && !validarNombreApellido($apellido)) {
                    $errores[] = "El apellido debe contener solo letras y empezar con mayúscula.";
                } elseif (!$apellido) {
                    $errores[] = "El campo apellido es obligatorio.";
                }

                // Validar el email
                if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errores[] = "El email ingresado no es válido.";
                } elseif (!$email) {
                    $errores[] = "El campo email es obligatorio.";
                }

                // Validar rol
                if (!$rol) {
                    $errores[] = "El campo rol es obligatorio.";
                }

                // Validar si el nombre parece aleatorio
                if ($nombre && nombrePareceAleatorio($nombre)) {
                    $errores[] = "El nombre ingresado parece inválido. Por favor, revisa que sea un nombre real.";
                }

                // Validar si el apellido parece aleatorio
                if ($apellido && nombrePareceAleatorio($apellido)) {
                    $errores[] = "El apellido ingresado parece inválido. Por favor, revisa que sea un apellido real.";
                }

                if (empty($errores)) {
                    // Verificar si el RUT ya existe
                    $checkRUTQuery = "SELECT * FROM usuarios WHERE rut = :rut";
                    $checkRUTStmt = $conn->prepare($checkRUTQuery);
                    $checkRUTStmt->execute([':rut' => $rut]);

                    if ($checkRUTStmt->rowCount() > 0) {
                        $errores[] = "El RUT $rut ya está registrado.";
                    } else {
                        // Hash de la contraseña
                        $password_hashed = password_hash($password_plain, PASSWORD_BCRYPT);

                        // Fecha de creación
                        $fecha_creacion = date("Y-m-d H:i:s");

                        // Estado inicial del usuario (pendiente)
                        $estado = 'pendiente';

                        // Preparar la consulta de inserción
                        $insertQuery = "INSERT INTO usuarios (nombre, apellido, email, password, rol, rut, fecha_creacion, estado) VALUES (:nombre, :apellido, :email, :password, :rol, :rut, :fecha_creacion, :estado)";
                        $insertStmt = $conn->prepare($insertQuery);
                        $insertStmt->execute([
                            ':nombre' => $nombre,
                            ':apellido' => $apellido,
                            ':email' => $email,
                            ':password' => $password_hashed,
                            ':rol' => $rol,
                            ':rut' => $rut,
                            ':fecha_creacion' => $fecha_creacion,
                            ':estado' => $estado
                        ]);

                        $message = "Registro exitoso. Tu cuenta está pendiente de aprobación por el administrador.";
                    }
                } else {
                    $message = implode("\n", $errores);
                }
            } catch (PDOException $e) {
                error_log($e->getMessage()); // Guarda el error en el log del servidor
                $message = "Error interno. Intenta más tarde.";
            }
        }
        ?>

        <?php if (!empty($message)): ?>
    <script>
        window.onload = function() {
            Swal.fire({
                icon: "<?php echo (strpos($message, 'exitoso') !== false) ? 'success' : 'error'; ?>",
                title: "<?php echo (strpos($message, 'exitoso') !== false) ? '¡Registro exitoso!' : '¡Atención!'; ?>",
                text: "<?php echo str_replace(array("\r", "\n"), '', htmlspecialchars($message)); ?>",
                confirmButtonColor: '#facc15'
            });
        }
    </script>
    <?php endif; ?>
</body>
</html>
