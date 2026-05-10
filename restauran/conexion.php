<?php
$host = "localhost";
$usuario = "root";
$password = "";
$base_datos = "buen_provecho";

$conexion = new mysqli($host, $usuario, $password, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");

function limpiar($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}
?>
