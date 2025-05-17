<?php
require_once 'db_connection.php';
$id = $_POST['id'];
$estado = $_POST['estado'];
$stmt = $conn->prepare("UPDATE dispositivos_faltante SET Ubicacion=? WHERE id_dispositivo=?");
$stmt->bind_param("si", $estado, $id);
$stmt->execute();
echo "ok";
?>