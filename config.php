<?php
session_start();


$servidor = "localhost";
$nombreBd = "CoffeeYU";
$usuario = "root";
$pass = "";


$conn = new mysqli($servidor, $usuario, $pass, $nombreBd);


if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}


$conn->set_charset("utf8mb4");

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header("Location: index.php");
        exit();
    }
}

function cleanInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}


define('SITE_NAME', 'CoffeeYU');
define('SITE_URL', 'http://localhost/coffeeyu/');
?>