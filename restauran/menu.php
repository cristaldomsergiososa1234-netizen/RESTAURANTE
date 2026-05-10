<?php
require_once "conexion.php";

$id_mesa = isset($_GET['mesa']) ? (int) $_GET['mesa'] : 0;

$stmtMesa = $conexion->prepare("SELECT id_mesa, nombre, estado FROM mesas WHERE id_mesa = ?");
$stmtMesa->bind_param("i", $id_mesa);
$stmtMesa->execute();
$mesa = $stmtMesa->get_result()->fetch_assoc();

if (!$mesa) {
    die("Mesa no válida. Verifica el QR.");
}

$sqlProductos = "SELECT p.id_producto, p.nombre, p.precio, c.nombre AS categoria
                 FROM productos p
                 INNER JOIN categorias c ON p.id_categoria = c.id_categoria
                 ORDER BY c.id_categoria ASC, p.nombre ASC";

$resultadoProductos = $conexion->query($sqlProductos);
$productosPorCategoria = [];

while ($producto = $resultadoProductos->fetch_assoc()) {
    $productosPorCategoria[$producto['categoria']][] = $producto;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - Buen Provecho</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <header>
        <h1>🍽️ Buen Provecho</h1>
        <div class="mesa">Estás en: <?= limpiar($mesa['nombre']) ?></div>

        <div class="nav-secciones">
            <button class="activo" onclick="mostrarSeccion('info', this)">Info</button>
            <button onclick="mostrarSeccion('menu', this)">Menú</button>
            <button onclick="mostrarSeccion('contacto', this)">Contacto</button>
        </div>
    </header>

    <main>
        <section id="info" class="seccion activa">
            <h2 class="titulo-seccion">Sobre nosotros</h2>
            <p class="texto">Bienvenido a Buen Provecho. Mira el menú desde tu mesa y realiza tu pedido.</p>
            <div class="card">
                <div class="imagenes-info">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=900&q=80" alt="Restaurante">
                    <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=900&q=80" alt="Pizza">
                    <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=900&q=80" alt="Hamburguesa">
                </div>
            </div>
        </section>

        <section id="menu" class="seccion">
            <h2 class="titulo-seccion">Menú</h2>
            <p class="texto">Busca por nombre o filtra por categoría.</p>

            <div class="menu-controles">
                <input type="text" id="buscadorMenu" placeholder="🔍 Buscar producto..." oninput="filtrarProductos()">
                <div class="filtros-categorias">
                    <button class="filtro activo" onclick="filtrarCategoria('todos', this)">Todos</button>
                    <?php foreach (array_keys($productosPorCategoria) as $categoria): ?>
                        <button class="filtro" onclick="filtrarCategoria('<?= limpiar($categoria) ?>', this)">
                            <?= limpiar($categoria) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="mensajeSinResultados" class="sin-resultados" style="display:none;">No se encontraron productos.</div>

            <?php foreach ($productosPorCategoria as $nombreCategoria => $productos): ?>
                <div class="categoria-bloque" data-categoria="<?= limpiar($nombreCategoria) ?>">
                    <h3 class="categoria-titulo"><?= limpiar($nombreCategoria) ?></h3>
                    <div class="menu-grid">
                        <?php foreach ($productos as $producto): ?>
                            <article class="producto" data-nombre="<?= strtolower(limpiar($producto['nombre'])) ?>" data-categoria="<?= limpiar($nombreCategoria) ?>">
                                <h3><?= limpiar($producto['nombre']) ?></h3>
                                <div class="precio">Gs. <?= number_format($producto['precio'], 0, ',', '.') ?></div>
                                <button class="btn-agregar" onclick="agregarAlCarrito(<?= limpiar($producto['id_producto']) ?>, '<?= limpiar($producto['nombre']) ?>', <?= limpiar($producto['precio']) ?>)">
                                    Agregar
                                </button>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section id="contacto" class="seccion">
            <h2 class="titulo-seccion">Contacto</h2>
            <div class="contacto-grid">
                <div class="card contacto-card">
                    <h3>Datos del restaurante</h3>
                    <p>📍 Dirección: Av. Principal 123, Paraguay</p>
                    <p>📞 Teléfono: +595 981 000 000</p>
                    <p>🕒 Horario: Lunes a domingo, 10:00 a 23:00</p>
                    <div class="redes">
                        <a href="#">Facebook</a>
                        <a href="#">Instagram</a>
                        <a href="#">WhatsApp</a>
                    </div>
                </div>
                <div class="card contacto-card">
                    <h3>Ubicación</h3>
                    <div class="mapa">Aquí puedes insertar Google Maps.</div>
                </div>
            </div>
        </section>
    </main>

    <div class="carrito">
        <strong>🛒 Productos: <span id="cantidadCarrito">0</span></strong>
        <strong>Total: Gs. <span id="totalCarrito">0</span></strong>
        <button onclick="verPedido()">Ver</button>
        <button onclick="enviarPedido()">Enviar pedido</button>
    </div>

    <script>const idMesa = <?= json_encode($id_mesa) ?>;</script>
    <script src="js/menu.js"></script>
</body>
</html>
<?php $conexion->close(); ?>
