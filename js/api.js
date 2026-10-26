const API_URL = 'http://localhost/servidor-huevos/api/obtener_datos.php';

const catalogoLocal = [
    { nombre: 'Extra Grande', precio: 5.5, limite: 3, huevos: 30, imagen: 'carton-extragrande.jpg' },
    { nombre: 'Grande', precio: 4.5, limite: 5, huevos: 30, imagen: 'carton-grande.jpg' },
    { nombre: 'Pequeño', precio: 3.5, limite: 5, huevos: 30, imagen: 'carton-pequeno.jpg' },
    { nombre: 'Chiquito', precio: 3, limite: 1, huevos: 30, imagen: 'carton-chiquito.jpg' },
    { nombre: 'Jumbo', precio: 5.75, limite: 1, huevos: 30, imagen: 'carton-jumbo.jpg' }
];

const imagenesPorNombre = new Map(
    catalogoLocal.map(producto => [producto.nombre.toLocaleLowerCase('es'), producto.imagen])
);

function obtenerListaProductos(datos) {
    if (Array.isArray(datos)) {
        return datos;
    }

    if (Array.isArray(datos?.productos)) {
        return datos.productos;
    }

    if (Array.isArray(datos?.data)) {
        return datos.data;
    }

    if (Array.isArray(datos?.data?.productos)) {
        return datos.data.productos;
    }

    throw new Error('La respuesta de la API no contiene una lista de productos.');
}

function normalizarProductos(productos) {
    return productos.map(producto => {
        const nombre = producto.nombre_tipo ?? producto.nombre ?? producto.name ?? producto.tipo;
        const precio = Number(producto.precio_carton ?? producto.precio ?? producto.price);

        if (typeof nombre !== 'string' || !nombre.trim() || !Number.isFinite(precio) || precio < 0) {
            throw new Error('La API devolvió un producto con nombre o precio no válido.');
        }

        const stockEntrada = producto.stock_disponible
            ?? producto.stock
            ?? producto.existencia
            ?? producto.disponibles;
        const stock = stockEntrada === undefined || stockEntrada === null ? null : Number(stockEntrada);
        const limiteEntrada = producto.limite_diario ?? producto.limite;
        const limite = Number(limiteEntrada);
        const huevosEntrada = producto.huevos_por_carton ?? producto.huevos;
        const huevos = Number(huevosEntrada);
        const nombreLimpio = nombre.trim();

        return {
            nombre: nombreLimpio,
            precio,
            stock: Number.isInteger(stock) && stock >= 0 ? stock : null,
            limite: Number.isInteger(limite) && limite > 0 ? limite : 5,
            huevos: Number.isInteger(huevos) && huevos > 0 ? huevos : null,
            imagen: imagenesPorNombre.get(nombreLimpio.toLocaleLowerCase('es')) ?? 'logo-huevos.png'
        };
    });
}

function crearElemento(tag, clase, texto) {
    const elemento = document.createElement(tag);
    if (clase) {
        elemento.className = clase;
    }
    if (texto !== undefined) {
        elemento.textContent = texto;
    }
    return elemento;
}

function crearTarjeta(producto, esCatalogoPedidos) {
    const tarjeta = crearElemento('article', esCatalogoPedidos ? 'producto-card' : 'catalogo-card');
    const imagenContenedor = crearElemento(
        'div',
        esCatalogoPedidos ? 'producto-img-container' : ''
    );
    const imagen = document.createElement('img');
    imagen.src = `img/${producto.imagen}`;
    imagen.alt = `Cartón de huevos ${producto.nombre}`;
    imagen.loading = 'lazy';
    imagenContenedor.append(imagen);

    const informacion = crearElemento('div', esCatalogoPedidos ? 'producto-info' : 'catalogo-card-info');
    const nombre = producto.huevos
        ? `${producto.nombre} (${producto.huevos} Huevos)`
        : producto.nombre;
    informacion.append(
        crearElemento(esCatalogoPedidos ? 'div' : 'h3', esCatalogoPedidos ? 'producto-titulo' : '', nombre),
        crearElemento(
            esCatalogoPedidos ? 'div' : 'p',
            esCatalogoPedidos ? 'producto-precio' : 'catalogo-precio',
            `$${producto.precio.toFixed(2)}`
        ),
        crearElemento(
            esCatalogoPedidos ? 'span' : 'p',
            esCatalogoPedidos ? 'badge-limite' : '',
            producto.stock === null
                ? 'Disponibilidad: sin datos'
                : producto.stock === 0
                    ? 'Agotado'
                    : `${producto.stock} cartones disponibles`
        )
    );

    if (esCatalogoPedidos) {
        const boton = crearElemento(
            'button',
            `btn-anadir${producto.nombre === 'Pequeño' || producto.nombre === 'Chiquito' ? ' naranja' : ''}`,
            producto.stock === 0 ? 'Agotado' : 'Añadir al carrito'
        );
        boton.type = 'button';
        boton.disabled = producto.stock === 0;
        boton.addEventListener('click', () => {
            window.agregarAlCarrito(producto.nombre, producto.precio, producto.limite);
        });
        informacion.append(boton);
    } else {
        const enlace = crearElemento('a', 'catalogo-accion', 'Ver catálogo');
        enlace.href = 'productos.html';
        informacion.append(enlace);
    }

    tarjeta.append(imagenContenedor, informacion);
    return tarjeta;
}

function mostrarCatalogo(productos, usarDatosLocales) {
    document.querySelectorAll('[data-product-catalog]').forEach(contenedor => {
        const esCatalogoPedidos = contenedor.classList.contains('productos-grid');
        contenedor.replaceChildren(...productos.map(producto => crearTarjeta(producto, esCatalogoPedidos)));
    });

    document.querySelectorAll('[data-catalog-status]').forEach(aviso => {
        aviso.hidden = !usarDatosLocales;
        aviso.textContent = usarDatosLocales
            ? 'No se pudo conectar con el servidor. Cargando datos locales de la granja; la disponibilidad actual se confirmará al realizar el pedido.'
            : '';
    });
}

async function cargarCatalogo() {
    try {
        const respuesta = await fetch(API_URL, {
            headers: { Accept: 'application/json' },
            cache: 'no-store'
        });

        if (!respuesta.ok) {
            throw new Error(`La API respondió con estado ${respuesta.status}.`);
        }

        const datos = await respuesta.json();
        const productos = normalizarProductos(obtenerListaProductos(datos));
        mostrarCatalogo(productos, false);
    } catch (error) {
        console.error('No fue posible cargar el catálogo desde la API:', error);
        mostrarCatalogo(catalogoLocal.map(producto => ({ ...producto, stock: null })), true);
    }
}

document.addEventListener('DOMContentLoaded', cargarCatalogo);
