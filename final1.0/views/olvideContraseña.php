<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidé mi Contraseña</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-md w-full">
        <!-- Enlace para volver -->
        <a href="../views/pantallaPrincipal.php"
           class="bg-[#4CAF50]-400 hover:bg-[#388E3C]-500 text-[#263238] font-semibold py-2 px-4 rounded mb-4 inline-block transition duration-200 text-center">
            &larr; Volver
        </a>
        <!-- Título -->
        <h2 class="text-2xl font-bold text-[#00796b] text-center mb-4">Restablecer Contraseña</h2>
        <form action="reset_request.php" method="post" class="space-y-4">
            <div>
                <label for="correo" class="block text-sm font-medium text-[#263238]">Ingrese su correo electrónico:</label>
                <input type="email" name="correo" id="correo"
                       class="w-full mt-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#4CAF50] focus:border-[#4CAF50] text-[#263238]"
                       placeholder="tuemail@ejemplo.com" required>
            </div>
            <div class="text-center">
                <button type="submit"
                        class="w-full bg-[#4CAF50] hover:bg-[#388E3C] text-white font-semibold py-2 px-4 rounded-lg transition duration-300">
                    Enviar enlace de restablecimiento
                </button>
            </div>
        </form>
    </div>
</body>
</html>