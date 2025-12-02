<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];

$sql = "CALL sp_historial_usuario(?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordenes = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Órdenes - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6f4e37;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b6f47 100%);
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s;
        }
        
        .order-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .order-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-2"></i>Volver a la Tienda
                </a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="mb-4"><i class="fas fa-box me-2"></i>Mis Órdenes</h2>
        
        <?php if ($ordenes->num_rows === 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-5x text-muted mb-3"></i>
                <h3>No tienes órdenes aún</h3>
                <p class="text-muted">¡Empieza a comprar y tus órdenes aparecerán aquí!</p>
                <a href="index.php" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-bag me-2"></i>Ir a Comprar
                </a>
            </div>
        <?php else: ?>
            <?php while ($orden = $ordenes->fetch_assoc()): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h5 class="mb-0">Orden #<?php echo str_pad($orden['orden_id'], 6, '0', STR_PAD_LEFT); ?></h5>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?>
                                </small>
                            </div>
                            <div class="col-md-3">
                                <span class="badge bg-<?php echo $orden['estado'] === 'Completada' ? 'success' : 'warning'; ?>">
                                    <?php echo $orden['estado']; ?>
                                </span>
                            </div>
                            <div class="col-md-3 text-end">
                                <h4 class="mb-0 text-primary">$<?php echo number_format($orden['total'], 2); ?></h4>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <h6 class="text-muted mb-2">Productos:</h6>
                        <p class="mb-0"><?php echo htmlspecialchars($orden['productos']); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>