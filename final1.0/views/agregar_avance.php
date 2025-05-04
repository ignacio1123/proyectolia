<?php
require_once 'db_connection.php';
session_start();

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: No se ha identificado al usuario. Por favor, inicia sesión.";
    header("Location: login.php");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre_documento = $_POST['nombre_documento'];
    

    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
   
        $directorio = '../uploads/documentos/'; 
        $archivo = $_FILES['archivo'];

 
        $tipo_archivo = pathinfo($archivo['name'], PATHINFO_EXTENSION);
        if ($tipo_archivo == 'docx' || $tipo_archivo == 'doc') {
            $nombre_archivo = uniqid() . '.' . $tipo_archivo; 
            $ruta_archivo = $directorio . $nombre_archivo;


            if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {

                $sql = "INSERT INTO avances_proyectos (id_usuario, nombre_documento, archivo) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);

                if ($stmt) {
                    $stmt->bind_param("iss", $id_usuario, $nombre_documento, $ruta_archivo);
                    if ($stmt->execute()) {
                        echo "Avance agregado exitosamente.";
                    } else {
                        echo "Error al agregar el avance.";
                    }
                } else {
                    echo "Error en la consulta preparada: " . $conn->error;
                }
            } else {
                echo "Error al subir el archivo.";
            }
        } else {
            echo "Solo se permiten archivos Word (.docx, .doc).";
        }
    } else {
        echo "Error al cargar el archivo.";
    }
}
?>
