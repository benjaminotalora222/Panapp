<?php
$appRoot = '/PanApp'; // ← ajusta si el proyecto está en otra carpeta del servidor

// Helper para generar los atributos de un link del sidebar
function sidebarLink(string $page, string $titulo): array {
    $active = ($titulo === $page);
    return [
        'class'  => $active ? 'border-l-4' : '',
        'style'  => $active
            ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:12px;'
            : 'color:#6B4F3A;',
        'icolor' => $active ? '#F97316' : '#A87D5C',
        'active' => $active,
    ];
}
?>
    <!-- ══ SIDEBAR ══ -->
    <aside id="sidebar"
           class="fixed top-0 left-0 h-full w-60 bg-white flex flex-col z-50 transition-transform duration-300 -translate-x-full md:translate-x-0"
           style="border-right:1px solid #F3D5B5;">

        <!-- Brand -->
        <a href="<?= $appRoot ?>/public/index.php"
           class="flex items-center gap-3 px-5 py-4 no-underline"
           style="border-bottom:1px solid #F3D5B5;">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xl flex-shrink-0"
                 style="background:#F97316;box-shadow:0 3px 10px rgba(249,115,22,0.35);">🥐</div>
            <span class="text-xl font-black" style="color:#1C0A00;">
                Pan<span style="color:#F97316;">App</span>
            </span>
        </a>

        <!-- Nav -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 flex flex-col gap-1">

            <!-- GENERAL -->
            <p class="text-xs font-black uppercase tracking-widest px-2 pt-2 pb-1" style="color:#A87D5C;">General</p>

            <?php $l = sidebarLink('Dashboard', $titulo); ?>
            <a href="<?= $appRoot ?>/views/dashboard/<?= strtolower($usuario['rol']) ?>.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-home w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Inicio
            </a>

            <!-- ── CAJERO ── -->
            <?php if (strtoupper($usuario['rol']) === 'CAJERO'): ?>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Mi trabajo</p>

            <?php $l = sidebarLink('Ventas', $titulo); ?>
            <a href="<?= $appRoot ?>/views/ventas/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-cash-register w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Ventas
            </a>

            <?php $l = sidebarLink('Productos', $titulo); ?>
            <a href="<?= $appRoot ?>/views/productos/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-bread-slice w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Productos
            </a>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Reportes</p>

            <?php $l = sidebarLink('Reporte Ventas', $titulo); ?>
            <a href="<?= $appRoot ?>/views/reportes/index.php?tab=ventas"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-chart-bar w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Reporte Ventas
            </a>

            <?php $l = sidebarLink('Reporte Productos', $titulo); ?>
            <a href="<?= $appRoot ?>/views/reportes/index.php?tab=productos"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-chart-pie w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Reporte Productos
            </a>

            <?php endif; ?>

            <!-- ── ADMIN ── -->
            <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Operaciones</p>

            <?php $l = sidebarLink('Ventas', $titulo); ?>
            <a href="<?= $appRoot ?>/views/ventas/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-cash-register w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Ventas
            </a>

            <?php $l = sidebarLink('Inventario', $titulo); ?>
            <a href="<?= $appRoot ?>/views/inventario/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-boxes w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Inventario
            </a>

            <?php $l = sidebarLink('Productos', $titulo); ?>
            <a href="<?= $appRoot ?>/views/productos/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-bread-slice w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Productos
            </a>

            <?php $l = sidebarLink('Proveedores', $titulo); ?>
            <a href="<?= $appRoot ?>/views/proveedores/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-truck w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Proveedores
            </a>

            <?php $l = sidebarLink('Insumos', $titulo); ?>
            <a href="<?= $appRoot ?>/views/insumos/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-seedling w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Insumos
            </a>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Administración</p>

            <?php $l = sidebarLink('Reportes', $titulo); ?>
            <a href="<?= $appRoot ?>/views/reportes/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-chart-bar w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Reportes
            </a>

            <?php $l = sidebarLink('Usuarios', $titulo); ?>
            <a href="<?= $appRoot ?>/views/usuarios/index.php"
               class="nav-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= $l['class'] ?>"
               style="<?= $l['style'] ?>"
               data-active="<?= $l['active'] ? '1' : '0' ?>">
                <i class="fas fa-users w-4 text-center" style="color:<?= $l['icolor'] ?>;"></i>
                Usuarios
            </a>

            <?php endif; ?>

        </nav>

        <!-- User info + logout -->
        <div class="flex items-center gap-3 px-4 py-3" style="border-top:1px solid #F3D5B5;">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-base flex-shrink-0"
                 style="background:#FFF7ED;border:2px solid #FED7AA;">👤</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-black truncate" style="color:#1C0A00;">
                    <?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>
                </p>
                <p class="text-xs font-bold" style="color:#A87D5C;">
                    <?= htmlspecialchars($usuario['rol']) ?>
                </p>
            </div>
            <a href="<?= $appRoot ?>/controllers/AuthController.php?accion=logout"
               id="btn-logout"
               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
               style="border-color:#F3D5B5;color:#A87D5C;"
               title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

    </aside>

<script>
    // Hover en links del sidebar
    document.querySelectorAll('.nav-link').forEach(function(link) {
        var isActive = link.dataset.active === '1';
        link.addEventListener('mouseover', function() {
            this.style.background = '#FFF7ED';
            this.style.color = '#EA6A0A';
        });
        link.addEventListener('mouseout', function() {
            if (!isActive) {
                this.style.background = '';
                this.style.color = '#6B4F3A';
            }
        });
    });

    // Hover en botón logout
    var btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('mouseover', function() {
            this.style.background = '#FEE2E2';
            this.style.borderColor = '#FCA5A5';
            this.style.color = '#DC2626';
        });
        btnLogout.addEventListener('mouseout', function() {
            this.style.background = '';
            this.style.borderColor = '#F3D5B5';
            this.style.color = '#A87D5C';
        });
    }

    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('overlay').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }
</script>
