<?php
require_once 'config.php';
requireAdmin();


$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d');


$sql = "CALL sp_resumen_ventas(?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
$stmt->execute();
$result = $stmt->get_result();
$resumen = $result->fetch_assoc();
$stmt->close();
$conn->next_result();


$sql = "CALL sp_productos_mas_vendidos(10)";
$productos_top = $conn->query($sql);
$conn->next_result();


$sql = "SELECT h.*, p.name as producto_nombre 
        FROM historial_stock h
        INNER JOIN productos p ON h.product_id = p.product_id
        ORDER BY h.fecha_movimiento DESC
        LIMIT 20";
$historial_stock = $conn->query($sql);


$sql = "SELECT * FROM v_resumen_inventario ORDER BY total_vendido DESC";
$inventario = $conn->query($sql);


$sql = "SELECT * FROM v_actividad_reciente LIMIT 15";
$actividad = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    
</head>
<body>
   
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?>
        </div>
        <ul class="nav flex-column mt-3">
            <li class="nav-item">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin-productos.php">
                    <i class="fas fa-box me-2"></i>Productos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin-usuarios.php">
                    <i class="fas fa-users me-2"></i>Usuarios
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin-ordenes.php">
                    <i class="fas fa-shopping-bag me-2"></i>Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin-reportes.php">
                    <i class="fas fa-chart-bar me-2"></i>Reportes
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-store me-2"></i>Ver Tienda
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                </a>
            </li>
        </ul>
    </div>

    
    <div class="main-content">
        <h2 class="mb-4"><i class="fas fa-chart-line me-2"></i>Reportes y Estadísticas</h2>

        
        <div class="card">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" value="<?php echo $fecha_fin; ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        
        <h4 class="mb-3">Resumen del Período</h4>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stat-box">
                    <h3><?php echo $resumen['total_ordenes'] ?? 0; ?></h3>
                    <p><i class="fas fa-shopping-cart me-2"></i>Total Órdenes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>$<?php echo number_format($resumen['ventas_totales'] ?? 0, 2); ?></h3>
                    <p><i class="fas fa-dollar-sign me-2"></i>Ventas Totales</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3>$<?php echo number_format($resumen['ticket_promedio'] ?? 0, 2); ?></h3>
                    <p><i class="fas fa-receipt me-2"></i>Ticket Promedio</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box">
                    <h3><?php echo $resumen['clientes_unicos'] ?? 0; ?></h3>
                    <p><i class="fas fa-users me-2"></i>Clientes Únicos</p>
                </div>
            </div>
        </div>

        <div class="row">
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-fire me-2"></i>Top 10 Productos Más Vendidos</h5>
                        <small class="text-muted">Procedimiento Almacenado: sp_productos_mas_vendidos</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Vendidos</th>
                                        <th>Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($productos_top && $productos_top->num_rows > 0): ?>
                                        <?php while ($prod = $productos_top->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($prod['name']); ?></strong></td>
                                                <td><span class="badge bg-primary"><?php echo $prod['total_vendido']; ?></span></td>
                                                <td><strong class="text-success">$<?php echo number_format($prod['ingresos_totales'], 2); ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay datos</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-warehouse me-2"></i>Resumen de Inventario</h5>
                        <small class="text-muted">Vista: v_resumen_inventario</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                        <th>Precio</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($inv = $inventario->fetch_assoc()): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($inv['name']); ?></strong></td>
                                            <td>
                                                <span class="badge <?php echo $inv['stock'] > 10 ? 'bg-success' : ($inv['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                                    <?php echo $inv['stock']; ?>
                                                </span>
                                            </td>
                                            <td>$<?php echo number_format($inv['price'], 2); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Stock</h5>
                        <small class="text-muted">Tabla: historial_stock (con Trigger)</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Anterior</th>
                                        <th>Nuevo</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($hist = $historial_stock->fetch_assoc()): ?>
                                        <tr>
                                            <td><small><?php echo htmlspecialchars($hist['producto_nombre']); ?></small></td>
                                            <td><?php echo $hist['cantidad_anterior']; ?></td>
                                            <td><?php echo $hist['cantidad_nueva']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $hist['tipo_movimiento'] === 'Venta' ? 'danger' : 'success'; ?>">
                                                    <?php echo $hist['tipo_movimiento']; ?>
                                                </span>
                                            </td>
                                            <td><small><?php echo date('d/m H:i', strtotime($hist['fecha_movimiento'])); ?></small></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Actividad Reciente</h5>
                        <small class="text-muted">Vista: v_actividad_reciente</small>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Usuario</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($act = $actividad->fetch_assoc()): ?>
                                        <tr>
                                            <td><span class="badge bg-info"><?php echo $act['tipo']; ?></span></td>
                                            <td><?php echo htmlspecialchars($act['username']); ?></td>
                                            <td><strong class="text-success">$<?php echo number_format($act['monto'], 2); ?></strong></td>
                                            <td><small><?php echo date('d/m H:i', strtotime($act['fecha'])); ?></small></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>