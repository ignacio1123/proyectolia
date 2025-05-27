<?php
session_start();

// Configuración de conexión a la base de datos
$servername = "localhost"; 
$username = "root"; 
$password = "";  
$dbname = "proyectolab";   

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);   

// Verificar conexión
if ($conn->connect_error) {     
    die("Error de conexión: " . $conn->connect_error); 
} 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['iniciar_sesion'])) {

    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    $contraseña = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {

            $user = $result->fetch_assoc();

            // Verificar el estado del usuario
            if ($user['estado'] === 'Pendiente' || $user['estado'] === 'Inactivo') {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Acceso denegado',
                                text: 'Tu cuenta está en un estado no válido (Pendiente o Inactivo). Por favor, contacta al administrador/Director de Carrera.',
                                confirmButtonText: 'Aceptar'
                            });
                        });
                      </script>";
            } else if ($user['estado'] === 'Rechazado') {
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Acceso rechazado',
                                text: 'Tu registro fue rechazado. Por favor, comunícate con el Director para más información.',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                window.location = '" . htmlspecialchars($_SERVER["PHP_SELF"]) . "';
                            });
                        });
                      </script>";
            } else if (($user['estado'] === 'Aprobado' || $user['estado'] === 'Activo') && password_verify($contraseña, $user['password'])) {

                $_SESSION['correo'] = $correo;
                $_SESSION['role'] = $user['rol'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['id_usuario'] = $user['id_usuario'];

                switch ($user['rol']) {
                    case 'estudiante':
                        header("Location: pantallaEstudiante.php");
                        break;
                    case 'director':
                        header("Location: pantallaDirector.php");
                        break;
                    case 'administrador':
                        header("Location: pantallaAdmin.php");
                        break;
                    default:
                        $error = "Rol no reconocido.";
                }

                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }

        $stmt->close();
    } else {
        $error = "Error en la preparación de la consulta.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Universidad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Google Fonts for Material Design -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Particles.js -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }

        body {
            background-color: #121212;
            color: #fff;
            overflow-x: hidden;
        }

        #particles-js {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
        }

        .navbar {
            backdrop-filter: blur(10px);
            background-color: rgba(0, 77, 64, 0.95); /* Dark green with transparency */
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
            z-index: 50;
        }

        .form-container {
            backdrop-filter: blur(16px);
            background-color: rgba(38, 50, 56, 0.8); /* Material dark background with transparency */
            border-radius: 8px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            z-index: 10;
            position: relative;
            width: 100%;
            max-width: 100%;
        }

        @media (min-width: 640px) {
            .form-container {
                max-width: 90%;
            }
        }

        @media (min-width: 768px) {
            .form-container {
                max-width: 28rem;
            }
        }

        .form-header {
            background-color: #00796b; /* Material Green 700 */
            padding: 1.25rem;
            text-align: center;
        }

        .form {
            padding: 1.5rem;
        }

        .input-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-field {
            width: 100%;
            padding: 1rem 1rem 1rem 3.5rem;
            border: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            background-color: rgba(255, 255, 255, 0.05);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
            border-radius: 4px 4px 0 0;
        }

        .input-field:focus {
            border-bottom: 2px solid #4CAF50; /* Material Green */
            box-shadow: 0 1px 0 0 #4CAF50;
            outline: none;
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-weight: 300;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.7);
        }

        .login-btn {
            background-color: #4CAF50; /* Material Green */
            color: white;
            border: none;
            border-radius: 4px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-btn:hover {
            background-color: #43A047; /* Material Green 600 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-1px);
        }

        .login-btn:active {
            background-color: #388E3C; /* Material Green 700 */
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
            transform: translateY(1px);
        }

        .separator {
            display: flex;
            align-items: center;
            text-align: center;
            color: rgba(255, 255, 255, 0.6);
            margin: 1.5rem 0;
        }

        .separator::before,
        .separator::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .separator:not(:empty)::before {
            margin-right: 1rem;
        }

        .separator:not(:empty)::after {
            margin-left: 1rem;
        }

        .text-link {
            color: #81C784; /* Material Green 300 */
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .text-link:hover {
            color: #A5D6A7; /* Material Green 200 */
            text-decoration: underline;
        }

        /* Smooth hover effect for navigation links */
        .nav-link {
            position: relative;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            padding: 0.5rem 0;
            font-weight: 500;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: #A5D6A7; /* Material Green 200 */
            transition: width 0.3s;
        }

        .nav-link:hover {
            color: #A5D6A7; /* Material Green 200 */
        }

        .nav-link:hover::after {
            width: 100%;
        }

        /* Error message styling */
        .error-message {
            background-color: rgba(244, 67, 54, 0.1); /* Material Red with transparency */
            border-left: 4px solid #F44336; /* Material Red */
            color: #FFCDD2; /* Material Red 100 */
            border-radius: 4px;
            padding: 0.75rem 1rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .error-icon {
            margin-right: 0.75rem;
            color: #EF5350; /* Material Red 400 */
        }

        /* Ripple effect */
        .ripple {
            position: relative;
            overflow: hidden;
        }

        .ripple:after {
            content: "";
            display: block;
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            background-image: radial-gradient(circle, #fff 10%, transparent 10.01%);
            background-repeat: no-repeat;
            background-position: 50%;
            transform: scale(10, 10);
            opacity: 0;
            transition: transform .5s, opacity 1s;
        }

        .ripple:active:after {
            transform: scale(0, 0);
            opacity: .3;
            transition: 0s;
        }

        /* Elevate card on hover */
        .form-container {
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.5);
        }

        /* University badge style */
        .university-badge {
            position: absolute;
            top: -15px;
            right: 20px;
            background-color: #00796b; /* Material Green 700 */
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            z-index: 20;
        }

        /* Mobile menu styles */
        .mobile-menu {
            display: none;
            position: fixed;
            top: 4rem;
            left: 0;
            right: 0;
            background-color: rgba(0, 77, 64, 0.95);
            padding: 1rem;
            z-index: 40;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .mobile-menu.active {
            display: block;
        }

        .mobile-menu a {
            display: block;
            padding: 0.75rem 1rem;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-weight: 500;
        }

        .mobile-menu a:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
    <!-- Particles.js Container -->
    <div id="particles-js"></div>
<div id="lia-overlay" class="fixed inset-0 bg-black bg-opacity-80 z-50 hidden flex items-center justify-center">
    <div id="lia-content" class="bg-gradient-to-r from-green-800 to-green-600 p-8 rounded-lg max-w-3xl mx-4 transform translate-x-full transition-transform duration-700 ease-out">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-white">Laboratorio de Innovación y Acceso</h2>
            <button id="close-lia" class="text-white hover:text-green-200 transition-colors">
                <i class="material-icons">close</i>
            </button>
        </div>
        <div class="space-y-4">
            <h3 class="text-xl md:text-2xl font-medium text-green-100">Impulsando la Innovación y el Acceso a Recursos</h3>
            <p class="text-white text-opacity-90">
                Bienvenido al Laboratorio de Innovación, un espacio dedicado a facilitar el acceso a recursos, 
                promover la innovación y crear una comunidad colaborativa.
            </p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
                    <i class="material-icons text-green-300 text-3xl mb-2">lightbulb</i>
                    <h4 class="text-lg font-medium text-white mb-2">Innovación</h4>
                    <p class="text-white text-opacity-80 text-sm">Explora nuevas tecnologías y metodologías en un entorno de aprendizaje activo.</p>
                </div>
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
                    <i class="material-icons text-green-300 text-3xl mb-2">group</i>
                    <h4 class="text-lg font-medium text-white mb-2">Colaboración</h4>
                    <p class="text-white text-opacity-80 text-sm">Conecta con estudiantes y profesionales para desarrollar proyectos colaborativos.</p>
                </div>
                <div class="bg-white bg-opacity-10 p-4 rounded-lg">
                    <i class="material-icons text-green-300 text-3xl mb-2">school</i>
                    <h4 class="text-lg font-medium text-white mb-2">Recursos</h4>
                    <p class="text-white text-opacity-80 text-sm">Accede a equipos, software y mentorías para fortalecer tu formación académica.</p>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Navigation Bar -->
    <nav class="navbar fixed w-full top-0 py-3 px-4 md:px-6 flex justify-between items-center z-10">
        <div class="flex items-center">
            <a href="pantallaPrincipal.php" class="text-xl md:text-2xl font-medium text-white flex items-center">
                <img src="../public/img/logo Santo Tomas.jpg" alt="Logo" class="w-8 h-8 md:w-10 md:h-10 mr-2 md:mr-3 rounded-md">
                <span class="hidden sm:inline">IPC Santo Tomás</span>
            </a>
        </div>
        <div class="hidden md:flex items-center space-x-6 lg:space-x-8">
            <a href="#" class="nav-link">Inicio</a>
            <a href="#" class="nav-link">LIA</a>
            <a href="https://www.santotomas.cl/" class="nav-link">Santo Tomás</a>
        </div>
        <!-- Mobile menu button -->
        <div class="md:hidden">
            <button id="mobile-menu-button" class="text-white focus:outline-none p-2">
                <i class="material-icons">menu</i>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="mobile-menu">
        <a href="#" class="block">Inicio</a>
        <a href="#" class="block">LIA</a>
        <a href="https://www.santotomas.cl/" class="block">Santo Tomás</a>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen flex justify-center items-center px-4 pt-20 pb-8">
        <div class="form-container w-full my-4 md:my-8 relative">
            
            <!-- Form Header -->
            <div class="form-header">
                <img src="../public/img/Lia.jpg" alt="Logo" class="w-16 h-16 md:w-20 md:h-20 mx-auto rounded-full border-4 border-white border-opacity-20">
                <h2 class="text-xl md:text-2xl font-medium mt-3 md:mt-4">Bienvenido al Sistema</h2>
                <p class="text-green-100 text-opacity-80 mt-1 md:mt-2 text-sm md:text-base">Ingresa tus credenciales institucionales</p>
            </div>

            <!-- Form Body -->
            <div class="form">
                <!-- Error message placeholder -->
                <?php if (!empty($error)): ?>
                    <div class="error-message">
                        <i class="material-icons error-icon">error_outline</i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="input-group">
                        <i class="material-icons input-icon">email</i>
                        <input 
                            class="input-field" 
                            type="email" 
                            name="correo" 
                            id="correo" 
                            placeholder="Correo electrónico institucional" 
                            required 
                            autocomplete="email"
                            value="<?php echo isset($_POST['correo']) ? htmlspecialchars($_POST['correo']) : ''; ?>"
                        >
                    </div>

                    <div class="input-group">
                        <i class="material-icons input-icon">lock</i>
                        <input 
                            class="input-field" 
                            type="password" 
                            name="password" 
                            id="contraseña" 
                            placeholder="Contraseña" 
                            required
                            autocomplete="current-password"
                        >
                    </div>

                    <button type="submit" name="iniciar_sesion" class="login-btn ripple">
                        Iniciar Sesión 
                        <i class="material-icons ml-2">arrow_forward</i>
                    </button>
                </form>

                <!-- Links -->
                <div class="text-center mt-5 md:mt-6 space-y-2">
                    <p class="text-gray-300 text-sm md:text-base">
                        ¿Eres nuevo en el sistema? 
                        <a href="/proyectolia/final1.0/views/registro.php" class="text-link">Solicitar acceso</a>
                    </p>
                    <p class="text-gray-300 text-sm md:text-base">
                        <a href="../views/olvideContraseña.php" class="text-link">¿Olvidaste tu contraseña?</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="py-3 md:py-4 px-4 md:px-6 text-center text-gray-400 text-xs md:text-sm bg-gray-900 bg-opacity-70">
        <p>&copy; 2025 Universidad. Todos los derechos reservados.</p>
    </footer>

    <script>
        // Initialize particles.js
        document.addEventListener('DOMContentLoaded', function() {
            particlesJS("particles-js", {
                "particles": {
                    "number": {
                        "value": 50, // Reduced for mobile performance
                        "density": {
                            "enable": true,
                            "value_area": 800
                        }
                    },
                    "color": {
                        "value": "#4CAF50" // Material Green
                    },
                    "shape": {
                        "type": "circle",
                        "stroke": {
                            "width": 0,
                            "color": "#000000"
                        },
                        "polygon": {
                            "nb_sides": 5
                        }
                    },
                    "opacity": {
                        "value": 0.5,
                        "random": false,
                        "anim": {
                            "enable": false,
                            "speed": 1,
                            "opacity_min": 0.1,
                            "sync": false
                        }
                    },
                    "size": {
                        "value": 3,
                        "random": true,
                        "anim": {
                            "enable": false,
                            "speed": 40,
                            "size_min": 0.1,
                            "sync": false
                        }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#81C784", // Material Green 300
                        "opacity": 0.4,
                        "width": 1
                    },
                    "move": {
                        "enable": true,
                        "speed": 2,
                        "direction": "none",
                        "random": false,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false,
                        "attract": {
                            "enable": false,
                            "rotateX": 600,
                            "rotateY": 1200
                        }
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": {
                            "enable": true,
                            "mode": "grab"
                        },
                        "onclick": {
                            "enable": true,
                            "mode": "push"
                        },
                        "resize": true
                    },
                    "modes": {
                        "grab": {
                            "distance": 140,
                            "line_linked": {
                                "opacity": 1
                            }
                        },
                        "bubble": {
                            "distance": 400,
                            "size": 40,
                            "duration": 2,
                            "opacity": 8,
                            "speed": 3
                        },
                        "repulse": {
                            "distance": 200,
                            "duration": 0.4
                        },
                        "push": {
                            "particles_nb": 4
                        },
                        "remove": {
                            "particles_nb": 2
                        }
                    }
                },
                "retina_detect": true
            });

            // Focus on email input when page loads
            document.getElementById('correo').focus();
            
            // Material Design ripple effect for buttons
            const buttons = document.querySelectorAll('.ripple');
            buttons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    let x = e.clientX - e.target.offsetLeft;
                    let y = e.clientY - e.target.offsetTop;
                    
                    let ripple = document.createElement('span');
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(function() {
                        ripple.remove();
                    }, 600);
                });
            });

            // Mobile menu toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('active');
                const icon = mobileMenuButton.querySelector('i');
                if (mobileMenu.classList.contains('active')) {
                    icon.textContent = 'close';
                } else {
                    icon.textContent = 'menu';
                }
            });

            // Close mobile menu on window resize if it gets to desktop size
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768 && mobileMenu.classList.contains('active')) {
                    mobileMenu.classList.remove('active');
                    mobileMenuButton.querySelector('i').textContent = 'menu';
                }
            });

            // Detect if user is on a mobile device or smaller screen to optimize particles
            function optimizeForDevice() {
                if (window.innerWidth < 768) {
                    // Reduce particles for better performance on mobile
                    if (typeof pJSDom !== 'undefined' && pJSDom.length > 0) {
                        pJSDom[0].pJS.particles.number.value = 30;
                        pJSDom[0].pJS.particles.move.speed = 1;
                        pJSDom[0].pJS.fn.particlesRefresh();
                    }
                }
            }
            
            // Call once on load
            optimizeForDevice();
            
            // Also call on resize
            window.addEventListener('resize', optimizeForDevice);

            // Agregar manejo para el enlace LIA
            const liaLinks = document.querySelectorAll('a[href="#"].nav-link');
            liaLinks.forEach(link => {
                if (link.textContent === 'LIA') {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const liaOverlay = document.getElementById('lia-overlay');
                        const liaContent = document.getElementById('lia-content');
                        
                        // Mostrar el overlay
                        liaOverlay.classList.remove('hidden');
                        
                        // Animar la entrada del contenido
                        setTimeout(() => {
                            liaContent.classList.remove('translate-x-full');
                            liaContent.classList.add('translate-x-0');
                        }, 50);
                    });
                }
            });
            
            // Cerrar el overlay LIA
            document.getElementById('close-lia').addEventListener('click', function() {
                const liaOverlay = document.getElementById('lia-overlay');
                const liaContent = document.getElementById('lia-content');
                
                // Animar la salida del contenido
                liaContent.classList.remove('translate-x-0');
                liaContent.classList.add('translate-x-full');
                
                // Ocultar el overlay después de la animación
                setTimeout(() => {
                    liaOverlay.classList.add('hidden');
                }, 700);
            });
            
            // También cerrar haciendo clic fuera del contenido
            document.getElementById('lia-overlay').addEventListener('click', function(e) {
                if (e.target === this) {
                    const liaContent = document.getElementById('lia-content');
                    liaContent.classList.remove('translate-x-0');
                    liaContent.classList.add('translate-x-full');
                    
                    setTimeout(() => {
                        this.classList.add('hidden');
                    }, 700);
                }
            });
            
            // El enlace LIA en el menú móvil también debe activar el overlay
            const mobileLinks = mobileMenu.querySelectorAll('a');
            
            mobileLinks.forEach(link => {
                if (link.textContent === 'LIA') {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        
                        // Cerrar el menú móvil
                        mobileMenu.classList.remove('active');
                        document.getElementById('mobile-menu-button').querySelector('i').textContent = 'menu';
                        
                        // Mostrar el overlay de LIA
                        const liaOverlay = document.getElementById('lia-overlay');
                        const liaContent = document.getElementById('lia-content');
                        
                        liaOverlay.classList.remove('hidden');
                        
                        setTimeout(() => {
                            liaContent.classList.remove('translate-x-full');
                            liaContent.classList.add('translate-x-0');
                        }, 50);
                    });
                }
            });
        });
    </script>
</body>
</html>