<?php
session_start();

function checkIfLoggedIn() {

    if (!isset($_SESSION['user_id'])) {

        header("Location: login.php"); 
        exit(); 
    }
}
?>
