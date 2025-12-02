<?php
require_once 'config.php';
requireAdmin();

$sql = "SELECT COUNT(*) as total FROM usuarios WHERE role = 'Cliente'";
$total_clientes = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM productos WHERE activo = 1";
$total_productos = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as total FROM ordenes WHERE estado = 'Completada'";
$total_ordenes = $conn->query($sql)->fetch_assoc()['total'];

$sql = "SELECT SUM(total) as total FROM ordenes WHERE estado = 'Completada'";
$ventas_totales = $conn->query($sql)->fetch_assoc()['total'] ?? 0;

$sql = "CALL sp_productos_mas_vendidos(5)";
$productos_vendidos = $conn->query($sql);
$conn->next_result();


$sql = "SELECT o.orden_id, o.total, o.fecha_orden, u.username 
        FROM ordenes o 
        INNER JOIN usuarios u ON o.usuario_id = u.id 
        WHERE o.estado = 'Completada'
        ORDER BY o.fecha_orden DESC 
        LIMIT 5";
$ordenes_recientes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-bg: #8b6f47;
            --sidebar-hover: #63491fff ;
            --sidebar-width: 255px;
           
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
    
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background:   linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar .brand {
            padding: 20px;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar .brand i {
            font-size: 2rem;
        }
        
        .sidebar nav {
            padding: 20px 0;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .nav-link:hover,
        .nav-link.active {
            color: white;
            background: rgba(0, 0, 0, 0.2);
            border-left-color: white;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
    
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-header h2 {
            margin: 0;
            color: #333;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header h2 i {
            color: #8b6f47;
        }
        
    
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-card .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 15px;
        }
        
        .stat-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 10px 0;
            color: #333;
        }
        
        .stat-card p {
            color: #666;
            margin: 0;
            font-size: 0.95rem;
        }
        
        .bg-brown { background: linear-gradient(135deg, #8b6f47, #6f4e37); }
        .bg-orange { background: linear-gradient(135deg, #d2691e, #cd853f); }
        .bg-green { background: linear-gradient(135deg, #28a745, #20c997); }
        .bg-blue { background: linear-gradient(135deg, #17a2b8, #0056b3); }
        

        .content-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .content-card .card-header {
            background: #f8f9fa;
            padding: 15px 20px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .content-card .card-header h5 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .content-card .card-body {
            padding: 20px;
        }
        
        
        .table {
            margin: 0;
        }
        
        .table thead {
            background: #f8f9fa;
        }
        
        .table thead th {
            border: none;
            padding: 12px;
            font-weight: 600;
            color: #495057;
        }
        
        .table tbody td {
            padding: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .text-success {
            color: #28a745 !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-coffee"></i>
            <span><?php echo SITE_NAME; ?></span>
        </div>
        <nav>
            <a class="nav-link active" href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link" href="admin-productos.php">
                <i class="fas fa-box"></i>
                <span>Productos</span>
            </a>
            <a class="nav-link" href="admin-usuarios.php">
                <i class="fas fa-users"></i>
                <span>Usuarios</span>
            </a>
            <a class="nav-link" href="admin-ordenes.php">
                <i class="fas fa-shopping-bag"></i>
                <span>Órdenes</span>
            </a>
            <a class="nav-link" href="admin-reportes.php">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
            <a class="nav-link" href="index.php" style="margin-top: 40px;">
                <i class="fas fa-store"></i>
                <span>Ver Tienda</span>
            </a>
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </nav>
    </div>


    <div class="main-content">
        <div class="page-header">
            <h2>
                <i class="fas fa-tachometer-alt"></i>
                Panel de Control
            </h2>
        </div>

        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon bg-brown">
                    <i class="fas fa-users"></i>
                </div>
                <h3><?php echo $total_clientes; ?></h3>
                <p>Total Clientes</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-orange">
                    <i class="fas fa-box"></i>
                </div>
                <h3><?php echo $total_productos; ?></h3>
                <p>Productos Activos</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-green">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3><?php echo $total_ordenes; ?></h3>
                <p>Órdenes Completadas</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon bg-blue">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <h3>$<?php echo number_format($ventas_totales, 2); ?></h3>
                <p>Ventas Totales</p>
            </div>
        </div>

    
        <div class="row">
            <div class="col-md-6">
                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-fire me-2"></i>Productos Más Vendidos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Vendidos</th>
                                        <th>Ingresos</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($productos_vendidos && $productos_vendidos->num_rows > 0): ?>
                                        <?php while ($producto = $productos_vendidos->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($producto['name']); ?></strong></td>
                                                <td><span class="badge bg-primary"><?php echo $producto['total_vendido']; ?></span></td>
                                                <td><strong class="text-success">$<?php echo number_format($producto['ingresos_totales'], 2); ?></strong></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No hay datos disponibles</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="content-card">
                    <div class="card-header">
                        <h5><i class="fas fa-clock me-2"></i>Órdenes Recientes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Orden</th>
                                        <th>Cliente</th>
                                        <th>Total</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($ordenes_recientes->num_rows > 0): ?>
                                        <?php while ($orden = $ordenes_recientes->fetch_assoc()): ?>
                                            <tr>
                                                <td><strong>#<?php echo str_pad($orden['orden_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                <td><?php echo htmlspecialchars($orden['username']); ?></td>
                                                <td><strong class="text-success">$<?php echo number_format($orden['total'], 2); ?></strong></td>
                                                <td><small><?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></small></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No hay órdenes recientes</td>
                                        </tr>
                                    <?php endif; ?>
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