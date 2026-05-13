<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php");
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$database = new Database();
$db       = $database->conectar();

$stmt = $db->prepare("SELECT * FROM productos ORDER BY categoria, nombre");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titulo = "Productos";
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
    <span style="color:#F97316;">Productos</span>
</div>

<!-- Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-black" style="color:#1C0A00;">
            Gestión de <span style="color:#F97316;">Productos</span>
        </h2>
        <p class="text-sm font-semibold mt-1" style="color:#A87D5C;">
            <?= count($productos) ?> producto<?= count($productos) !== 1 ? 's' : '' ?> registrado<?= count($productos) !== 1 ? 's' : '' ?>
        </p>
    </div>
    <button type="button" onclick="abrirModalProducto()"
       class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white transition-all"
       style="background:#F97316;box-shadow:0 4px 12px rgba(249,115,22,0.3);"
       onmouseover="this.style.background='#EA6A0A';this.style.transform='translateY(-1px)';"
       onmouseout="this.style.background='#F97316';this.style.transform='';">
        <i class="fas fa-plus"></i> Agregar Producto
    </button>
</div>

<?php if (isset($_SESSION['alert'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
            title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
            text: '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
            confirmButtonColor: '#F97316'
        });
    });
</script>
<?php unset($_SESSION['alert']); endif; ?>

<!-- Grid de tarjetas -->
<?php if (empty($productos)): ?>
<div class="bg-white rounded-2xl border flex flex-col items-center justify-center py-20 gap-4"
     style="border-color:#F3D5B5;">
    <span style="font-size:56px;">🍞</span>
    <p class="text-lg font-black" style="color:#A87D5C;">No hay productos registrados aún.</p>
    <button type="button" onclick="abrirModalProducto()"
            class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-black text-white"
            style="background:#F97316;">
        <i class="fas fa-plus"></i> Agregar el primero
    </button>
</div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;">
    <?php foreach ($productos as $p): ?>
    <div class="bg-white rounded-2xl border overflow-hidden transition-all"
         style="border-color:#F3D5B5;box-shadow:0 2px 8px rgba(249,115,22,0.06);"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(249,115,22,0.14)';"
         onmouseout="this.style.transform='';this.style.boxShadow='0 2px 8px rgba(249,115,22,0.06)';">

        <!-- Imagen grande -->
        <div style="width:100%;height:180px;background:#FFF7ED;position:relative;overflow:hidden;">
            <?php if (!empty($p['imagen'])): ?>
            <img src="/PanApp/public/uploads/productos/<?= htmlspecialchars($p['imagen']) ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>"
                 style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
            <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;">
                <span style="font-size:52px;">🍞</span>
                <span style="font-size:11px;font-weight:700;color:#C9A880;">Sin imagen</span>
            </div>
            <?php endif; ?>
            <!-- Badge categoría -->
            <span style="position:absolute;top:10px;left:10px;background:rgba(255,255,255,0.92);
                         border:1px solid #F3D5B5;border-radius:99px;padding:3px 10px;
                         font-size:11px;font-weight:900;color:#EA6A0A;backdrop-filter:blur(4px);">
                <?= htmlspecialchars($p['categoria'] ?? '—') ?>
            </span>
        </div>

        <!-- Info -->
        <div style="padding:14px 16px 12px;">
            <p style="font-size:15px;font-weight:900;color:#1C0A00;margin-bottom:2px;
                      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                <?= htmlspecialchars($p['nombre']) ?>
            </p>
            <?php if (!empty($p['descripcion'])): ?>
            <p style="font-size:12px;font-weight:600;color:#A87D5C;margin-bottom:8px;
                      display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                <?= htmlspecialchars($p['descripcion']) ?>
            </p>
            <?php else: ?>
            <div style="margin-bottom:8px;"></div>
            <?php endif; ?>

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:18px;font-weight:900;color:#F97316;">
                    $<?= number_format($p['precio'], 0, ',', '.') ?>
                </span>
                <span style="font-size:11px;font-weight:700;color:#A87D5C;
                             background:#FFF7ED;border:1px solid #F3D5B5;
                             border-radius:8px;padding:2px 8px;">
                    <?= htmlspecialchars($p['unidad_medida'] ?? '—') ?>
                </span>
            </div>

            <!-- Acciones -->
            <div style="display:flex;gap:8px;">
                <button type="button"
                   onclick="abrirModalEditarProducto(<?= $p['id_producto'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>', '<?= htmlspecialchars(addslashes($p['descripcion'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($p['categoria'] ?? '')) ?>', '<?= $p['precio'] ?>', '<?= htmlspecialchars(addslashes($p['unidad_medida'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($p['imagen'] ?? '')) ?>')"
                   style="flex:1;padding:8px;border-radius:10px;border:1.5px solid #F3D5B5;
                          background:#FFF7ED;color:#6B4F3A;font-size:12px;font-weight:800;
                          cursor:pointer;display:flex;align-items:center;justify-content:center;gap:5px;transition:all 0.15s;"
                   onmouseover="this.style.borderColor='#F97316';this.style.color='#F97316';this.style.background='#fff';"
                   onmouseout="this.style.borderColor='#F3D5B5';this.style.color='#6B4F3A';this.style.background='#FFF7ED';"
                   title="Editar">
                    <i class="fas fa-pen"></i> Editar
                </button>
                <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>
                <button type="button"
                   onclick="confirmarEliminar(<?= $p['id_producto'] ?>, '<?= htmlspecialchars(addslashes($p['nombre'])) ?>')"
                   style="padding:8px 12px;border-radius:10px;border:1.5px solid #FCA5A5;
                          background:#FFF1F0;color:#DC2626;font-size:12px;font-weight:800;
                          cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;"
                   onmouseover="this.style.background='#FEE2E2';"
                   onmouseout="this.style.background='#FFF1F0';"
                   title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- ══ MODAL NUEVO PRODUCTO ══ -->
<div id="modal-producto" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-producto-box" class="bg-white rounded-2xl w-full shadow-2xl overflow-y-auto"
         style="max-width:600px;max-height:90vh;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Nuevo <span style="color:#F97316;">Producto</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Completa los datos del producto</p>
            </div>
            <button onclick="cerrarModal('modal-producto','modal-producto-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">x</button>
        </div>

        <form id="form-producto" class="px-6 py-5 flex flex-col gap-4" enctype="multipart/form-data">

            <!-- Zona imagen grande -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Imagen del Producto <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <label id="drop-crear" for="img-crear"
                       style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                              border:2px dashed #F3D5B5;background:#FFF7ED;border-radius:16px;
                              cursor:pointer;transition:all 0.2s;min-height:200px;position:relative;overflow:hidden;"
                       ondragover="event.preventDefault();this.style.borderColor='#F97316';this.style.background='#fff';"
                       ondragleave="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"
                       ondrop="handleDrop(event,'img-crear','preview-crear','drop-crear');">
                    <img id="preview-crear" src="" alt=""
                         style="display:none;width:100%;height:200px;object-fit:cover;border-radius:14px;">
                    <div id="placeholder-crear" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px;">
                        <div style="width:64px;height:64px;background:#F3D5B5;border-radius:16px;
                                    display:flex;align-items:center;justify-content:center;font-size:28px;">
                            📷
                        </div>
                        <span style="font-size:14px;font-weight:800;color:#6B4F3A;">Haz clic o arrastra una imagen</span>
                        <span style="font-size:12px;font-weight:600;color:#A87D5C;">JPG, PNG, WEBP · máx. 2 MB</span>
                    </div>
                    <input type="file" id="img-crear" name="imagen" accept="image/*" style="display:none;"
                           onchange="previewImg(this,'preview-crear','placeholder-crear','drop-crear');">
                </label>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-bread-slice absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombre" required maxlength="255" placeholder="Ej. Pan de queso"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Descripcion <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <textarea name="descripcion" rows="2" maxlength="255" placeholder="Descripcion breve..."
                          class="w-full px-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                          style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                          onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                          onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Categoria <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="categoria" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Pan">Pan</option>
                            <option value="Pastel">Pastel</option>
                            <option value="Galleta">Galleta</option>
                            <option value="Torta">Torta</option>
                            <option value="Bebida">Bebida</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="unidad_medida" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Unidad">Unidad</option>
                            <option value="Docena">Docena</option>
                            <option value="Libra">Libra</option>
                            <option value="Kilo">Kilo</option>
                            <option value="Porcion">Porcion</option>
                            <option value="Litro">Litro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Precio ($) <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-dollar-sign absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="number" name="precio" required min="0" step="0.01" placeholder="0"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div id="error-producto" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-producto"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-plus"></i> <span id="btn-producto-txt">Crear Producto</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-producto','modal-producto-box')"
                        class="px-5 py-2.5 rounded-xl text-sm font-black transition-all"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                        onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══ MODAL EDITAR PRODUCTO ══ -->
<div id="modal-editar-producto" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background:rgba(28,10,0,0.45);backdrop-filter:blur(4px);display:none!important;">
    <div id="modal-editar-producto-box" class="bg-white rounded-2xl w-full shadow-2xl overflow-y-auto"
         style="max-width:600px;max-height:90vh;border:1px solid #F3D5B5;transform:scale(0.92) translateY(16px);opacity:0;transition:transform 0.25s cubic-bezier(0.34,1.56,0.64,1),opacity 0.2s ease;">

        <div class="flex items-center justify-between px-6 py-5 border-b" style="border-color:#F3D5B5;">
            <div>
                <h3 class="text-lg font-black" style="color:#1C0A00;">Editar <span style="color:#F97316;">Producto</span></h3>
                <p class="text-xs font-semibold mt-0.5" style="color:#A87D5C;">Modifica los datos del producto</p>
            </div>
            <button onclick="cerrarModal('modal-editar-producto','modal-editar-producto-box')"
                    class="w-8 h-8 rounded-xl flex items-center justify-center text-sm transition-all"
                    style="background:#FFF7ED;border:1px solid #F3D5B5;color:#A87D5C;"
                    onmouseover="this.style.background='#FEE2E2';this.style.color='#DC2626';"
                    onmouseout="this.style.background='#FFF7ED';this.style.color='#A87D5C';">x</button>
        </div>

        <form id="form-editar-producto" class="px-6 py-5 flex flex-col gap-4" enctype="multipart/form-data">
            <input type="hidden" id="ep-id">
            <input type="hidden" id="ep-imagen-actual" name="imagen_actual">

            <!-- Zona imagen grande -->
            <div class="flex flex-col gap-2">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Imagen del Producto <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <label id="drop-editar" for="img-editar"
                       style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                              border:2px dashed #F3D5B5;background:#FFF7ED;border-radius:16px;
                              cursor:pointer;transition:all 0.2s;min-height:200px;position:relative;overflow:hidden;"
                       ondragover="event.preventDefault();this.style.borderColor='#F97316';this.style.background='#fff';"
                       ondragleave="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"
                       ondrop="handleDrop(event,'img-editar','preview-editar','drop-editar');">
                    <img id="preview-editar" src="" alt=""
                         style="display:none;width:100%;height:200px;object-fit:cover;border-radius:14px;">
                    <div id="placeholder-editar" style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:24px;">
                        <div style="width:64px;height:64px;background:#F3D5B5;border-radius:16px;
                                    display:flex;align-items:center;justify-content:center;font-size:28px;">
                            📷
                        </div>
                        <span style="font-size:14px;font-weight:800;color:#6B4F3A;">Haz clic o arrastra para cambiar</span>
                        <span style="font-size:12px;font-weight:600;color:#A87D5C;">JPG, PNG, WEBP · max. 2 MB</span>
                    </div>
                    <input type="file" id="img-editar" name="imagen" accept="image/*" style="display:none;"
                           onchange="previewImg(this,'preview-editar','placeholder-editar','drop-editar');">
                </label>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Nombre <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-bread-slice absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="text" name="nombre" id="ep-nombre" required maxlength="255"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Descripcion <span class="font-semibold normal-case" style="color:#A87D5C;">(opcional)</span></label>
                <textarea name="descripcion" id="ep-descripcion" rows="2" maxlength="255"
                          class="w-full px-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all resize-none"
                          style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                          onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                          onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Categoria <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="categoria" id="ep-categoria" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Pan">Pan</option>
                            <option value="Pastel">Pastel</option>
                            <option value="Galleta">Galleta</option>
                            <option value="Torta">Torta</option>
                            <option value="Bebida">Bebida</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Unidad <span style="color:#F97316;">*</span></label>
                    <div class="relative">
                        <i class="fas fa-ruler absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                        <select name="unidad_medida" id="ep-unidad" required class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none appearance-none"
                                style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                                onfocus="this.style.borderColor='#F97316';" onblur="this.style.borderColor='#F3D5B5';">
                            <option value="">-- Selecciona --</option>
                            <option value="Unidad">Unidad</option>
                            <option value="Docena">Docena</option>
                            <option value="Libra">Libra</option>
                            <option value="Kilo">Kilo</option>
                            <option value="Porcion">Porcion</option>
                            <option value="Litro">Litro</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-1">
                <label class="text-xs font-black uppercase tracking-wider" style="color:#6B4F3A;">Precio ($) <span style="color:#F97316;">*</span></label>
                <div class="relative">
                    <i class="fas fa-dollar-sign absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#A87D5C;"></i>
                    <input type="number" name="precio" id="ep-precio" required min="0" step="0.01"
                           class="w-full pl-8 pr-3 py-2.5 rounded-xl text-sm font-semibold outline-none transition-all"
                           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#1C0A00;"
                           onfocus="this.style.borderColor='#F97316';this.style.background='#fff';"
                           onblur="this.style.borderColor='#F3D5B5';this.style.background='#FFF7ED';">
                </div>
            </div>

            <div id="error-editar-producto" class="hidden px-4 py-3 rounded-xl text-sm font-bold"
                 style="background:#FEE2E2;color:#991B1B;border:1px solid #FCA5A5;"></div>

            <div class="flex gap-3 pt-1">
                <button type="submit" id="btn-editar-producto"
                        class="flex-1 py-2.5 rounded-xl text-sm font-black text-white flex items-center justify-center gap-2"
                        style="background:#F97316;box-shadow:0 4px 14px rgba(249,115,22,0.3);"
                        onmouseover="this.style.background='#EA6A0A';" onmouseout="this.style.background='#F97316';">
                    <i class="fas fa-save"></i> <span id="btn-editar-producto-txt">Guardar Cambios</span>
                </button>
                <button type="button" onclick="cerrarModal('modal-editar-producto','modal-editar-producto-box')"
                        class="px-5 py-2.5 rounded-xl text-sm font-black transition-all"
                        style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;"
                        onmouseover="this.style.background='#F3D5B5';" onmouseout="this.style.background='#FFF7ED';">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalProducto() {
    abrirModal('modal-producto','modal-producto-box');
    document.getElementById('form-producto').reset();
    document.getElementById('error-producto').classList.add('hidden');
    document.getElementById('preview-crear').style.display = 'none';
    document.getElementById('placeholder-crear').style.display = 'flex';
    document.getElementById('drop-crear').style.borderColor = '#F3D5B5';
    document.getElementById('drop-crear').style.background  = '#FFF7ED';
}

function previewImg(input, previewId, placeholderId, dropId) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var img = document.getElementById(previewId);
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById(placeholderId).style.display = 'none';
            document.getElementById(dropId).style.borderColor = '#F97316';
            document.getElementById(dropId).style.background  = '#fff';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleDrop(event, inputId, previewId, dropId) {
    event.preventDefault();
    var input = document.getElementById(inputId);
    var files = event.dataTransfer.files;
    if (files.length > 0) {
        input.files = files;
        var placeholderId = previewId.replace('preview-', 'placeholder-');
        previewImg(input, previewId, placeholderId, dropId);
    }
    document.getElementById(dropId).style.borderColor = '#F97316';
    document.getElementById(dropId).style.background  = '#fff';
}

document.getElementById('form-producto').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-producto');
    var txt = document.getElementById('btn-producto-txt');
    var err = document.getElementById('error-producto');
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/ProductoController.php?accion=crear', { method:'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon:'success', title:'Producto creado!', text:'El producto fue agregado al catalogo.', confirmButtonColor:'#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = 'Error de conexion.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Crear Producto'; btn.style.opacity = '1'; });
});

function abrirModalEditarProducto(id, nombre, descripcion, categoria, precio, unidad, imagen) {
    abrirModal('modal-editar-producto', 'modal-editar-producto-box');
    document.getElementById('ep-id').value            = id;
    document.getElementById('ep-nombre').value        = nombre;
    document.getElementById('ep-descripcion').value   = descripcion;
    document.getElementById('ep-categoria').value     = categoria;
    document.getElementById('ep-precio').value        = precio;
    document.getElementById('ep-unidad').value        = unidad;
    document.getElementById('ep-imagen-actual').value = imagen;
    document.getElementById('error-editar-producto').classList.add('hidden');
    document.getElementById('img-editar').value = '';

    var preview     = document.getElementById('preview-editar');
    var placeholder = document.getElementById('placeholder-editar');
    var drop        = document.getElementById('drop-editar');

    if (imagen) {
        preview.src           = '/PanApp/public/uploads/productos/' + imagen;
        preview.style.display = 'block';
        placeholder.style.display = 'none';
        drop.style.borderColor = '#F97316';
        drop.style.background  = '#fff';
    } else {
        preview.src           = '';
        preview.style.display = 'none';
        placeholder.style.display = 'flex';
        drop.style.borderColor = '#F3D5B5';
        drop.style.background  = '#FFF7ED';
    }
}

document.getElementById('form-editar-producto').addEventListener('submit', function(e) {
    e.preventDefault();
    var btn = document.getElementById('btn-editar-producto');
    var txt = document.getElementById('btn-editar-producto-txt');
    var err = document.getElementById('error-editar-producto');
    var id  = document.getElementById('ep-id').value;
    btn.disabled = true; txt.textContent = 'Guardando...'; btn.style.opacity = '0.75';
    fetch('/PanApp/controllers/ProductoController.php?accion=editar&id=' + id, { method: 'POST', body: new FormData(this) })
    .then(function() {
        Swal.fire({ icon: 'success', title: 'Producto actualizado!', text: 'Los cambios fueron guardados correctamente.', confirmButtonColor: '#F97316' })
        .then(function() { window.location.reload(); });
    })
    .catch(function() { err.textContent = 'Error de conexion.'; err.classList.remove('hidden'); })
    .finally(function() { btn.disabled = false; txt.textContent = 'Guardar Cambios'; btn.style.opacity = '1'; });
});

function confirmarEliminar(id, nombre) {
    Swal.fire({
        icon: 'warning',
        title: '¿Eliminar producto?',
        html: '<p style="color:#6B4F3A;font-size:14px;line-height:1.6;">Estás a punto de eliminar <strong>' + nombre + '</strong>.<br>Esta acción no se puede deshacer.</p>',
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#F97316',
        reverseButtons: true,
        focusCancel: true
    }).then(function(result) {
        if (result.isConfirmed) {
            window.location.href = '/PanApp/controllers/ProductoController.php?accion=eliminar&id=' + id;
        }
    });
}
</script>
