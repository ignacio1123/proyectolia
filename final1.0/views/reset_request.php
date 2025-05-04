<?php
require 'db_connection.php';

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
        $expire = date("Y-m-d H:i:s", strtotime('+1 hour')); // Establece la expiración del token

        $sql = "UPDATE usuarios SET reset_token = ?, token_expiration = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token, $expire, $correo);
        if ($stmt->execute()) {
            echo "Token almacenado correctamente.";
        } else {
            echo "Error al almacenar el token: " . $stmt->error;
        }
        // Muestra el enlace de restablecimiento en pantalla
        $reset_link = "http://localhost/final1.0/views/reset_password.php?token=" . $token;
        echo "Haz clic en el siguiente enlace para restablecer tu contraseña: <a href='$reset_link'>$reset_link</a>";
    } else {
        echo "El correo electrónico no está registrado.";
    }

    $stmt->close();
}
$conn->close();
