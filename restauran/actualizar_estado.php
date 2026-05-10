<?php
require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: panel.php");
    exit;
}

$id_pedido = isset($_POST["id_pedido"]) ? (int) $_POST["id_pedido"] : 0;
$estado = $_POST["estado"] ?? "";

$estadosPermitidos = ["pendiente", "preparando", "servido", "pagado"];

if ($id_pedido <= 0 || !in_array($estado, $estadosPermitidos)) {
    header("Location: panel.php");
    exit;
}

$stmt = $conexion->prepare("UPDATE pedidos SET estado = ? WHERE id_pedido = ?");
$stmt->bind_param("si", $estado, $id_pedido);
$stmt->execute();

if ($estado === "pagado") {
    $stmtMesa = $conexion->prepare("UPDATE mesas 
        SET estado = 'libre' 
        WHERE id_mesa = (SELECT id_mesa FROM pedidos WHERE id_pedido = ?)");
    $stmtMesa->bind_param("i", $id_pedido);
    $stmtMesa->execute();
}

$conexion->close();

header("Location: panel.php");
exit;
?>
