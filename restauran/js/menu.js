let carrito = [];
let categoriaActual = 'todos';

function mostrarSeccion(id, boton) {
    document.querySelectorAll('.seccion').forEach(seccion => {
        seccion.classList.remove('activa');
    });

    document.getElementById(id).classList.add('activa');

    document.querySelectorAll('.nav-secciones button').forEach(btn => {
        btn.classList.remove('activo');
    });

    boton.classList.add('activo');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function filtrarCategoria(categoria, boton) {
    categoriaActual = categoria;

    document.querySelectorAll('.filtro').forEach(btn => {
        btn.classList.remove('activo');
    });

    boton.classList.add('activo');
    filtrarProductos();
}

function filtrarProductos() {
    const texto = document.getElementById('buscadorMenu').value.toLowerCase().trim();
    let totalVisibles = 0;

    document.querySelectorAll('.categoria-bloque').forEach(bloque => {
        let productosVisiblesEnBloque = 0;

        bloque.querySelectorAll('.producto').forEach(producto => {
            const nombre = producto.dataset.nombre;
            const categoriaProducto = producto.dataset.categoria;

            const coincideNombre = nombre.includes(texto);
            const coincideCategoria = categoriaActual === 'todos' || categoriaProducto === categoriaActual;

            if (coincideNombre && coincideCategoria) {
                producto.style.display = 'block';
                productosVisiblesEnBloque++;
                totalVisibles++;
            } else {
                producto.style.display = 'none';
            }
        });

        bloque.style.display = productosVisiblesEnBloque > 0 ? 'block' : 'none';
    });

    document.getElementById('mensajeSinResultados').style.display = totalVisibles === 0 ? 'block' : 'none';
}

function agregarAlCarrito(id, nombre, precio) {
    const producto = carrito.find(item => item.id === id);

    if (producto) {
        producto.cantidad++;
    } else {
        carrito.push({ id, nombre, precio, cantidad: 1 });
    }

    actualizarCarrito();
}

function actualizarCarrito() {
    const cantidad = carrito.reduce((acc, item) => acc + item.cantidad, 0);
    const total = carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0);

    document.getElementById('cantidadCarrito').textContent = cantidad;
    document.getElementById('totalCarrito').textContent = total.toLocaleString('es-PY');
}

function verPedido() {
    if (carrito.length === 0) {
        alert('Todavía no agregaste productos.');
        return;
    }

    let resumen = `Pedido de Mesa ${idMesa}\n\n`;

    carrito.forEach(item => {
        resumen += `${item.cantidad} x ${item.nombre} - Gs. ${(item.precio * item.cantidad).toLocaleString('es-PY')}\n`;
    });

    const total = carrito.reduce((acc, item) => acc + item.precio * item.cantidad, 0);
    resumen += `\nTotal: Gs. ${total.toLocaleString('es-PY')}`;

    alert(resumen);
}

async function enviarPedido() {
    if (carrito.length === 0) {
        alert('Agrega productos antes de enviar.');
        return;
    }

    const confirmar = confirm('¿Enviar pedido al restaurante?');

    if (!confirmar) return;

    try {
        const respuesta = await fetch('guardar_pedido.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_mesa: idMesa,
                carrito: carrito
            })
        });

        const resultado = await respuesta.json();

        if (resultado.ok) {
            alert('Pedido enviado correctamente. Número de pedido: ' + resultado.id_pedido);
            carrito = [];
            actualizarCarrito();
        } else {
            alert(resultado.mensaje || 'No se pudo enviar el pedido.');
        }
    } catch (error) {
        alert('Error de conexión al enviar el pedido.');
    }
}
