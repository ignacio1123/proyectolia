<?php
require_once 'db_connection.php';
session_start();

$id_usuario = $_SESSION['id_usuario'];

$sql = "SELECT s.id_solicitud, u.nombre, s.nombre_proyecto, s.estado, s.acotaciones 
        FROM solicitudes s
        INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
        WHERE s.id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$estados = [];
while ($avance = $resultado->fetch_assoc()) {
    $estados[] = $avance;
}
header('Content-Type: application/json');
echo json_encode($estados);
