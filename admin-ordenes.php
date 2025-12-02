<?php
require_once 'config.php';
requireAdmin();


$sql = "SELECT o.orden_id, o.total, o.estado, o.fecha_orden, u.username, u.email,
        (SELECT COUNT(*) FROM orden_detalles WHERE orden_id = o.orden_id) as items_count
        FROM ordenes o
        INNER JOIN usuarios u ON o.usuario_id = u.id
        ORDER BY o.fecha_orden DESC";
$ordenes = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Órdenes - <?php echo SITE_NAME; ?></title>
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
                <a class="nav-link active" href="admin-ordenes.php">
                    <i class="fas fa-shopping-bag me-2"></i>Órdenes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin-reportes.php">
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
        <h2 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>Gestión de Órdenes</h2>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Orden #</th>
                                <th>Cliente</th>
                                <th>Email</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($orden = $ordenes->fetch_assoc()): ?>
                                <tr>
                                    <td><strong>#<?php echo str_pad($orden['orden_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                    <td><?php echo htmlspecialchars($orden['username']); ?></td>
                                    <td><small><?php echo htmlspecialchars($orden['email']); ?></small></td>
                                    <td><span class="badge bg-secondary"><?php echo $orden['items_count']; ?> items</span></td>
                                    <td><strong class="text-success">$<?php echo number_format($orden['total'], 2); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $orden['estado'] === 'Completada' ? 'success' : 
                                                ($orden['estado'] === 'Procesando' ? 'warning' : 
                                                ($orden['estado'] === 'Cancelada' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo $orden['estado']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="verDetalle(<?php echo $orden['orden_id']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="detalleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Detalle de Orden</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalleContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalle(ordenId) {
            const modal = new bootstrap.Modal(document.getElementById('detalleModal'));
            modal.show();
            
            fetch('ajax/orden-detalle.php?orden_id=' + ordenId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detalleContent').innerHTML = html;
                });
        }
    </script>
</body>
</html>