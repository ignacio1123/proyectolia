<?php
require 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-md w-full text-center">
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

            // Verifica si el correo existe en la base de datos
            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $correo);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $token = bin2hex(random_bytes(50)); // Genera un token único
                $expire = date("Y-m-d H:i:s", strtotime('+1 hour')); // Expiración del token

                $sql = "UPDATE usuarios SET reset_token = ?, token_expiration = ? WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sss", $token, $expire, $correo);
                if ($stmt->execute()) {
                    echo '<div class="mb-2 text-[#00796b] font-semibold text-lg">¡Solicitud recibida!</div>';
                } else {
                    echo '<div class="mb-4 text-red-600 font-semibold">Error al almacenar el token: ' . htmlspecialchars($stmt->error) . '</div>';
                }
                // Muestra el enlace de restablecimiento en pantalla
                $reset_link = "http://localhost/proyectolia/final1.0/views/reset_password.php?token=" . $token;
                echo '<div class="mb-2 text-[#263238]">Haz clic en el botón para restablecer tu contraseña:</div>';
                echo '<a href="' . $reset_link . '" class="inline-block bg-[#4CAF50] hover:bg-[#388E3C] text-white font-semibold py-2 px-6 rounded transition duration-200 mb-4 shadow">Restablecer contraseña</a>';
                echo '<div class="text-xs text-gray-400 break-all mt-2">Si el botón no funciona, copia y pega este enlace en tu navegador:<br><span class="select-all">' . $reset_link . '</span></div>';
            } else {
                echo '<div class="mb-4 text-red-600 font-semibold">El correo electrónico no está registrado.</div>';
            }

            $stmt->close();
        }
        $conn->close();
        ?>
        <a href="olvideContraseña.php" class="inline-block mt-6 bg-green-400 hover:bg-[#388E3C]-500 text-[#263238] font-semibold py-2 px-4 rounded transition duration-200 shadow">
            Volver
        </a>
    </div>
</body>
</html>
