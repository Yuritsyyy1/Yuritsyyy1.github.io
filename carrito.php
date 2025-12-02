<?php
require_once 'config.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Actualizar cantidad
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cantidad'])) {
    $carrito_id = (int)$_POST['carrito_id'];
    $nueva_cantidad = (int)$_POST['cantidad'];
    
    if ($nueva_cantidad > 0) {
        // Verificar stock disponible
        $sql = "SELECT c.product_id, p.stock 
                FROM carrito c
                INNER JOIN productos p ON c.product_id = p.product_id
                WHERE c.carrito_id = ? AND c.usuario_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $carrito_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            
            if ($nueva_cantidad <= $item['stock']) {
                $sql = "UPDATE carrito SET cantidad = ? WHERE carrito_id = ? AND usuario_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iii", $nueva_cantidad, $carrito_id, $user_id);
                
                if ($stmt->execute()) {
                    $success = "Cantidad actualizada";
                    header("Location: carrito.php?updated=1");
                    exit();
                } else {
                    $error = "Error al actualizar";
                }
            } else {
                $error = "Stock insuficiente (disponible: " . $item['stock'] . ")";
            }
        }
    }
}

// Eliminar del carrito
if (isset($_GET['remove'])) {
    $carrito_id = (int)$_GET['remove'];
    
    $sql = "DELETE FROM carrito WHERE carrito_id = ? AND usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $carrito_id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: carrito.php?removed=1");
        exit();
    } else {
        $error = "Error al eliminar producto";
    }
}

// Mensajes de éxito
if (isset($_GET['updated'])) {
    $success = "Carrito actualizado correctamente";
}
if (isset($_GET['removed'])) {
    $success = "Producto eliminado del carrito";
}

// Obtener items del carrito CON IMAGEN
$sql = "SELECT c.carrito_id, c.cantidad, c.fecha_agregado,
        p.product_id, p.name, p.price, p.stock, p.imagen,
        (c.cantidad * p.price) as subtotal
        FROM carrito c
        INNER JOIN productos p ON c.product_id = p.product_id
        WHERE c.usuario_id = ?
        ORDER BY c.fecha_agregado DESC";
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
    <title>Carrito de Compras - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6f4e37;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b6f47 100%);
            box-shadow: 0 2px 15px rgba(0,0,0,0.2);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .cart-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 15px;
        }
        
        .page-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .cart-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .cart-item {
            border-bottom: 2px solid #f0f0f0;
            padding: 25px 0;
            display: flex;
            align-items: center;
            gap: 25px;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px 15px;
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-img {
            width: 120px;
            height: 120px;
            border-radius: 15px;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            overflow: hidden;
            position: relative;
        }
        
        .cart-item-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .cart-item-img-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #c4a57b, #d4af37);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
        }
        
        .cart-item-details {
            flex-grow: 1;
        }
        
        .cart-item-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        
        .cart-item-price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        .stock-info {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 12px;
        }
        
        .quantity-input {
            width: 80px;
            text-align: center;
            padding: 8px;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        
        .btn-quantity {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: none;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1.2rem;
        }
        
        .btn-quantity:hover {
            background: #8b6f47;
            transform: scale(1.15);
        }
        
        .btn-remove {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 600;
        }
        
        .btn-remove:hover {
            background: linear-gradient(135deg, #ee5a24, #d63031);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(238, 90, 36, 0.4);
        }
        
        .cart-summary {
            background: linear-gradient(135deg, #6f4e37, #8b6f47);
            color: white;
            padding: 30px;
            border-radius: 20px;
            position: sticky;
            top: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            font-size: 1.05rem;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-total {
            font-size: 2rem;
            font-weight: 700;
            margin-top: 10px;
        }
        
        .btn-checkout {
            background: white;
            color: var(--primary-color);
            border: none;
            padding: 18px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.2rem;
            width: 100%;
            transition: all 0.3s;
            margin-top: 20px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255,255,255,0.3);
            color: var(--primary-color);
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
        }
        
        .empty-cart i {
            font-size: 6rem;
            color: #dee2e6;
            margin-bottom: 25px;
        }
        
        .empty-cart h3 {
            color: #6c757d;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <div class="ms-auto">
                <a href="index.php" class="btn btn-outline-light">
                    <i class="fas fa-store me-2"></i>Seguir Comprando
                </a>
            </div>
        </div>
    </nav>

    <div class="cart-container">
        <h1 class="page-title">
            <i class="fas fa-shopping-cart me-3"></i>
            Mi Carrito de Compras
        </h1>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($items)): ?>
            <div class="cart-card">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p class="text-muted mb-4">¡Descubre nuestros deliciosos cafés!</p>
                    <a href="index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-coffee me-2"></i>Explorar Productos
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-card">
                        <h4 class="mb-4">
                            <i class="fas fa-list me-2"></i>
                            Productos (<?php echo count($items); ?>)
                        </h4>
                        
                        <?php foreach ($items as $item): 
                            // RUTA UNIFICADA - Todas las imágenes en img/
                            $imagen_path = 'img/' . $item['imagen'];
                        ?>
                            <div class="cart-item">
                                <div class="cart-item-img">
                                    <?php if (!empty($item['imagen']) && file_exists($imagen_path)): ?>
                                        <img src="<?php echo $imagen_path; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <div class="cart-item-img-placeholder">
                                            <i class="fas fa-mug-hot"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="cart-item-details">
                                    <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="cart-item-price">$<?php echo number_format($item['price'], 2); ?> c/u</div>
                                    <div class="stock-info">
                                        <i class="fas fa-box me-1"></i>
                                        Stock disponible: <?php echo $item['stock']; ?>
                                    </div>
                                </div>
                                
                                <div class="quantity-control">
                                    <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden" name="carrito_id" value="<?php echo $item['carrito_id']; ?>">
                                        <button type="button" class="btn-quantity" onclick="decrementar(this.form)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="cantidad" class="quantity-input" 
                                               value="<?php echo $item['cantidad']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>" readonly>
                                        <button type="button" class="btn-quantity" onclick="incrementar(this.form, <?php echo $item['stock']; ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                        <button type="submit" name="update_cantidad" class="btn btn-sm btn-success ms-2">
                                            <i class="fas fa-check"></i> Actualizar
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="text-end" style="min-width: 150px;">
                                    <div class="fw-bold fs-4 text-success mb-3">
                                        $<?php echo number_format($item['subtotal'], 2); ?>
                                    </div>
                                    <a href="?remove=<?php echo $item['carrito_id']; ?>" 
                                       class="btn btn-remove"
                                       onclick="return confirm('¿Eliminar <?php echo htmlspecialchars($item['name']); ?>?')">
                                        <i class="fas fa-trash me-2"></i>Eliminar
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h4 class="mb-4">
                            <i class="fas fa-receipt me-2"></i>
                            Resumen del Pedido
                        </h4>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-box me-2"></i>Productos:</span>
                            <span><?php echo count($items); ?> item(s)</span>
                        </div>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-dollar-sign me-2"></i>Subtotal:</span>
                            <span>$<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-truck me-2"></i>Envío:</span>
                            <span class="text-warning fw-bold">GRATIS</span>
                        </div>
                        
                        <div class="summary-row">
                            <span><i class="fas fa-percentage me-2"></i>Impuestos:</span>
                            <span>$0.00</span>
                        </div>
                        
                        <div class="summary-row mt-3 pt-3" style="border-top: 2px solid white;">
                            <strong style="font-size: 1.3rem;">
                                <i class="fas fa-tag me-2"></i>Total:
                            </strong>
                            <strong class="summary-total">$<?php echo number_format($total, 2); ?></strong>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-checkout">
                            <i class="fas fa-credit-card me-2"></i>Proceder al Pago
                        </a>
                        
                        <div class="text-center mt-3">
                            <small>
                                <i class="fas fa-shield-alt me-2"></i>
                                Compra 100% segura y protegida
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function incrementar(form, max) {
            const input = form.querySelector('input[name="cantidad"]');
            const valor = parseInt(input.value);
            if (valor < max) {
                input.value = valor + 1;
            }
        }
        
        function decrementar(form) {
            const input = form.querySelector('input[name="cantidad"]');
            const valor = parseInt(input.value);
            if (valor > 1) {
                input.value = valor - 1;
            }
        }
    </script>
</body>
</html>