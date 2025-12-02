<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];


$sql = "SELECT COUNT(*) as count FROM carrito WHERE usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    header("Location: carrito.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_compra'])) {
    // Llamar al procedimiento almacenado SIN los $$
    $sql = "CALL sp_procesar_checkout(?, @orden_id, @total)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        
        $result = $conn->query("SELECT @orden_id as orden_id, @total as total");
        $orden = $result->fetch_assoc();
        
        $_SESSION['last_order'] = $orden['orden_id'];
        header("Location: orden-exitosa.php");
        exit();
    } else {
        $error = "Error al procesar la compra. Por favor intente nuevamente.";
    }
}


$sql = "SELECT c.cantidad, p.name, p.price, (c.cantidad * p.price) as subtotal
        FROM carrito c
        INNER JOIN productos p ON c.product_id = p.product_id
        WHERE c.usuario_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

$total = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $items[] = $item;
    $total += $item['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6f4e37;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b6f47 100%);
        }
        
        .checkout-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        
        .checkout-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .order-item {
            border-bottom: 1px solid #e9ecef;
            padding: 15px 0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .btn-confirm {
            background: var(--primary-color);
            color: white;
            padding: 15px 40px;
            font-weight: 600;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
        }
        
        .btn-confirm:hover {
            background: #8b6f47;
            color: white;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .step {
            flex: 1;
            text-align: center;
            position: relative;
        }
        
        .step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #dee2e6;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            position: relative;
            z-index: 1;
        }
        
        .step-inactive .step-circle {
            background: #dee2e6;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
   
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?>
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="checkout-container">
            <h2 class="text-center mb-4"><i class="fas fa-check-circle me-2"></i>Finalizar Compra</h2>
            
            
            <div class="step-indicator">
                <div class="step">
                    <div class="step-circle">1</div>
                    <div class="mt-2">Carrito</div>
                </div>
                <div class="step">
                    <div class="step-circle">2</div>
                    <div class="mt-2">Checkout</div>
                </div>
                <div class="step step-inactive">
                    <div class="step-circle">3</div>
                    <div class="mt-2">Confirmación</div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-7">
                    <div class="checkout-card">
                        <h4 class="mb-4"><i class="fas fa-user me-2"></i>Información del Cliente</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usuario</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="checkout-card">
                        <h4 class="mb-4"><i class="fas fa-credit-card me-2"></i>Método de Pago</h4>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="metodo_pago" id="pago_efectivo" checked>
                            <label class="form-check-label" for="pago_efectivo">
                                <i class="fas fa-money-bill-wave me-2"></i>Pago en Efectivo (Contraentrega)
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="metodo_pago" id="pago_tarjeta">
                            <label class="form-check-label" for="pago_tarjeta">
                                <i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito/Débito
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="metodo_pago" id="pago_transferencia">
                            <label class="form-check-label" for="pago_transferencia">
                                <i class="fas fa-university me-2"></i>Transferencia Bancaria
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="checkout-card">
                        <h4 class="mb-4"><i class="fas fa-shopping-bag me-2"></i>Resumen del Pedido</h4>
                        
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                        <div class="text-muted small">Cantidad: <?php echo $item['cantidad']; ?></div>
                                    </div>
                                    <div class="text-end">
                                        <strong>$<?php echo number_format($item['subtotal'], 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($total, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Envío:</span>
                                <span class="text-success">GRATIS</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-5">Total:</strong>
                                <strong class="fs-4 text-primary">$<?php echo number_format($total, 2); ?></strong>
                            </div>
                            
                            <form method="POST">
                                <button type="submit" name="confirmar_compra" class="btn btn-confirm w-100">
                                    <i class="fas fa-lock me-2"></i>Confirmar Compra
                                </button>
                            </form>
                            
                            <a href="carrito.php" class="btn btn-outline-secondary w-100 mt-2">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Carrito
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>