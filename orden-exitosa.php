<?php
require_once 'config.php';
requireLogin();

if (!isset($_SESSION['last_order'])) {
    header("Location: index.php");
    exit();
}

$orden_id = $_SESSION['last_order'];
$user_id = $_SESSION['user_id'];


$sql = "SELECT o.orden_id, o.total, o.fecha_orden, o.estado
        FROM ordenes o
        WHERE o.orden_id = ? AND o.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $orden_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orden = $result->fetch_assoc();

if (!$orden) {
    header("Location: index.php");
    exit();
}

$sql = "SELECT od.cantidad, od.precio_unitario, od.subtotal, p.name
        FROM orden_detalles od
        INNER JOIN productos p ON od.product_id = p.product_id
        WHERE od.orden_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$detalles = $stmt->get_result();


unset($_SESSION['last_order']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden Exitosa - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #63491fff;
        }
        
        body {
            background:  linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .success-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background:  linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: white;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
            }
            to {
                transform: scale(1);
            }
        }
        
        .order-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }
        
        .order-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                
                <h2 class="mb-3">¡Compra Exitosa!</h2>
                <p class="text-muted mb-4">Tu orden ha sido procesada correctamente</p>
                
                <div class="order-details">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Número de Orden:</strong>
                        <span>#<?php echo str_pad($orden['orden_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Fecha:</strong>
                        <span><?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Estado:</strong>
                        <span class="badge bg-success"><?php echo $orden['estado']; ?></span>
                    </div>
                    
                    <hr>
                    
                    <h6 class="mb-3">Productos:</h6>
                    <?php while ($detalle = $detalles->fetch_assoc()): ?>
                        <div class="order-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong><?php echo htmlspecialchars($detalle['name']); ?></strong>
                                    <div class="text-muted small">Cantidad: <?php echo $detalle['cantidad']; ?> x $<?php echo number_format($detalle['precio_unitario'], 2); ?></div>
                                </div>
                                <div>
                                    <strong>$<?php echo number_format($detalle['subtotal'], 2); ?></strong>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                        <strong class="fs-5">Total:</strong>
                        <strong class="fs-4" style="color: var(--primary-color);">$<?php echo number_format($orden['total'], 2); ?></strong>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Recibirás un correo de confirmación en breve
                </div>
                
                <div class="d-grid gap-2">
                    <a href="mis-ordenes.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-box me-2"></i>Ver Mis Órdenes
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="fas fa-home me-2"></i>Volver a la Tienda
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>