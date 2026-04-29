<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../usuarios/login.php"); exit;
}

require_once __DIR__ . '/../../config/database.php';

$db      = (new Database())->conectar();
$rol     = strtoupper($_SESSION['usuario']['rol']);
$periodo = $_GET['periodo'] ?? 'diario';
$tab     = $_GET['tab']     ?? 'ventas';

switch ($periodo) {
    case 'semanal':
        $fechaDesde   = date('Y-m-d', strtotime('monday this week'));
        $fechaHasta   = date('Y-m-d', strtotime('sunday this week'));
        $labelPeriodo = 'Semanal (' . date('d/m/Y', strtotime($fechaDesde)) . ' — ' . date('d/m/Y', strtotime($fechaHasta)) . ')';
        break;
    case 'mensual':
        $fechaDesde   = date('Y-m-01');
        $fechaHasta   = date('Y-m-t');
        $labelPeriodo = 'Mensual — ' . date('F Y');
        break;
    default:
        $fechaDesde   = date('Y-m-d');
        $fechaHasta   = date('Y-m-d');
        $labelPeriodo = 'Diario — ' . date('d/m/Y');
        break;
}

$condUsuario = ($rol !== 'ADMIN') ? "AND v.id_usuario = " . intval($_SESSION['usuario']['id_usuario']) : '';

// Ventas
$stmtV = $db->prepare("
    SELECT v.id_venta, v.fecha, v.total,
           mp.nombre AS metodo_pago,
           u.nombres, u.apellidos
    FROM ventas v
    LEFT JOIN metodos_pago mp ON v.id_metodo_pago = mp.id_metodo_pago
    LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
    WHERE DATE(v.fecha) BETWEEN :desde AND :hasta
    AND v.estado = 'completada'
    $condUsuario
    ORDER BY v.fecha DESC
");
$stmtV->bindParam(':desde', $fechaDesde);
$stmtV->bindParam(':hasta', $fechaHasta);
$stmtV->execute();
$ventas = $stmtV->fetchAll(PDO::FETCH_ASSOC);

$totalVentas   = count($ventas);
$totalIngresos = array_sum(array_column($ventas, 'total'));
$promedio      = $totalVentas > 0 ? $totalIngresos / $totalVentas : 0;
$porMetodo     = [];
foreach ($ventas as $v) {
    $mp = $v['metodo_pago'] ?? 'Sin método';
    $porMetodo[$mp] = ($porMetodo[$mp] ?? 0) + $v['total'];
}

// Productos
$stmtP = $db->prepare("
    SELECT p.nombre, p.categoria,
           SUM(dv.cantidad) AS total_unidades,
           SUM(dv.subtotal) AS total_ingresos,
           COUNT(DISTINCT dv.id_venta) AS num_ventas
    FROM detalle_venta dv
    LEFT JOIN productos p ON dv.id_producto = p.id_producto
    LEFT JOIN ventas v ON dv.id_venta = v.id_venta
    WHERE DATE(v.fecha) BETWEEN :desde AND :hasta
    AND v.estado = 'completada'
    $condUsuario
    GROUP BY dv.id_producto
    ORDER BY total_unidades DESC
    LIMIT 20
");
$stmtP->bindParam(':desde', $fechaDesde);
$stmtP->bindParam(':hasta', $fechaHasta);
$stmtP->execute();
$productosRanking = $stmtP->fetchAll(PDO::FETCH_ASSOC);

$generadoPor = htmlspecialchars($_SESSION['usuario']['nombres'] . ' ' . $_SESSION['usuario']['apellidos']);
$tipoReporte = $tab === 'productos' ? 'Productos más Vendidos' : 'Ventas';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte <?= $tipoReporte ?> - PanApp</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:Arial,sans-serif;font-size:13px;color:#1C0A00;background:#fff;padding:32px;}
        .header{display:flex;align-items:center;justify-content:space-between;border-bottom:3px solid #F97316;padding-bottom:16px;margin-bottom:24px;}
        .brand{display:flex;align-items:center;gap:10px;}
        .brand-icon{width:44px;height:44px;background:#F97316;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;}
        .brand-name{font-size:22px;font-weight:900;}
        .brand-name span{color:#F97316;}
        .header-meta{text-align:right;color:#A87D5C;font-size:12px;}
        .header-meta p{margin-bottom:2px;}
        .report-title{background:linear-gradient(135deg,#F97316,#FB923C);color:#fff;padding:16px 20px;border-radius:10px;margin-bottom:20px;}
        .report-title h1{font-size:18px;font-weight:900;margin-bottom:4px;}
        .report-title p{font-size:12px;opacity:.85;}
        .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
        .card{border:1.5px solid #F3D5B5;border-radius:10px;padding:14px 16px;background:#FFF7ED;}
        .card-label{font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:1px;color:#A87D5C;margin-bottom:4px;}
        .card-value{font-size:22px;font-weight:900;}
        .card-value.orange{color:#F97316;}
        .section-title{font-size:14px;font-weight:900;color:#1C0A00;margin-bottom:10px;margin-top:20px;border-left:4px solid #F97316;padding-left:10px;}
        .metodos{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
        .metodo-pill{background:#FFF7ED;border:1.5px solid #F3D5B5;border-radius:8px;padding:8px 14px;font-size:12px;}
        .metodo-pill strong{color:#F97316;display:block;font-size:14px;}
        table{width:100%;border-collapse:collapse;font-size:12px;}
        thead tr{background:#FFF7ED;}
        th{padding:10px 12px;text-align:left;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.8px;color:#A87D5C;border-bottom:1.5px solid #F3D5B5;}
        td{padding:9px 12px;border-bottom:1px solid #F3D5B5;color:#1C0A00;}
        tr:nth-child(even) td{background:#FFFDF9;}
        .orange{color:#F97316;font-weight:900;}
        .total-row td{background:#FFF7ED!important;font-weight:900;border-top:2px solid #F3D5B5;}
        .grand-total{color:#F97316;font-size:14px;}
        .footer{margin-top:28px;padding-top:14px;border-top:1.5px solid #F3D5B5;display:flex;justify-content:space-between;font-size:11px;color:#A87D5C;}
        @media print{body{padding:20px;}.no-print{display:none!important;}@page{margin:1cm;}}
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom:20px;display:flex;gap:10px;">
        <button onclick="window.print()"
                style="background:#F97316;color:#fff;border:none;padding:10px 24px;border-radius:10px;font-weight:900;font-size:13px;cursor:pointer;">
            🖨️ Imprimir / Guardar PDF
        </button>
        <a href="index.php?periodo=<?= $periodo ?>&tab=<?= $tab ?>"
           style="background:#FFF7ED;border:1.5px solid #F3D5B5;color:#6B4F3A;padding:10px 20px;border-radius:10px;font-weight:900;font-size:13px;text-decoration:none;">
            ← Volver
        </a>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="brand">
            <div class="brand-icon">🥐</div>
            <div class="brand-name">Pan<span>App</span></div>
        </div>
        <div class="header-meta">
            <p><strong>Generado por:</strong> <?= $generadoPor ?></p>
            <p><strong>Rol:</strong> <?= htmlspecialchars($_SESSION['usuario']['rol']) ?></p>
            <p><strong>Fecha:</strong> <?= date('d/m/Y H:i') ?></p>
        </div>
    </div>

    <!-- Título -->
    <div class="report-title">
        <h1><?= $tab === 'productos' ? '🍞' : '📊' ?> Reporte de <?= $tipoReporte ?></h1>
        <p>Período: <?= $labelPeriodo ?></p>
    </div>

    <?php if ($tab === 'ventas'): ?>

    <!-- Tarjetas ventas -->
    <div class="cards">
        <div class="card"><div class="card-label">Ventas completadas</div><div class="card-value"><?= $totalVentas ?></div></div>
        <div class="card"><div class="card-label">Total ingresos</div><div class="card-value orange">$<?= number_format($totalIngresos,0,',','.') ?></div></div>
        <div class="card"><div class="card-label">Promedio por venta</div><div class="card-value">$<?= number_format($promedio,0,',','.') ?></div></div>
    </div>

    <?php if (!empty($porMetodo)): ?>
    <div class="section-title">Por Método de Pago</div>
    <div class="metodos">
        <?php foreach ($porMetodo as $mp => $monto): ?>
        <div class="metodo-pill"><?= htmlspecialchars($mp) ?><strong>$<?= number_format($monto,0,',','.') ?></strong></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="section-title">Detalle de Ventas</div>
    <?php if (empty($ventas)): ?>
        <p style="color:#A87D5C;font-style:italic;padding:16px 0;">No hay ventas en este período.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr>
                <th># Venta</th><th>Fecha</th>
                <?php if ($rol==='ADMIN'): ?><th>Cajero</th><?php endif; ?>
                <th>Método Pago</th><th style="text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $v): ?>
            <tr>
                <td class="orange">#<?= str_pad($v['id_venta'],4,'0',STR_PAD_LEFT) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($v['fecha'])) ?></td>
                <?php if ($rol==='ADMIN'): ?><td><?= htmlspecialchars($v['nombres'].' '.$v['apellidos']) ?></td><?php endif; ?>
                <td><?= htmlspecialchars($v['metodo_pago'] ?? '—') ?></td>
                <td style="text-align:right;font-weight:700;">$<?= number_format($v['total'],0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="total-row">
                <td colspan="<?= $rol==='ADMIN'?4:3 ?>" style="text-align:right;">TOTAL</td>
                <td style="text-align:right;" class="grand-total">$<?= number_format($totalIngresos,0,',','.') ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <?php else: ?>

    <!-- Tabla productos -->
    <div class="section-title">Ranking de Productos más Vendidos</div>
    <?php if (empty($productosRanking)): ?>
        <p style="color:#A87D5C;font-style:italic;padding:16px 0;">No hay datos de productos en este período.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Pos.</th><th>Producto</th><th>Categoría</th><th>Unidades</th><th>En # ventas</th><th style="text-align:right;">Ingresos</th></tr>
        </thead>
        <tbody>
            <?php $medallas=['🥇','🥈','🥉']; ?>
            <?php foreach ($productosRanking as $idx => $p): ?>
            <tr>
                <td><?= $medallas[$idx] ?? ($idx+1) ?></td>
                <td style="font-weight:700;"><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['categoria'] ?? '—') ?></td>
                <td class="orange"><?= $p['total_unidades'] ?></td>
                <td><?= $p['num_ventas'] ?></td>
                <td style="text-align:right;font-weight:700;">$<?= number_format($p['total_ingresos'],0,',','.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php endif; ?>

    <div class="footer">
        <span>PanApp · Sistema de Gestión de Panadería</span>
        <span>Reporte generado el <?= date('d/m/Y \a \l\a\s H:i') ?></span>
    </div>

</body>
</html>
