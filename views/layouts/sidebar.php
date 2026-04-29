    <!-- ══ SIDEBAR ══ -->
    <aside id="sidebar"
           class="fixed top-0 left-0 h-full w-60 bg-white flex flex-col z-50 transition-transform duration-300 -translate-x-full md:translate-x-0"
           style="border-right:1px solid #F3D5B5;">

        <!-- Brand -->
        <a href="../../index.php"
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

            <a href="../dashboard/<?= strtolower($usuario['rol']) ?>.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Dashboard') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Dashboard') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Dashboard') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-home w-4 text-center" style="color:<?= ($titulo === 'Dashboard') ? '#F97316' : '#A87D5C' ?>;"></i>
                Inicio
            </a>

            <!-- ── CAJERO ── -->
            <?php if (strtoupper($usuario['rol']) === 'CAJERO'): ?>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Mi trabajo</p>

            <a href="../ventas/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Ventas') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Ventas') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Ventas') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-cash-register w-4 text-center" style="color:#A87D5C;"></i>
                Ventas
            </a>

            <a href="../productos/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Productos') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Productos') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Productos') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-bread-slice w-4 text-center" style="color:#A87D5C;"></i>
                Productos
            </a>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Reportes</p>

            <!-- ✅ Ambos apuntan a index.php con ?tab= -->
            <a href="../reportes/index.php?tab=ventas"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Reporte Ventas') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Reporte Ventas') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Reporte Ventas') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-chart-bar w-4 text-center" style="color:#A87D5C;"></i>
                Reporte Ventas
            </a>

            <a href="../reportes/index.php?tab=productos"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Reporte Productos') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Reporte Productos') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Reporte Productos') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-chart-pie w-4 text-center" style="color:#A87D5C;"></i>
                Reporte Productos
            </a>

            <?php endif; ?>

            <!-- ── ADMIN ── -->
            <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Operaciones</p>

            <a href="../ventas/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Ventas') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Ventas') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Ventas') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-cash-register w-4 text-center" style="color:#A87D5C;"></i>
                Ventas
            </a>

            <a href="../inventario/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Inventario') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Inventario') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Inventario') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-boxes w-4 text-center" style="color:#A87D5C;"></i>
                Inventario
            </a>

            <a href="../productos/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Productos') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Productos') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Productos') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-bread-slice w-4 text-center" style="color:#A87D5C;"></i>
                Productos
            </a>

            <a href="../proveedores/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Proveedores') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Proveedores') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Proveedores') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-truck w-4 text-center" style="color:#A87D5C;"></i>
                Proveedores
            </a>

            <a href="../insumos/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Insumos') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Insumos') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Insumos') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-seedling w-4 text-center" style="color:#A87D5C;"></i>
                Insumos
            </a>

            <p class="text-xs font-black uppercase tracking-widest px-2 pt-3 pb-1" style="color:#A87D5C;">Administración</p>

            <!-- ✅ Admin también usa index.php -->
            <a href="../reportes/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Reportes') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Reportes') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Reportes') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-chart-bar w-4 text-center" style="color:#A87D5C;"></i>
                Reportes
            </a>

            <a href="../usuarios/index.php"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all no-underline <?= ($titulo === 'Usuarios') ? 'border-l-4' : '' ?>"
               style="<?= ($titulo === 'Usuarios') ? 'background:#FFF7ED;color:#EA6A0A;border-color:#F97316;padding-left:10px;' : 'color:#6B4F3A;' ?>"
               onmouseover="this.style.background='#FFF7ED';this.style.color='#EA6A0A';"
               onmouseout="<?= ($titulo !== 'Usuarios') ? "this.style.background='';this.style.color='#6B4F3A';" : '' ?>">
                <i class="fas fa-users w-4 text-center" style="color:#A87D5C;"></i>
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
            <a href="../../controllers/AuthController.php?accion=logout"
               class="w-8 h-8 rounded-lg border flex items-center justify-center text-sm transition-all"
               style="border-color:#F3D5B5;color:#A87D5C;"
               onmouseover="this.style.background='#FEE2E2';this.style.borderColor='#FCA5A5';this.style.color='#DC2626';"
               onmouseout="this.style.background='';this.style.borderColor='#F3D5B5';this.style.color='#A87D5C';"
               title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

    </aside>

<script>
    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('overlay').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }
</script>
