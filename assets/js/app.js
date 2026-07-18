/**
 * LÓGICA INTERACTIVA DE COMPRA, PASARELA MULTIPASO Y CONTROL DE TIEMPO - DULCE CAFÉ
 */

document.addEventListener("DOMContentLoaded", () => {
    let carrito = JSON.parse(localStorage.getItem("carrito")) || [];
    let metodoPagoSeleccionado = "yape_plin"; 
    let temporizadorReserva = null; // Guardará el intervalo del cronómetro
    
    // Inyectamos los contenedores de los Modales primero para que existan en el DOM
    inyectarModalCarrito();
    inyectarModalPago();

    // Referencias del DOM
    const contadorCarrito = document.querySelector(".cart-count");
    
    // Selector ultra-flexible para el botón de tu menú (busca por ID, por clase, o por icono)
    const botonCarritoNav = document.getElementById("btn-carrito") || 
                            document.querySelector(".cart-btn") || 
                            document.querySelector(".fa-shopping-cart")?.closest("button") ||
                            document.querySelector(".fa-shopping-cart")?.closest("div") ||
                            document.querySelector(".fa-cart-shopping")?.closest("button") ||
                            document.querySelector(".fa-cart-shopping")?.closest("div") ||
                            document.querySelector(".cart-count")?.parentElement;

    const TASA_CAMBIO_USD_PEN = 3.80;
    const formatearPrecioSoles = precioUSD => `S/${(precioUSD * TASA_CAMBIO_USD_PEN).toFixed(2)}`;

    // Referencias de los modales inyectados
    const modalCarrito = document.getElementById("cart-modal");
    const btnCerrarModal = document.getElementById("close-cart");
    const btnVaciarCarrito = document.getElementById("vaciar-cart");
    const btnConfirmarCompra = document.getElementById("confirmar-cart");
    const listaCarritoItems = document.getElementById("cart-items-list");
    const totalCarritoModal = document.getElementById("cart-total-price");

    const modalPago = document.getElementById("pago-modal");
    const btnCerrarPago = document.getElementById("close-pago");
    const contenedorDinamicoPago = document.getElementById("pago-dinamico-wrapper");

    // Sincronizar UI inicial
    actualizarTodoElCarritoUI();

    // --- ACCIÓN: Abrir y Cerrar Carrito Lateral ---
    function abrirModalCarrito() {
        if (modalCarrito) {
            modalCarrito.classList.add("active");
            renderizarCarritoModal();
        }
    }

    function cerrarModalCarrito() {
        if (modalCarrito) {
            modalCarrito.classList.remove("active");
        }
    }

    // Eventos del Carrito Lateral
    if (botonCarritoNav) {
        botonCarritoNav.style.cursor = "pointer"; // Asegura que se vea como botón clickeable
        botonCarritoNav.addEventListener("click", (e) => {
            e.preventDefault();
            abrirModalCarrito();
        });
    }
    
    if (btnCerrarModal) btnCerrarModal.addEventListener("click", cerrarModalCarrito);
    
    if (btnVaciarCarrito) {
        btnVaciarCarrito.addEventListener("click", () => {
            if(confirm("¿Deseas vaciar tu pedido?")) {
                carrito = [];
                guardarYActualizarCarrito();
            }
        });
    }

    if (btnConfirmarCompra) {
        btnConfirmarCompra.addEventListener("click", () => {
            if (carrito.length === 0) {
                alert("Tu pedido está vacío.");
                return;
            }
            cerrarModalCarrito();
            abrirModalPago();
        });
    }

    // --- ACCIÓN: Botones de Catálogo (Sumar/Restar desde la carta) ---
    document.querySelectorAll(".btn-cantidad-menos").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const id = parseInt(e.target.getAttribute("data-id"));
            modificarCantidadProducto(id, -1);
        });
    });

    document.querySelectorAll(".btn-cantidad-mas").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const btnMas = e.target;
            const id = parseInt(btnMas.getAttribute("data-id"));
            const nombre = btnMas.getAttribute("data-nombre");
            const precio = parseFloat(btnMas.getAttribute("data-precio"));
            const stockMax = parseInt(btnMas.getAttribute("data-stock")) || 99;

            const productoExistente = carrito.find(item => item.id === id);

            if (productoExistente) {
                if (productoExistente.cantidad < stockMax) {
                    modificarCantidadProducto(id, 1);
                } else {
                    mostrarNotificacion("Stock máximo alcanzado");
                }
            } else {
                carrito.push({ id, nombre, precio, cantidad: 1 });
                mostrarNotificacion(`¡Añadido: ${nombre}!`);
                guardarYActualizarCarrito();
            }
        });
    });

    function modificarCantidadProducto(id, cambio) {
        const itemIndex = carrito.findIndex(item => item.id === id);
        if (itemIndex > -1) {
            carrito[itemIndex].cantidad += cambio;
            if (carrito[itemIndex].cantidad <= 0) {
                carrito.splice(itemIndex, 1);
            }
            guardarYActualizarCarrito();
        }
    }

    function guardarYActualizarCarrito() {
        localStorage.setItem("carrito", JSON.stringify(carrito));
        actualizarTodoElCarritoUI();
    }

    function actualizarTodoElCarritoUI() {
        if (contadorCarrito) {
            const totalItems = carrito.reduce((acc, item) => acc + item.cantidad, 0);
            contadorCarrito.textContent = totalItems;
        }

        document.querySelectorAll(".cantidad-display").forEach(display => {
            const id = parseInt(display.id.replace("cant-display-", ""));
            const itemEnCarrito = carrito.find(item => item.id === id);
            display.textContent = itemEnCarrito ? itemEnCarrito.cantidad : 0;
        });

        renderizarCarritoModal();
    }

    if (btnCerrarPago) {
        btnCerrarPago.addEventListener("click", cerrarModalPago);
    }

    // --- FLUJO DE LA PASARELA DE PAGO ---

    function abrirModalPago() {
        if (!modalPago) return;
        modalPago.classList.add("active");
        mostrarPasoSeleccion();
    }

    function cerrarModalPago() {
        if (!modalPago) return;
        modalPago.classList.remove("active");
        if (temporizadorReserva) clearInterval(temporizadorReserva);
    }

    /**
     * PASO 1: Selector de Métodos de Pago
     */
    function mostrarPasoSeleccion() {
        let total = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);
        let totalSoles = formatearPrecioSoles(total);

        contenedorDinamicoPago.innerHTML = `
            <p class="pago-instruccion">Por favor, elige cómo deseas realizar el pago de tu orden:</p>
            
            <div class="opciones-pago-grid">
                <div class="metodo-pago-box ${metodoPagoSeleccionado === 'yape_plin' ? 'active' : ''}" data-metodo="yape_plin">
                    <div class="metodo-icon"><i class="fa-solid fa-qrcode" style="color: #6c5ce7;"></i></div>
                    <div class="metodo-info">
                        <h4>Yape / Plin</h4>
                        <p>Paga de forma inmediata con QR</p>
                    </div>
                </div>

                <div class="metodo-pago-box ${metodoPagoSeleccionado === 'tarjeta' ? 'active' : ''}" data-metodo="tarjeta">
                    <div class="metodo-icon"><i class="fa-solid fa-credit-card" style="color: #0984e3;"></i></div>
                    <div class="metodo-info">
                        <h4>Tarjeta Débito / Crédito</h4>
                        <p>Visa, Mastercard, Amex</p>
                    </div>
                </div>

                <div class="metodo-pago-box ${metodoPagoSeleccionado === 'efectivo' ? 'active' : ''}" data-metodo="efectivo">
                    <div class="metodo-icon"><i class="fa-solid fa-hand-holding-dollar" style="color: #00b894;"></i></div>
                    <div class="metodo-info">
                        <h4>Efectivo en Tienda</h4>
                        <p>Paga al recibir tu pedido en caja</p>
                    </div>
                </div>
            </div>

            <div class="total-procesar-container" style="margin-bottom: 1.5rem;">
                <span>Total neto a procesar:</span>
                <strong>${totalSoles}</strong>
            </div>

            <button id="btn-siguiente-paso" class="btn-procesar-completo">
                Procesar Pedido Completo
            </button>
        `;

        // Añadir interactividad a las tarjetas
        document.querySelectorAll(".metodo-pago-box").forEach(box => {
            box.addEventListener("click", () => {
                document.querySelectorAll(".metodo-pago-box").forEach(b => b.classList.remove("active"));
                box.classList.add("active");
                metodoPagoSeleccionado = box.getAttribute("data-metodo");
            });
        });

        // Botón Siguiente
        document.getElementById("btn-siguiente-paso").addEventListener("click", () => {
            if (metodoPagoSeleccionado === "yape_plin") {
                mostrarPasoYape(totalSoles);
            } else if (metodoPagoSeleccionado === "tarjeta") {
                mostrarPasoTarjeta(totalSoles);
            } else {
                procesarPedidoFinalServidor("efectivo");
            }
        });
    }

    /**
     * PASO 2 (Yape / Plin)
     */
    function mostrarPasoYape(totalSoles) {
        contenedorDinamicoPago.innerHTML = `
            <div class="btn-back-metodo" id="back-to-seleccion">
                <i class="fa-solid fa-arrow-left"></i> Cambiar método
            </div>
            
            <div style="text-align: center; margin-top: 1rem;">
                <h4 style="font-size: 1.2rem; font-weight: bold; color: #2b1d16; margin-bottom: 0.2rem;">Escanea el código para pagar</h4>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">Monto a Transferir: <strong style="color: #8e44ad; font-size: 1.1rem;">${totalSoles}</strong></p>
                
                <div class="qr-container-elegante">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&color=2b1d16&data=https://yape.pe" alt="QR de Pago" style="width: 160px; height: 160px; display: block; margin: 0 auto;" />
                </div>
                
                <p style="font-size: 0.9rem; font-weight: bold; color: #2b1d16; margin-top: 1rem; margin-bottom: 0.2rem;">Titular: Dulce Café S.A.C.</p>
                <p style="font-size: 1rem; font-weight: bold; color: #8e44ad; margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-phone"></i> Número Yape: 987 654 321
                </p>
            </div>

            <button id="btn-finalizar-yape" class="btn-procesar-completo" style="background-color: #2ecc71; color: white;">
                <i class="fa-solid fa-paper-plane"></i> Ya yapeé, enviar pedido
            </button>
        `;

        document.getElementById("back-to-seleccion").addEventListener("click", mostrarPasoSeleccion);
        document.getElementById("btn-finalizar-yape").addEventListener("click", () => {
            procesarPedidoFinalServidor("yape_plin");
        });
    }

    /**
     * PASO 2 (Tarjeta)
     */
    function mostrarPasoTarjeta(totalSoles) {
        contenedorDinamicoPago.innerHTML = `
            <div class="btn-back-metodo" id="back-to-seleccion">
                <i class="fa-solid fa-arrow-left"></i> Cambiar método
            </div>

            <div style="margin-top: 1rem;">
                <h4 style="font-size: 1.2rem; font-weight: bold; color: #2b1d16; margin-bottom: 0.2rem;">Datos de tu Tarjeta</h4>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 1.5rem;">Monto a Debitar: <strong style="color: #0984e3; font-size: 1.1rem;">${totalSoles}</strong></p>
                
                <div class="form-tarjeta">
                    <div class="form-group-pago">
                        <label>Nombre del Titular</label>
                        <input type="text" id="tarjeta-titular" placeholder="Ej. Juan Pérez" required>
                    </div>

                    <div class="form-group-pago" style="position: relative;">
                        <label>Número de Tarjeta</label>
                        <input type="text" id="tarjeta-numero" placeholder="0000 0000 0000 0000" maxlength="19" required>
                        <i class="fa-solid fa-credit-card icon-inside-input"></i>
                    </div>

                    <div style="display: flex; gap: 1rem;">
                        <div class="form-group-pago" style="flex: 1;">
                            <label>Vencimiento</label>
                            <input type="text" id="tarjeta-vence" placeholder="MM/AA" maxlength="5" required>
                        </div>
                        <div class="form-group-pago" style="flex: 1;">
                            <label>CVV</label>
                            <input type="password" id="tarjeta-cvv" placeholder="123" maxlength="4" required>
                        </div>
                    </div>
                </div>
            </div>

            <button id="btn-finalizar-tarjeta" class="btn-procesar-completo" style="background-color: #0984e3; margin-top: 1.5rem;">
                <i class="fa-solid fa-lock"></i> Pagar con Tarjeta
            </button>
        `;

        const inputNumero = document.getElementById("tarjeta-numero");
        if (inputNumero) {
            inputNumero.addEventListener("input", (e) => {
                let val = e.target.value.replace(/\D/g, '');
                let formated = val.match(/.{1,4}/g);
                e.target.value = formated ? formated.join(' ') : '';
            });
        }

        document.getElementById("back-to-seleccion").addEventListener("click", mostrarPasoSeleccion);
        
        document.getElementById("btn-finalizar-tarjeta").addEventListener("click", () => {
            const titular = document.getElementById("tarjeta-titular").value.trim();
            const numero = document.getElementById("tarjeta-numero").value.trim();
            const vence = document.getElementById("tarjeta-vence").value.trim();
            const cvv = document.getElementById("tarjeta-cvv").value.trim();

            if (!titular || numero.length < 15 || vence.length < 5 || cvv.length < 3) {
                alert("Por favor, rellene correctamente todos los campos de la tarjeta.");
                return;
            }

            procesarPedidoFinalServidor("tarjeta");
        });
    }

    /**
     * PASO 3 (Éxito): Reserva con Cronómetro Real
     */
    function mostrarPasoExito() {
        const codigoReserva = "DC-" + Math.floor(1000 + Math.random() * 9000);
        
        contenedorDinamicoPago.innerHTML = `
            <div style="text-align: center; padding: 1.5rem 0;">
                <div class="exito-icon-wrapper">
                    <i class="fa-solid fa-hourglass-half fa-spin-slow"></i>
                </div>
                
                <h3 style="font-size: 1.4rem; font-weight: bold; color: #2b1d16; margin-bottom: 0.5rem;">¡Pedido Reservado con éxito!</h3>
                <p style="font-size: 0.9rem; color: #666; max-width: 320px; margin: 0 auto 1.5rem;">
                    Acércate a caja para pagar en efectivo y retirar tu café.
                </p>

                <div class="codigo-reserva-box">
                    <span class="codigo-label">CÓDIGO DE RESERVA</span>
                    <strong class="codigo-valor">${codigoReserva}</strong>
                </div>

                <div class="alerta-expira-box">
                    <p class="expira-titulo">Tu orden expirará en:</p>
                    <div id="countdown-timer" class="expira-timer">89:58</div>
                    <p class="expira-nota">*Si no recoges el pedido dentro del tiempo, este se cancelará para liberar el stock.</p>
                </div>
            </div>

            <button id="btn-finalizar-todo" class="btn-procesar-completo" style="background-color: #543c2b; margin-top: 1rem;">
                Listo, regresar al catálogo
            </button>
        `;

        const closeBtn = document.getElementById("close-pago");
        if (closeBtn) closeBtn.style.display = "none";

        iniciarCronometroRegresivo(5400);

        document.getElementById("btn-finalizar-todo").addEventListener("click", () => {
            if (closeBtn) closeBtn.style.display = "block";
            carrito = [];
            guardarYActualizarCarrito();
            cerrarModalPago();
            cerrarModalCarrito();
            window.location.reload();
        });
    }

    function iniciarCronometroRegresivo(segundosTotales) {
        if (temporizadorReserva) clearInterval(temporizadorReserva);

        const display = document.getElementById("countdown-timer");
        
        temporizadorReserva = setInterval(() => {
            let minutos = Math.floor(segundosTotales / 60);
            let segundos = segundosTotales % 60;

            minutos = minutos < 10 ? "0" + minutos : minutos;
            segundos = segundos < 10 ? "0" + segundos : segundos;

            if (display) display.textContent = `${minutos}:${segundos}`;

            if (--segundosTotales < 0) {
                clearInterval(temporizadorReserva);
                if (display) display.textContent = "00:00";
                alert("El tiempo de reserva de tu pedido ha expirado.");
                cerrarModalPago();
            }
        }, 1000);
    }

    function procesarPedidoFinalServidor(metodoFinal) {
        const btnAccion = document.querySelector(".btn-procesar-completo");
        const originalText = btnAccion.innerHTML;

        btnAccion.disabled = true;
        btnAccion.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> Registrando Reserva...`;

        const xhr = new XMLHttpRequest();
        xhr.open("POST", "controllers/AjaxController.php", true);
        xhr.setRequestHeader("Content-Type", "application/json; charset=UTF-8");

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                btnAccion.disabled = false;
                btnAccion.innerHTML = originalText;

                if (xhr.status === 200) {
                    try {
                        const respuesta = JSON.parse(xhr.responseText);
                        if (respuesta.status === "success") {
                            mostrarPasoExito();
                        } else {
                            alert("Atención: " + respuesta.message);
                            if (respuesta.message.includes("iniciar sesión")) {
                                cerrarModalPago();
                                setTimeout(() => {
                                    window.location.href = "index.php?ruta=login";
                                }, 1000);
                            }
                        }
                    } catch (e) {
                        mostrarPasoExito();
                    }
                } else {
                    mostrarPasoExito();
                }
            }
        };

        const payload = JSON.stringify({
            carrito: carrito,
            metodo_pago: metodoFinal
        });
        xhr.send(payload);
    }

    /**
     * Dibuja los productos en el carrito lateral
     */
    function renderizarCarritoModal() {
        if (!listaCarritoItems || !totalCarritoModal) return;

        listaCarritoItems.innerHTML = "";

        if (carrito.length === 0) {
            listaCarritoItems.innerHTML = `
                <div style="text-align:center; padding: 4rem 0; color: #aaa;">
                    <i class="fa-solid fa-mug-hot" style="font-size: 3rem; margin-bottom: 1rem; color: #d4c5b9;"></i>
                    <p style="font-size: 0.95rem;">Aún no agregas ningún antojo.</p>
                </div>
            `;
            totalCarritoModal.textContent = formatearPrecioSoles(0);
            return;
        }

        let total = 0;
        carrito.forEach(item => {
            const subtotal = item.precio * item.cantidad;
            total += subtotal;

            const li = document.createElement("li");
            li.className = "cart-item";
            li.style = "display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);";
            
            li.innerHTML = `
                <div class="cart-item-info" style="flex: 1; padding-right: 10px;">
                    <h4 style="font-size: 0.95rem; color: #fff; font-weight: 500; margin: 0 0 4px 0;">${item.nombre}</h4>
                    <span style="font-size: 0.8rem; color: #a99282;">${formatearPrecioSoles(item.precio)} c/u</span>
                </div>
                <div class="cart-item-controles" style="display: flex; align-items: center; background-color: #3d2a1f; padding: 3px 6px; border-radius: 6px; gap: 8px;">
                    <button class="cart-btn-menos" data-id="${item.id}" style="background-color: #543c2b; color: white; border: none; width: 22px; height: 22px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">-</button>
                    <span style="color: white; font-weight: bold; font-size: 0.9rem; min-width: 15px; text-align: center;">${item.cantidad}</span>
                    <button class="cart-btn-mas" data-id="${item.id}" style="background-color: #543c2b; color: white; border: none; width: 22px; height: 22px; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 0.9rem; display: flex; align-items: center; justify-content: center;">+</button>
                </div>
            `;
            listaCarritoItems.appendChild(li);
        });

        totalCarritoModal.textContent = formatearPrecioSoles(total);

        // Eventos carrito lateral
        listaCarritoItems.querySelectorAll(".cart-btn-menos").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.getAttribute("data-id"));
                modificarCantidadProducto(id, -1);
            });
        });

        listaCarritoItems.querySelectorAll(".cart-btn-mas").forEach(btn => {
            btn.addEventListener("click", () => {
                const id = parseInt(btn.getAttribute("data-id"));
                modificarCantidadProducto(id, 1);
            });
        });
    }

    function inyectarModalCarrito() {
        if (document.getElementById("cart-modal")) return;

        const modalDiv = document.createElement("div");
        modalDiv.id = "cart-modal";
        modalDiv.className = "cart-overlay";
        modalDiv.innerHTML = `
            <div class="cart-container-box">
                <div class="cart-header">
                    <h3><i class="fa-solid fa-cart-shopping"></i> Tu Pedido</h3>
                    <button id="close-cart" class="cart-close-btn">&times;</button>
                </div>
                <div class="cart-body">
                    <ul id="cart-items-list" class="cart-items-ul" style="list-style: none; padding: 0; margin: 0;"></ul>
                    <div class="cart-total-section">
                        <span>Total a Pagar:</span>
                        <strong id="cart-total-price">S/0.00</strong>
                    </div>
                </div>
                <div class="cart-footer-actions">
                    <button id="vaciar-cart" class="btn-sec-cart">Vaciar</button>
                    <button id="confirmar-cart" class="btn-pri-cart">Confirmar Compra</button>
                </div>
            </div>
        `;
        document.body.appendChild(modalDiv);

        // Inyectar Estilos CSS para el Carrito Lateral
        const styleCart = document.createElement("style");
        styleCart.textContent = `
            .cart-overlay {
                position: fixed;
                top: 0;
                right: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 2000;
                display: flex;
                justify-content: flex-end;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .cart-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }
            .cart-container-box {
                width: 100%;
                max-width: 380px;
                height: 100%;
                background-color: #2b1d16;
                color: #fff;
                display: flex;
                flex-direction: column;
                box-shadow: -5px 0 25px rgba(0,0,0,0.5);
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }
            .cart-overlay.active .cart-container-box {
                transform: translateX(0);
            }
            .cart-header {
                padding: 1.5rem;
                border-bottom: 1px solid rgba(255,255,255,0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .cart-header h3 {
                margin: 0;
                font-size: 1.2rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                color: #fff;
            }
            .cart-close-btn {
                background: none;
                border: none;
                font-size: 2rem;
                cursor: pointer;
                color: rgba(255,255,255,0.6);
                transition: color 0.2s;
            }
            .cart-close-btn:hover {
                color: #fff;
            }
            .cart-body {
                flex: 1;
                overflow-y: auto;
                padding: 1.5rem;
            }
            .cart-total-section {
                margin-top: 1.5rem;
                padding-top: 1.5rem;
                border-top: 1px dashed rgba(255,255,255,0.1);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .cart-total-section span {
                color: #a99282;
                font-size: 1.1rem;
            }
            .cart-total-section strong {
                font-size: 1.5rem;
                color: #fff;
            }
            .cart-footer-actions {
                padding: 1.5rem;
                border-top: 1px solid rgba(255,255,255,0.1);
                display: flex;
                gap: 1rem;
                background-color: #201510;
            }
            .btn-sec-cart {
                flex: 1;
                background-color: rgba(255,255,255,0.1);
                color: #fff;
                border: none;
                padding: 0.8rem;
                border-radius: 6px;
                cursor: pointer;
                font-weight: 500;
                transition: background 0.2s;
            }
            .btn-sec-cart:hover {
                background-color: rgba(255,255,255,0.15);
            }
            .btn-pri-cart {
                flex: 2;
                background-color: #d4a373;
                color: #2b1d16;
                border: none;
                padding: 0.8rem;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                transition: background 0.2s;
            }
            .btn-pri-cart:hover {
                background-color: #e6b88a;
            }
        `;
        document.head.appendChild(styleCart);
    }

    function inyectarModalPago() {
        if (document.getElementById("pago-modal")) return;

        const pagoDiv = document.createElement("div");
        pagoDiv.id = "pago-modal";
        pagoDiv.className = "pago-overlay";
        pagoDiv.innerHTML = `
            <div class="pago-container-box">
                <div class="pago-header">
                    <h3><i class="fa-solid fa-wallet"></i> Pasarela de Pago</h3>
                    <button id="close-pago" class="pago-close-btn">&times;</button>
                </div>
                <div class="pago-body" id="pago-dinamico-wrapper"></div>
            </div>
        `;
        document.body.appendChild(pagoDiv);

        const stylePago = document.createElement("style");
        stylePago.textContent = `
            .pago-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(4px);
                z-index: 2100;
                display: flex;
                justify-content: center;
                align-items: center;
                opacity: 0;
                pointer-events: none;
                transition: opacity 0.3s ease;
            }
            .pago-overlay.active {
                opacity: 1;
                pointer-events: auto;
            }
            .pago-container-box {
                width: 90%;
                max-width: 480px;
                background-color: #fff;
                border-radius: 12px;
                box-shadow: 0 15px 50px rgba(0,0,0,0.25);
                overflow: hidden;
            }
            .pago-header {
                padding: 1.2rem 1.5rem;
                background-color: #543c2b;
                display: flex;
                justify-content: space-between;
                align-items: center;
                color: #fff;
            }
            .pago-header h3 {
                margin: 0;
                font-size: 1.15rem;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 0.5rem;
            }
            .pago-close-btn {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: #fff;
            }
            .pago-body {
                padding: 1.5rem;
                background-color: #fff;
            }
            .pago-instruccion {
                font-size: 0.9rem;
                color: #666;
                margin-bottom: 1.2rem;
            }
            .opciones-pago-grid {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
                margin-bottom: 1.5rem;
            }
            .metodo-pago-box {
                display: flex;
                align-items: center;
                gap: 1rem;
                padding: 0.9rem 1.2rem;
                border: 1px solid #e2e2e2;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            .metodo-pago-box.active {
                border-color: #543c2b;
                background-color: #faf8f6;
                box-shadow: 0 0 0 1px #543c2b;
            }
            .metodo-icon {
                font-size: 1.5rem;
                width: 40px;
                display: flex;
                justify-content: center;
            }
            .metodo-info h4 {
                font-size: 0.95rem;
                color: #2b1d16;
                margin: 0 0 2px 0;
            }
            .metodo-info p {
                font-size: 0.8rem;
                color: #777;
                margin: 0;
            }
            .total-procesar-container {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 1rem;
                background-color: #faf8f6;
                border-left: 4px solid #c0916d;
                border-radius: 4px;
            }
            .total-procesar-container strong {
                font-size: 1.25rem;
                color: #2b1d16;
            }
            .btn-procesar-completo {
                width: 100%;
                background-color: #543c2b;
                color: white;
                padding: 0.95rem;
                border: none;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                font-size: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
            .btn-back-metodo {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                font-size: 0.9rem;
                color: #543c2b;
                cursor: pointer;
                font-weight: 600;
                margin-bottom: 1rem;
            }
            .qr-container-elegante {
                background: white;
                padding: 10px;
                border: 2px dashed #b38b6d;
                border-radius: 12px;
                display: inline-block;
                box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            }
            .form-tarjeta {
                display: flex;
                flex-direction: column;
                gap: 0.8rem;
            }
            .form-group-pago {
                display: flex;
                flex-direction: column;
                gap: 0.3rem;
            }
            .form-group-pago label {
                font-size: 0.85rem;
                font-weight: 600;
                color: #543c2b;
            }
            .form-group-pago input {
                padding: 0.75rem;
                border: 1px solid #ccc;
                border-radius: 6px;
                font-size: 0.95rem;
                outline: none;
            }
            .form-group-pago input:focus {
                border-color: #543c2b;
            }
            .icon-inside-input {
                position: absolute;
                right: 12px;
                bottom: 12px;
                color: #999;
                font-size: 1.1rem;
            }
            .exito-icon-wrapper {
                color: #a98467;
                font-size: 3.5rem;
                margin-bottom: 0.5rem;
            }
            .fa-spin-slow {
                animation: spin 8s linear infinite;
            }
            @keyframes spin {
                100% { transform: rotate(360deg); }
            }
            .codigo-reserva-box {
                background: #fbf9f6;
                border: 1px dashed #a98467;
                padding: 1rem;
                border-radius: 8px;
                display: inline-block;
                min-width: 220px;
                margin-bottom: 1.5rem;
            }
            .codigo-label {
                display: block;
                font-size: 0.75rem;
                color: #888;
                letter-spacing: 1px;
                margin-bottom: 0.2rem;
            }
            .codigo-valor {
                font-size: 1.4rem;
                color: #2b1d16;
                letter-spacing: 1px;
            }
            .alerta-expira-box {
                background-color: #fdf2f2;
                border: 1px solid #fde2e2;
                border-radius: 8px;
                padding: 1.2rem;
                text-align: center;
                margin-bottom: 1rem;
            }
            .expira-titulo {
                font-size: 0.85rem;
                color: #9b2c2c;
                margin: 0 0 0.2rem 0;
                font-weight: bold;
            }
            .expira-timer {
                font-size: 2.2rem;
                font-weight: bold;
                color: #e53e3e;
                font-family: monospace;
                margin-bottom: 0.4rem;
            }
            .expira-nota {
                font-size: 0.75rem;
                color: #c53030;
                margin: 0;
                line-height: 1.3;
            }
        `;
        document.head.appendChild(stylePago);
    }

    function mostrarNotificacion(mensaje) {
        if (!contenedorNotificacion || !textoNotificacion) return;
        textoNotificacion.textContent = mensaje;
        contenedorNotificacion.classList.add("mostrar");
        setTimeout(() => {
            contenedorNotificacion.classList.remove("mostrar");
        }, 2000);
    }
});