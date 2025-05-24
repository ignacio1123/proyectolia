<?php
require_once 'db_connection.php';

if (!isset($_GET['id'])) {
    die('ID de avance no especificado.');
}

$id_avance = intval($_GET['id']);

// Busca el avance en la base de datos
$sql = "SELECT nombre_documento, archivo FROM avances_proyectos WHERE id_avance = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_avance);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Avance no encontrado.');
}

$row = $result->fetch_assoc();
$nombre_documento = $row['nombre_documento'];
$ruta_archivo = $row['archivo'];

// Verifica que el archivo exista
if (!file_exists($ruta_archivo)) {
    die('Archivo no encontrado.');
}

// Obtiene la extensión original del archivo
$extension = pathinfo($ruta_archivo, PATHINFO_EXTENSION);

// Permite espacios y tildes, solo elimina caracteres peligrosos para nombres de archivo
$nombre_descarga = preg_replace('/[\/\\\\?%*:|"<>]/u', '', $nombre_documento) . '.' . $extension;

// Envía las cabeceras para forzar la descarga
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $nombre_descarga . '"');
header('Content-Length: ' . filesize($ruta_archivo));
readfile($ruta_archivo);
exit;