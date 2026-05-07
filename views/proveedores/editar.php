<?php
// Redirige a crear.php que ya maneja edición
$id = intval($_GET['id'] ?? 0);
header("Location: crear.php?id=$id");
exit;
