<?php
require 'db_connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verifica si el token es válido
    $sql = "SELECT * FROM usuarios WHERE reset_token = ? AND token_expiration > NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verifica si se ha enviado el formulario
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);
            $email = $user['email'];

            // Actualiza la contraseña en la base de datos
            $sql = "UPDATE usuarios SET password = ?, reset_token = NULL, token_expiration = NULL WHERE email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $nueva_contrasena, $email);
            $stmt->execute();

            echo "Tu contraseña ha sido restablecida con éxito.";
        }
    } else {
        echo "Token inválido o ha expirado.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-md w-full">
        <h2 class="text-2xl font-bold text-gray-800 text-center mb-4">Establecer Nueva Contraseña</h2>
        <form action="" method="post" class="space-y-4">
            <div>
                <label for="nueva_contrasena" class="block text-sm font-medium text-gray-700">Nueva Contraseña:</label>
                <input type="password" name="nueva_contrasena" id="nueva_contrasena" 
                       class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                       required>
            </div>
            <div class="flex justify-between items-center">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                    Restablecer Contraseña
                </button>
                <a href="../index.php" 
                   class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold py-2 px-4 rounded-lg transition duration-300">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</body>
</html>