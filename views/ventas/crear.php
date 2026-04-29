<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

// Obtener productos disponibles
$stmtProd = $db->prepare("SELECT id_producto, nombre, precio, categoria, unidad_medida FROM productos ORDER BY categoria, nombre");
$stmtProd->execute();
$productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

// Obtener métodos de pago
$stmtPago = $db->prepare("SELECT id_metodo_pago, nombre FROM metodos_pago ORDER BY nombre");
$stmtPago->execute();
$metodosPago = $stmtPago->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Registrar Venta";
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Breadcrumb -->
<div class="flex items-center gap-2 mb-6 text-sm font-bold" style="color:#A87D5C;">
    <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php"
       class="no-underline" style="color:#A87D5C;"
       onmouseover="this.style.color='#F97316';" onmouseout="this.style.color='#A87D5C';">
        <i class="fas fa-home mr-1"></i> Dashboard
    </a>
    <span>/</span>
    <span style="color:#F97316;">Registrar Venta</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- ══ COLUMNA IZQUIERDA: Productos ══ -->
    <div class="lg:col-span-2 flex flex-col gap-5">

        <!-- Buscador -->
        <div class="bg-white rounded-2xl border p-5" style="border-color:#F3D5B5;">
            <h3 class="text-lg font-black mb-4" style="color:#1C0A00;">
                Selecciona <span style="color:#F97316;">Productos</span>
            </h3>
            <div class="relative mb-4">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                <input type="text" id="buscador" placeholder="Buscar producto..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-semibold outline-none"
                       style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                       onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';"
                       oninput="filtrarProductos(this.value)">
            </div>

            <!-- Grid de productos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="listaProductos">
                <?php if (empty($productos)): ?>
                    <p class="col-span-2 text-center text-sm font-bold py-6" style="color:#A87D5C;">
                        🍞 No hay productos registrados.
                    </p>
                <?php endif; ?>
                <?php foreach ($productos as $p): ?>
                <div class="producto-card border rounded-xl p-4 cursor-pointer transition-all select-none"
                     style="border-color:#F3D5B5;"
                     data-id="<?= $p['id_producto'] ?>"
                     data-nombre="<?= htmlspecialchars($p['nombre']) ?>"
                     data-precio="<?= $p['precio'] ?>"
                     data-categoria="<?= htmlspecialchars(strtolower($p['categoria'] ?? '')) ?>"
                     onclick="agregarProducto(this)"
                     onmouseover="this.style.borderColor='#F97316';this.style.background='#FFF7ED';"
                     onmouseout="this.style.borderColor='#F3D5B5';this.style.background='';">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-black text-sm" style="color:#1C0A00;"><?= htmlspecialchars($p['nombre']) ?></p>
                            <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">
                                <?= htmlspecialchars($p['categoria'] ?? '—') ?> · <?= htmlspecialchars($p['unidad_medida'] ?? '') ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-sm" style="color:#F97316;">
                                $<?= number_format($p['precio'], 0, ',', '.') ?>
                            </p>
                            <p class="text-xs font-bold mt-0.5" style="color:#A87D5C;">c/u</p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- ══ COLUMNA DERECHA: Resumen de venta ══ -->
    <div class="flex flex-col gap-5">

        <div class="bg-white rounded-2xl border" style="border-color:#F3D5B5;">

            <div class="px-5 py-4 border-b" style="border-color:#F3D5B5;">
                <h3 class="text-lg font-black" style="color:#1C0A00;">
                    🧾 Resumen de <span style="color:#F97316;">Venta</span>
                </h3>
            </div>

            <!-- Items seleccionados -->
            <div id="itemsVenta" class="px-5 py-4 flex flex-col gap-3 min-h-[160px]">
                <p id="emptyMsg" class="text-sm font-bold text-center py-6" style="color:#A87D5C;">
                    Haz clic en un producto para agregarlo
                </p>
            </div>

            <div class="px-5 pb-5 flex flex-col gap-4">

                <!-- Total -->
                <div class="flex items-center justify-between pt-3 border-t" style="border-color:#F3D5B5;">
                    <span class="font-black text-sm" style="color:#6B4F3A;">TOTAL</span>
                    <span class="font-black text-2xl" style="color:#F97316;" id="totalVenta">$0</span>
                </div>

                <!-- Método de pago -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Método de Pago</label>
                    <div class="relative">
                        <i class="fas fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-sm" style="color:#A87D5C;"></i>
                        <select id="metodoPago"
                                class="w-full pl-9 pr-4 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <?php foreach ($metodosPago as $mp): ?>
                                <option value="<?= $mp['id_metodo_pago'] ?>"><?= htmlspecialchars($mp['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Botón registrar -->
                <button onclick="confirmarVenta()"
                        class="w-full py-3 rounded-xl text-sm font-black text-white transition-all"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.35);"
                        onmouseover="this.style.background='#EA6A0A';"
                        onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-check mr-2"></i> Registrar Venta
                </button>

                <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php"
                   class="w-full py-2.5 rounded-xl text-sm font-black text-center no-underline transition-all block"
                   style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                   onmouseover="this.style.background='#F3D5B5';"
                   onmouseout="this.style.background='#FFF7ED';">
                    Cancelar
                </a>

            </div>
        </div>

    </div>
</div>

<!-- Form oculto para enviar -->
<form id="formVenta" action="../../controllers/VentaController.php" method="POST" style="display:none;">
    <input type="hidden" name="id_metodo_pago" id="inputMetodo">
    <input type="hidden" name="total"          id="inputTotal">
    <input type="hidden" name="items"          id="inputItems">
</form>

<script>
    let carrito = {}; // { id: { nombre, precio, cantidad } }

    function agregarProducto(el) {
        const id     = el.dataset.id;
        const nombre = el.dataset.nombre;
        const precio = parseFloat(el.dataset.precio);

        if (carrito[id]) {
            carrito[id].cantidad++;
        } else {
            carrito[id] = { nombre, precio, cantidad: 1 };
        }
        renderCarrito();
    }

    function cambiarCantidad(id, delta) {
        if (!carrito[id]) return;
        carrito[id].cantidad += delta;
        if (carrito[id].cantidad <= 0) delete carrito[id];
        renderCarrito();
    }

    function renderCarrito() {
        const container = document.getElementById('itemsVenta');
        const emptyMsg  = document.getElementById('emptyMsg');
        const keys      = Object.keys(carrito);

        if (keys.length === 0) {
            container.innerHTML = '<p id="emptyMsg" class="text-sm font-bold text-center py-6" style="color:#A87D5C;">Haz clic en un producto para agregarlo</p>';
            document.getElementById('totalVenta').textContent = '$0';
            return;
        }

        let html  = '';
        let total = 0;

        keys.forEach(id => {
            const item    = carrito[id];
            const subtotal = item.precio * item.cantidad;
            total += subtotal;
            html += `
            <div class="flex items-center justify-between gap-2 py-2 border-b" style="border-color:#F3D5B5;">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-black truncate" style="color:#1C0A00;">${item.nombre}</p>
                    <p class="text-xs font-semibold" style="color:#A87D5C;">$${formatNum(item.precio)} c/u</p>
                </div>
                <div class="flex items-center gap-1">
                    <button onclick="cambiarCantidad('${id}', -1)"
                            class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center border transition-all"
                            style="border-color:#F3D5B5;color:#6B4F3A;"
                            onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                            onmouseout="this.style.background='';this.style.color='#6B4F3A';">−</button>
                    <span class="w-6 text-center text-xs font-black" style="color:#1C0A00;">${item.cantidad}</span>
                    <button onclick="cambiarCantidad('${id}', 1)"
                            class="w-6 h-6 rounded-lg text-xs font-black flex items-center justify-center border transition-all"
                            style="border-color:#F3D5B5;color:#6B4F3A;"
                            onmouseover="this.style.background='#FFF7ED';this.style.color='#F97316';"
                            onmouseout="this.style.background='';this.style.color='#6B4F3A';">+</button>
                </div>
                <p class="text-xs font-black w-16 text-right" style="color:#F97316;">$${formatNum(subtotal)}</p>
            </div>`;
        });

        container.innerHTML = html;
        document.getElementById('totalVenta').textContent = '$' + formatNum(total);
    }

    function formatNum(n) {
        return new Intl.NumberFormat('es-CO').format(n);
    }

    function filtrarProductos(q) {
        document.querySelectorAll('.producto-card').forEach(card => {
            const nombre    = card.dataset.nombre.toLowerCase();
            const categoria = card.dataset.categoria;
            card.style.display = (nombre.includes(q.toLowerCase()) || categoria.includes(q.toLowerCase())) ? '' : 'none';
        });
    }

    function confirmarVenta() {
        const keys = Object.keys(carrito);
        if (keys.length === 0) {
            Swal.fire({ icon: 'warning', title: 'Carrito vacío', text: 'Agrega al menos un producto.', confirmButtonColor: '#F97316' });
            return;
        }
        const metodo = document.getElementById('metodoPago').value;
        if (!metodo) {
            Swal.fire({ icon: 'warning', title: 'Método de pago', text: 'Selecciona un método de pago.', confirmButtonColor: '#F97316' });
            return;
        }

        let total = 0;
        keys.forEach(id => { total += carrito[id].precio * carrito[id].cantidad; });

        const items = keys.map(id => ({
            id_producto:     id,
            cantidad:        carrito[id].cantidad,
            precio_unitario: carrito[id].precio,
            subtotal:        carrito[id].precio * carrito[id].cantidad
        }));

        Swal.fire({
            icon: 'question',
            title: '¿Confirmar venta?',
            text: `Total: $${formatNum(total)}`,
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText:  'Cancelar',
            confirmButtonColor: '#F97316'
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('inputMetodo').value = metodo;
                document.getElementById('inputTotal').value  = total;
                document.getElementById('inputItems').value  = JSON.stringify(items);
                document.getElementById('formVenta').submit();
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
