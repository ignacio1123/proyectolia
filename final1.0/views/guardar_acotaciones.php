<?php
require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_solicitud = $_POST['id_solicitud'] ?? null;
    $acotaciones = $_POST['acotaciones'] ?? null;

    if ($id_solicitud && $acotaciones) {
        $query = "UPDATE solicitudes SET acotaciones = ? WHERE id_solicitud = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $acotaciones, $id_solicitud);

        if ($stmt->execute()) {
            echo "Acotaciones guardadas exitosamente.";
            header("Location: pantallaDirector.php"); // Redirige a la página principal
        } else {
            echo "Error al guardar las acotaciones: " . $stmt->error;
        }
    } else {
        echo "Error: Datos incompletos.";
    }
}
?>