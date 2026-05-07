<?php
$appRoot = '/PanApp';

function sidebarLink(string $page, string $titulo): array {
    $active = ($titulo === $page);
    return [
        'class'  => $active ? 'active' : '',
        'active' => $active,
        'icolor' => $active ? '#F97316' : '#A87D5C',
    ];
}

// ── Consultar insumos con stock bajo (solo Admin) ──
$stockBajoItems = [];
$totalStockBajo = 0;
if (strtoupper($usuario['rol']) === 'ADMIN') {
    try {
        require_once __DIR__ . '/../../config/database.php';
        $dbSidebar = (new Database())->conectar();
        $stmtSB = $dbSidebar->prepare("
            SELECT i.nombre, ii.cantidad_actual, i.unidad_medida
            FROM inventario_insumos ii
            JOIN insumos i ON ii.id_insumo = i.id_insumo
            WHERE ii.cantidad_actual <= 5
            ORDER BY ii.cantidad_actual ASC
            LIMIT 10
        ");
        $stmtSB->execute();
        $stockBajoItems = $stmtSB->fetchAll(PDO::FETCH_ASSOC);
        $totalStockBajo = count($stockBajoItems);
    } catch (Exception $e) {
        $totalStockBajo = 0;
    }
}
?>

<style>
    /* ══ SIDEBAR PROFESIONAL ══ */
    #sidebar {
        background: #FFFFFF;
        border-right: 1px solid #F0E6D8;
        box-shadow: 4px 0 24px rgba(28,10,0,0.06);
        display: flex;
        flex-direction: column;
    }

    /* Brand */
    .sb-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 20px 16px;
        border-bottom: 1px solid #F0E6D8;
        text-decoration: none;
    }
    .sb-brand-logo {
        width: 38px; height: 38px;
        background: linear-gradient(135deg, #F97316, #EA6A0A);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(249,115,22,0.35);
        flex-shrink: 0;
        transition: transform 0.2s;
    }
    .sb-brand:hover .sb-brand-logo { transform: rotate(8deg) scale(1.05); }
    .sb-brand-name {
        font-family: 'Nunito', sans-serif;
        font-size: 20px;
        font-weight: 900;
        color: #1C0A00;
        letter-spacing: -0.3px;
    }
    .sb-brand-name span { color: #F97316; }

    /* Nav */
    .sb-nav {
        flex: 1;
        overflow-y: auto;
        padding: 12px 12px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .sb-nav::-webkit-scrollbar { width: 3px; }
    .sb-nav::-webkit-scrollbar-thumb { background: #F3D5B5; border-radius: 3px; }

    /* Sección label */
    .sb-section {
        font-size: 9px;
        font-weight: 900;
        letter-spacing: 1.8px;
        text-transform: uppercase;
        color: #C4A882;
        padding: 14px 10px 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sb-section::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #F0E6D8;
    }

    /* Link */
    .sb-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 9px 12px;
        border-radius: 10px;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
        color: #6B4F3A;
        transition: background 0.15s, color 0.15s, transform 0.1s;
        position: relative;
        cursor: pointer;
    }
    .sb-link:hover {
        background: #FFF7ED;
        color: #EA6A0A;
        transform: translateX(2px);
    }
    .sb-link.active {
        background: linear-gradient(135deg, #FFF3E8, #FFE8CC);
        color: #EA6A0A;
        font-weight: 900;
        box-shadow: 0 2px 8px rgba(249,115,22,0.12);
    }
    .sb-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 3px;
        background: #F97316;
        border-radius: 0 3px 3px 0;
    }

    /* Ícono del link */
    .sb-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        transition: background 0.15s;
    }
    .sb-link:not(.active) .sb-icon {
        background: #F9F3EC;
    }
    .sb-link.active .sb-icon {
        background: rgba(249,115,22,0.15);
    }
    .sb-link:hover:not(.active) .sb-icon {
        background: #FEE8CC;
    }

    /* Badge de notificación */
    .sb-badge {
        margin-left: auto;
        font-size: 9px;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 99px;
        background: #F97316;
        color: #fff;
        letter-spacing: 0.3px;
    }
    .sb-badge-alert {
        margin-left: auto;
        font-size: 9px;
        font-weight: 900;
        padding: 2px 7px;
        border-radius: 99px;
        background: #EF4444;
        color: #fff;
        letter-spacing: 0.3px;
        animation: pulseBadge 2s ease-in-out infinite;
    }
    @keyframes pulseBadge {
        0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
        50%      { box-shadow: 0 0 0 4px rgba(239,68,68,0); }
    }

    /* Footer del sidebar */
    .sb-footer {
        padding: 12px;
        border-top: 1px solid #F0E6D8;
        background: #FDFAF7;
    }
    .sb-user {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 10px;
        border-radius: 12px;
        background: #fff;
        border: 1px solid #F0E6D8;
        transition: border-color 0.2s;
    }
    .sb-user:hover { border-color: #F3D5B5; }
    .sb-avatar {
        width: 34px; height: 34px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        font-weight: 900;
        color: #fff;
        flex-shrink: 0;
        background: linear-gradient(135deg, #F97316, #EA6A0A);
    }
    .sb-user-info { flex: 1; min-width: 0; }
    .sb-user-name {
        font-size: 12px;
        font-weight: 900;
        color: #1C0A00;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        font-family: 'Nunito', sans-serif;
    }
    .sb-user-role {
        font-size: 10px;
        font-weight: 700;
        color: #A87D5C;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-top: 1px;
    }
    .sb-role-dot {
        width: 5px; height: 5px;
        border-radius: 50%;
        background: #10B981;
        flex-shrink: 0;
    }
    .sb-logout {
        width: 30px; height: 30px;
        border-radius: 8px;
        border: 1px solid #F0E6D8;
        background: transparent;
        color: #A87D5C;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, color 0.15s;
        flex-shrink: 0;
    }
    .sb-logout:hover {
        background: #FEE2E2;
        border-color: #FCA5A5;
        color: #DC2626;
    }
</style>

<!-- ══ SIDEBAR ══ -->
<aside id="sidebar"
       class="fixed top-0 left-0 h-full w-60 z-50 transition-transform duration-300 -translate-x-full md:translate-x-0">

    <!-- Brand -->
    <a href="<?= $appRoot ?>/public/index.php" class="sb-brand">
        <div class="sb-brand-logo">🥐</div>
        <span class="sb-brand-name">Pan<span>App</span></span>
    </a>

    <!-- Nav -->
    <nav class="sb-nav">

        <!-- GENERAL -->
        <div class="sb-section">General</div>

        <?php $l = sidebarLink('Dashboard', $titulo); ?>
        <a href="<?= $appRoot ?>/views/dashboard/<?= strtolower($usuario['rol']) ?>.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-home" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Inicio
        </a>

        <!-- ── CAJERO ── -->
        <?php if (strtoupper($usuario['rol']) === 'CAJERO'): ?>

        <div class="sb-section">Mi trabajo</div>

        <?php $l = sidebarLink('Ventas', $titulo); ?>
        <a href="<?= $appRoot ?>/views/ventas/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-cash-register" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Ventas
        </a>

        <?php $l = sidebarLink('Productos', $titulo); ?>
        <a href="<?= $appRoot ?>/views/productos/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-bread-slice" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Productos
        </a>

        <div class="sb-section">Reportes</div>

        <?php $l = sidebarLink('Reporte Ventas', $titulo); ?>
        <a href="<?= $appRoot ?>/views/reportes/index.php?tab=ventas"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-chart-bar" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Reporte Ventas
        </a>

        <?php $l = sidebarLink('Reporte Productos', $titulo); ?>
        <a href="<?= $appRoot ?>/views/reportes/index.php?tab=productos"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-chart-pie" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Reporte Productos
        </a>

        <?php endif; ?>

        <!-- ── ADMIN ── -->
        <?php if (strtoupper($usuario['rol']) === 'ADMIN'): ?>

        <div class="sb-section">Operaciones</div>

        <?php $l = sidebarLink('Ventas', $titulo); ?>
        <a href="<?= $appRoot ?>/views/ventas/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-cash-register" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Ventas
        </a>

        <?php $l = sidebarLink('Inventario', $titulo); ?>
        <a href="<?= $appRoot ?>/views/inventario/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-boxes" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Inventario
            <?php if ($totalStockBajo > 0): ?>
            <span class="sb-badge-alert" title="<?= $totalStockBajo ?> insumo<?= $totalStockBajo !== 1 ? 's' : '' ?> con stock bajo">
                <?= $totalStockBajo ?>
            </span>
            <?php endif; ?>
        </a>

        <?php $l = sidebarLink('Productos', $titulo); ?>
        <a href="<?= $appRoot ?>/views/productos/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-bread-slice" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Productos
        </a>

        <?php $l = sidebarLink('Proveedores', $titulo); ?>
        <a href="<?= $appRoot ?>/views/proveedores/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-truck" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Proveedores
        </a>

        <?php $l = sidebarLink('Insumos', $titulo); ?>
        <a href="<?= $appRoot ?>/views/insumos/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-seedling" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Insumos
        </a>

        <div class="sb-section">Administración</div>

        <?php $l = sidebarLink('Reportes', $titulo); ?>
        <a href="<?= $appRoot ?>/views/reportes/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-chart-bar" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Reportes
        </a>

        <?php $l = sidebarLink('Usuarios', $titulo); ?>
        <a href="<?= $appRoot ?>/views/usuarios/index.php"
           class="sb-link <?= $l['class'] ?>"
           data-active="<?= $l['active'] ? '1' : '0' ?>">
            <span class="sb-icon">
                <i class="fas fa-users" style="color:<?= $l['icolor'] ?>;"></i>
            </span>
            Usuarios
        </a>

        <?php endif; ?>

    </nav>

    <!-- Footer: usuario + logout -->
    <div class="sb-footer">
        <div class="sb-user">
            <?php
            $iniciales = strtoupper(
                substr($usuario['nombres'] ?? 'U', 0, 1) .
                substr($usuario['apellidos'] ?? '', 0, 1)
            );
            ?>
            <div class="sb-avatar"><?= $iniciales ?></div>
            <div class="sb-user-info">
                <div class="sb-user-name">
                    <?= htmlspecialchars($usuario['nombres'] . ' ' . $usuario['apellidos']) ?>
                </div>
                <div class="sb-user-role">
                    <span class="sb-role-dot"></span>
                    <?= htmlspecialchars($usuario['rol']) ?>
                </div>
            </div>
            <a href="<?= $appRoot ?>/controllers/AuthController.php?accion=logout"
               class="sb-logout" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>

</aside>

<script>
    // Hover en links del sidebar
    document.querySelectorAll('.sb-link').forEach(function(link) {
        var isActive = link.dataset.active === '1';
        link.addEventListener('mouseover', function() {
            if (!isActive) {
                this.style.background = '#FFF7ED';
                this.style.color = '#EA6A0A';
            }
        });
        link.addEventListener('mouseout', function() {
            if (!isActive) {
                this.style.background = '';
                this.style.color = '';
            }
        });
    });

    function openSidebar() {
        document.getElementById('sidebar').classList.remove('-translate-x-full');
        document.getElementById('overlay').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.add('-translate-x-full');
        document.getElementById('overlay').classList.add('hidden');
    }
</script>
