<?php
session_start();

// Validar que el usuario haya iniciado sesión en XAMPP
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html?error=acceso_denegado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Huevos Frescos - Catálogo de Productos</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        /* --- BARRA DE NAVEGACIÓN --- */
        nav {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 80px;
            height: 100px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 18px;
            text-decoration: none;
        }

        .logo-area img {
            height: 70px;
            width: auto;
            object-fit: contain;
        }

        .logo-texto {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .logo-texto .titulo-logo {
            font-size: 30px;
            font-weight: 800;
            color: #1e3a1e;
        }

        .logo-texto .subtitulo-logo {
            font-size: 26px;
            font-weight: 700;
            color: #16a34a;
        }

        .nav-links {
            display: flex;
            gap: 30px;
            list-style: none;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #334155;
            font-size: 18px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: #d97706;
            border-bottom: 3px solid #d97706;
            padding-bottom: 6px;
        }

        .btn-logout {
            background-color: #ef4444;
            color: white !important;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            border-bottom: none !important;
        }

        .btn-logout:hover {
            background-color: #dc2626;
        }

        .carrito-btn {
            background-color: #fef3c7;
            color: #d97706;
            border: 1px solid #fcd34d;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 800;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
        }

        .carrito-btn:hover {
            background-color: #fde68a;
        }

        .badge-cart-count {
            background-color: #ea580c;
            color: white;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 14px;
        }

        /* --- CATÁLOGO DE PRODUCTOS --- */
        .container {
            max-width: 1500px;
            margin: 0 auto;
            padding: 60px 50px 120px 50px;
        }

        .section-title {
            font-size: 48px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 10px;
            text-align: center;
        }

        .welcome-msg {
            text-align: center;
            font-size: 20px;
            color: #16a34a;
            font-weight: 700;
            margin-bottom: 40px;
        }

        .productos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 45px;
        }

        .producto-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(0,0,0,0.06);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .producto-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.15);
        }

        .producto-img-container {
            width: 100%;
            height: 320px; 
            background-color: #f1f5f9;
            overflow: hidden;
        }

        .producto-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .producto-info {
            padding: 35px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .producto-titulo {
            font-weight: 800;
            font-size: 26px;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .producto-precio {
            font-size: 34px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 16px;
        }

        .badge-limite {
            background-color: #fef3c7;
            color: #92400e;
            font-size: 16px;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            margin-bottom: 25px;
            display: inline-block;
        }

        .btn-anadir {
            width: 100%;
            background-color: #16a34a;
            color: #ffffff;
            border: none;
            padding: 18px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .btn-anadir:hover {
            background-color: #15803d;
        }

        .btn-anadir.naranja {
            background-color: #ea580c;
        }

        .btn-anadir.naranja:hover {
            background-color: #c2410c;
        }

        /* --- MODAL DE CARRITO --- */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background: white;
            padding: 35px;
            border-radius: 16px;
            width: 90%;
            max-width: 550px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 15px;
        }

        .modal-header h2 {
            font-size: 26px;
            color: #0f172a;
        }

        .close-modal {
            font-size: 28px;
            font-weight: bold;
            color: #64748b;
            cursor: pointer;
            border: none;
            background: none;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 18px;
        }

        .cart-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cart-btn-qty {
            background: #e2e8f0;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 18px;
            cursor: pointer;
        }

        .cart-total {
            margin-top: 25px;
            font-size: 22px;
            font-weight: 800;
            display: flex;
            justify-content: space-between;
            color: #0f172a;
        }

        .limite-alerta {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
            padding: 10px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 14px;
            text-align: center;
            font-weight: 600;
        }

        .btn-checkout {
            width: 100%;
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 10px;
            font-size: 20px;
            font-weight: 800;
            margin-top: 20px;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-checkout:hover {
            background-color: #15803d;
        }

        @media (max-width: 1100px) {
            .productos-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 700px) {
            .productos-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA SUPERIOR -->
    <nav>
        <a href="productos.php" class="logo-area">
            <img src="img/logo-huevos.png" alt="Logo Granja">
            <div class="logo-texto">
                <span class="titulo-logo">Huevos</span>
                <span class="subtitulo-logo">Frescos</span>
            </div>
        </a>

        <ul class="nav-links">
            <li><a href="productos.php" class="active">Nuestros Huevos</a></li>
            <li>
                <button class="carrito-link carrito-btn" onclick="abrirCarrito()">
                    🛒 Carrito <span class="badge-cart-count" id="cart-count">0</span>
                </button>
            </li>
            <li><a href="auth/logout.php" class="btn-logout">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- CATÁLOGO -->
    <div class="container">
        <h1 class="section-title">Nuestros Cartones Disponibles</h1>
        <p class="welcome-msg">Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Cliente'); ?></p>
        
        <div class="productos-grid">
            
            <!-- PRODUCTO 1: EXTRA GRANDE -->
            <div class="producto-card">
                <div class="producto-img-container">
                    <img src="img/carton-extragrande.jpg" alt="Cartón Extra Grande">
                </div>
                <div class="producto-info">
                    <div class="producto-titulo">Extra Grande (30 Huevos)</div>
                    <div class="producto-precio">$5.50</div>
                    <span class="badge-limite">Máximo 3 al día</span>
                    <button class="btn-anadir" onclick="agregarAlCarrito('Extra Grande', 5.50, 3)">Añadir al carrito</button>
                </div>
            </div>

            <!-- PRODUCTO 2: GRANDE -->
            <div class="producto-card">
                <div class="producto-img-container">
                    <img src="img/carton-grande.jpg" alt="Cartón Grande">
                </div>
                <div class="producto-info">
                    <div class="producto-titulo">Grande (30 Huevos)</div>
                    <div class="producto-precio">$4.50</div>
                    <span class="badge-limite">Máximo 5 al día</span>
                    <button class="btn-anadir" onclick="agregarAlCarrito('Grande', 4.50, 5)">Añadir al carrito</button>
                </div>
            </div>

            <!-- PRODUCTO 3: PEQUEÑO -->
            <div class="producto-card">
                <div class="producto-img-container">
                    <img src="img/carton-pequeno.jpg" alt="Cartón Pequeño">
                </div>
                <div class="producto-info">
                    <div class="producto-titulo">Pequeño (30 Huevos)</div>
                    <div class="producto-precio">$3.50</div>
                    <span class="badge-limite">Máximo 5 al día</span>
                    <button class="btn-anadir naranja" onclick="agregarAlCarrito('Pequeño', 3.50, 5)">Añadir al carrito</button>
                </div>
            </div>

            <!-- PRODUCTO 4: CHIQUITO -->
            <div class="producto-card">
                <div class="producto-img-container">
                    <img src="img/carton-chiquito.jpg" alt="Cartón Chiquito">
                </div>
                <div class="producto-info">
                    <div class="producto-titulo">Chiquito (30 Huevos)</div>
                    <div class="producto-precio">$3.00</div>
                    <span class="badge-limite">Máximo 1 al día</span>
                    <button class="btn-anadir naranja" onclick="agregarAlCarrito('Chiquito', 3.00, 1)">Añadir al carrito</button>
                </div>
            </div>

            <!-- PRODUCTO 5: JUMBO -->
            <div class="producto-card">
                <div class="producto-img-container">
                    <img src="img/carton-jumbo.jpg" alt="Cartón Jumbo">
                </div>
                <div class="producto-info">
                    <div class="producto-titulo">Jumbo (30 Huevos)</div>
                    <div class="producto-precio">$5.75</div>
                    <span class="badge-limite">Máximo 1 al día</span>
                    <button class="btn-anadir" onclick="agregarAlCarrito('Jumbo', 5.75, 1)">Añadir al carrito</button>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL VENTANA DEL CARRITO -->
    <div class="modal-overlay" id="modalCarrito">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Tu Carrito de Compras</h2>
                <button class="close-modal" onclick="cerrarCarrito()">&times;</button>
            </div>
            <div id="cart-items-container">
                <p style="text-align: center; color: #64748b; font-size: 18px;">El carrito está vacío.</p>
            </div>
            <div class="limite-alerta">
                ⚠️ Límite total de la tienda: <strong>Máximo 5 cartones por cliente</strong>.
            </div>
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total-price">$0.00</span>
            </div>
            <button class="btn-checkout" onclick="confirmarPedido()">Confirmar Pedido</button>
        </div>
    </div>

    <!-- SCRIPT DE LÓGICA DE NEGOCIO -->
    <script>
        let carrito = {};
        const MAX_CARTONES_TOTAL = 5;

        function obtenerTotalCartones() {
            let total = 0;
            for (let p in carrito) {
                total += carrito[p].cantidad;
            }
            return total;
        }

        function agregarAlCarrito(nombre, precio, limiteIndividual) {
            let totalActual = obtenerTotalCartones();

            if (totalActual >= MAX_CARTONES_TOTAL) {
                alert(`⚠️ Has alcanzado el límite máximo total de ${MAX_CARTONES_TOTAL} cartones por cliente.`);
                return;
            }

            if (!carrito[nombre]) {
                carrito[nombre] = {
                    precio: precio,
                    cantidad: 0,
                    limiteIndividual: limiteIndividual
                };
            }

            if (carrito[nombre].cantidad >= limiteIndividual) {
                alert(`⚠️ Solo puedes agregar un máximo de ${limiteIndividual} cartón(es) de tipo ${nombre} por día.`);
                return;
            }

            carrito[nombre].cantidad++;
            actualizarInterfaz();
        }

        function cambiarCantidad(nombre, cambio) {
            if (!carrito[nombre]) return;

            let totalActual = obtenerTotalCartones();

            if (cambio > 0) {
                if (totalActual >= MAX_CARTONES_TOTAL) {
                    alert(`⚠️ Has alcanzado el límite máximo total de ${MAX_CARTONES_TOTAL} cartones por cliente.`);
                    return;
                }
                if (carrito[nombre].cantidad >= carrito[nombre].limiteIndividual) {
                    alert(`⚠️ Máximo ${carrito[nombre].limiteIndividual} cartón(es) para ${nombre}.`);
                    return;
                }
            }

            carrito[nombre].cantidad += cambio;

            if (carrito[nombre].cantidad <= 0) {
                delete carrito[nombre];
            }

            actualizarInterfaz();
        }

        function actualizarInterfaz() {
            let totalCartones = obtenerTotalCartones();
            document.getElementById('cart-count').innerText = totalCartones;

            let container = document.getElementById('cart-items-container');
            let totalPrecio = 0;

            if (totalCartones === 0) {
                container.innerHTML = '<p style="text-align: center; color: #64748b; font-size: 18px;">El carrito está vacío.</p>';
            } else {
                let html = '';
                for (let p in carrito) {
                    let subtotal = carrito[p].cantidad * carrito[p].precio;
                    totalPrecio += subtotal;
                    html += `
                        <div class="cart-item">
                            <div>
                                <strong>${p}</strong><br>
                                <small style="color:#64748b;">$${carrito[p].precio.toFixed(2)} c/u</small>
                            </div>
                            <div class="cart-controls">
                                <button class="cart-btn-qty" onclick="cambiarCantidad('${p}', -1)">-</button>
                                <span>${carrito[p].cantidad}</span>
                                <button class="cart-btn-qty" onclick="cambiarCantidad('${p}', 1)">+</button>
                            </div>
                        </div>
                    `;
                }
                container.innerHTML = html;
            }

            document.getElementById('cart-total-price').innerText = `$${totalPrecio.toFixed(2)}`;
        }

        function abrirCarrito() {
            document.getElementById('modalCarrito').style.display = 'flex';
        }

        function cerrarCarrito() {
            document.getElementById('modalCarrito').style.display = 'none';
        }

        function confirmarPedido() {
            let total = obtenerTotalCartones();
            if (total === 0) {
                alert("Tu carrito está vacío. Agrega productos antes de confirmar.");
                return;
            }
            alert(`¡Gracias por tu pedido! Has reservado ${total} cartón(es). Nos pondremos en contacto para coordinar la entrega.`);
            carrito = {};
            actualizarInterfaz();
            cerrarCarrito();
        }
    </script>
</body>
</html>