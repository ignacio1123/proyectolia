<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header("Location: login.php");
    exit;
}

echo "<pre>";
print_r($_SESSION['usuario']);
echo "</pre>";
?>