<?php
require_once "conexion.php";

$sqlPedidos = "SELECT p.id_pedido, p.estado, p.total, p.fecha, m.nombre AS mesa
               FROM pedidos p
               INNER JOIN mesas m ON p.id_mesa = m.id_mesa
               WHERE p.estado != 'pagado'
               ORDER BY p.fecha DESC";

$pedidos = $conexion->query($sqlPedidos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="10">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Pedidos</title>
    <link rel="stylesheet" href="css/panel.css">
</head>
<body>
    <header>
        <h1>📋 Panel de Pedidos</h1>
        <p>Se actualiza automáticamente cada 10 segundos.</p>
    </header>

    <main>
        <?php if ($pedidos && $pedidos->num_rows > 0): ?>
            <div class="pedidos-grid">
                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                    <article class="pedido">
                        <div class="pedido-top">
                            <div>
                                <h2><?= limpiar($pedido['mesa']) ?></h2>

                                <p class="hora-pedido">
                                    Pedido recibido:
                                    <?= date("H:i", strtotime($pedido['fecha'])) ?>
                                </p>
                            </div>

                            <span class="estado <?= limpiar($pedido['estado']) ?>">
                                <?= limpiar($pedido['estado']) ?>
                            </span>
                        </div>

                        <p><strong>Total:</strong> Gs. <?= number_format($pedido['total'], 0, ',', '.') ?></p>
                        
                        <h3>Productos</h3>
                        <ul>
                            <?php
                            $stmtDetalles = $conexion->prepare("SELECT d.cantidad, d.subtotal, pr.nombre
                                                                FROM detalle_pedido d
                                                                INNER JOIN productos pr ON d.id_producto = pr.id_producto
                                                                WHERE d.id_pedido = ?");
                            $stmtDetalles->bind_param("i", $pedido['id_pedido']);
                            $stmtDetalles->execute();
                            $detalles = $stmtDetalles->get_result();

                            while ($detalle = $detalles->fetch_assoc()):
                            ?>
                                <li>
                                    <?= limpiar($detalle['cantidad']) ?> x
                                    <?= limpiar($detalle['nombre']) ?>
                                    <strong>Gs. <?= number_format($detalle['subtotal'], 0, ',', '.') ?></strong>
                                </li>
                            <?php endwhile; ?>
                        </ul>

                        <form action="actualizar_estado.php" method="POST" class="acciones">
                            <input type="hidden" name="id_pedido" value="<?= limpiar($pedido['id_pedido']) ?>">
                            <button name="estado" value="preparando">Preparando</button>
                            <button name="estado" value="servido">Servido</button>
                            <button name="estado" value="pagado">Pagado</button>
                        </form>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="vacio">No hay pedidos pendientes.</div>
        <?php endif; ?>
    </main>
</body>
</html>
<?php $conexion->close(); ?>
