<?php
require_once "conexion.php";

header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["id_mesa"]) || !isset($data["carrito"])) {
    echo json_encode(["ok" => false, "mensaje" => "Datos incompletos"]);
    exit;
}

$id_mesa = (int) $data["id_mesa"];
$carrito = $data["carrito"];

if ($id_mesa <= 0 || count($carrito) === 0) {
    echo json_encode(["ok" => false, "mensaje" => "Pedido vacío"]);
    exit;
}

$total = 0;

foreach ($carrito as $item) {
    $total += ((int)$item["precio"]) * ((int)$item["cantidad"]);
}

$conexion->begin_transaction();

try {
    $stmtPedido = $conexion->prepare("INSERT INTO pedidos (id_mesa, estado, total) VALUES (?, 'pendiente', ?)");
    $stmtPedido->bind_param("ii", $id_mesa, $total);
    $stmtPedido->execute();

    $id_pedido = $conexion->insert_id;

    $stmtDetalle = $conexion->prepare("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");

    foreach ($carrito as $item) {
        $id_producto = (int) $item["id"];
        $cantidad = (int) $item["cantidad"];
        $precio_unitario = (int) $item["precio"];
        $subtotal = $cantidad * $precio_unitario;

        $stmtDetalle->bind_param("iiiii", $id_pedido, $id_producto, $cantidad, $precio_unitario, $subtotal);
        $stmtDetalle->execute();
    }

    $stmtMesa = $conexion->prepare("UPDATE mesas SET estado = 'ocupada' WHERE id_mesa = ?");
    $stmtMesa->bind_param("i", $id_mesa);
    $stmtMesa->execute();

    $conexion->commit();

    echo json_encode([
        "ok" => true,
        "mensaje" => "Pedido enviado correctamente",
        "id_pedido" => $id_pedido
    ]);
} catch (Exception $e) {
    $conexion->rollback();

    echo json_encode([
        "ok" => false,
        "mensaje" => "Error al guardar pedido"
    ]);
}

$conexion->close();
?>
