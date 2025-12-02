<?php
require_once 'config.php';


$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY product_id DESC";
$productos = $conn->query($sql);


$sql_ofertas = "SELECT * FROM productos WHERE activo = 1 AND price < 50 ORDER BY price ASC LIMIT 4";
$productos_oferta = $conn->query($sql_ofertas);


if ($productos_oferta->num_rows < 4) {
    $sql_ofertas = "SELECT * FROM productos WHERE activo = 1 ORDER BY price ASC LIMIT 4";
    $productos_oferta = $conn->query($sql_ofertas);
}


$cart_count = 0;
if (isLoggedIn()) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT SUM(cantidad) as total FROM carrito WHERE usuario_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $cart_count = $row['total'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Tienda de Café</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #63491fff ;
            --secondary-color:  #8b6f47;
            --accent-color: #d4af37;
            --dark-bg: #2c2416;
          
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b6f47 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: white !important;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);,
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 600"><rect fill="%236f4e37" width="1200" height="600"/></svg>');
            background-size: cover;
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(212, 175, 55, 0.2) 0%, transparent 50%);
            animation: pulse 15s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
        
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
            text-shadow: 3px 3px 6px rgba(0,0,0,0.3);
        }
        
        .hero-section p {
            position: relative;
            z-index: 1;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .section-title::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--accent-color));
            margin: 15px auto 0;
            border-radius: 2px;
        }
        
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            background: white;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }
        
        .product-img-container {
            height: 250px;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
        }
        
        .product-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-img-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .btn-add-cart {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-add-cart:hover {
            background: #8b6f47;
            transform: scale(1.05);
            color: white;
        }
        
        .cart-badge {
            background: #dc3545;
            border-radius: 50%;
            padding: 2px 8px;
            font-size: 0.8rem;
            position: absolute;
            top: -8px;
            right: -8px;
        }
        
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            z-index: 1;
        }
        
    
        .ofertas-section {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
            padding: 80px 0;
            margin: 60px 0;
            position: relative;
        }
        
        .ofertas-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="%23d4af37" opacity="0.1"/></svg>');
            background-size: 30px 30px;
        }
        
        .offer-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 10px 15px;
            border-radius: 50%;
            font-weight: 700;
            font-size: 0.9rem;
            box-shadow: 0 4px 15px rgba(238, 90, 36, 0.4);
            z-index: 2;
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .offer-card {
            position: relative;
            background: white;
            border: 3px solid #ff6b6b;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .offer-card .product-img-container {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        }
        
        .offer-card .product-price {
            color: #ee5a24;
        }
        
        .offer-card .original-price {
            text-decoration: line-through;
            color: #999;
            font-size: 1rem;
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-2"></i>Inicio</a>
                    </li>
                    
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link position-relative" href="carrito.php">
                                <i class="fas fa-shopping-cart me-2"></i>Carrito
                                <?php if ($cart_count > 0): ?>
                                    <span class="cart-badge"><?php echo $cart_count; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mis-ordenes.php">
                                <i class="fas fa-box me-2"></i>Mis Órdenes
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-2"></i><?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>


    <div class="hero-section">
        <div class="container">
            <h1><i class="fas fa-coffee"></i> Bienvenido a <?php echo SITE_NAME; ?></h1>
            <p class="lead">Los mejores cafés artesanales para tu día</p>
            <a href="#productos" class="btn btn-light btn-lg mt-3">
                <i class="fas fa-arrow-down me-2"></i>Ver Productos
            </a>
        </div>
    </div>


    <div class="ofertas-section">
        <div class="container">
            <div class="section-title">
                <h2><i class="fas fa-fire me-2"></i>¡Ofertas Especiales!</h2>
                <p class="text-muted">Aprovecha estos precios increíbles por tiempo limitado</p>
            </div>
            
            <div class="row g-4">
                <?php if ($productos_oferta->num_rows > 0): ?>
                    <?php 
                    $contador_ofertas = 0;
                    while ($oferta = $productos_oferta->fetch_assoc()): 
                        // RUTA UNIFICADA - Todas las imágenes en img/
                        $imagen_path = 'img/' . $oferta['imagen'];
                        $descuento = rand(15, 30);
                        $precio_original = $oferta['price'] * (1 + ($descuento / 100));
                        $contador_ofertas++;
                    ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card product-card offer-card shadow">
                                <div class="offer-badge">
                                    -<?php echo $descuento; ?>%
                                </div>
                                <div class="product-img-container">
                                    <?php if (!empty($oferta['imagen']) && file_exists($imagen_path)): ?>
                                        <img src="<?php echo $imagen_path; ?>" alt="<?php echo htmlspecialchars($oferta['name']); ?>" class="product-img">
                                    <?php else: ?>
                                        <div class="product-img-placeholder">
                                            <i class="fas fa-mug-hot"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span class="stock-badge text-danger">
                                        <i class="fas fa-fire me-1"></i>¡OFERTA!
                                    </span>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($oferta['name']); ?></h5>
                                    <p class="card-text text-muted small">
                                        <?php echo htmlspecialchars(substr($oferta['description'], 0, 70)) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <div>
                                            <span class="original-price">$<?php echo number_format($precio_original, 2); ?></span>
                                            <span class="product-price">$<?php echo number_format($oferta['price'], 2); ?></span>
                                        </div>
                                        <?php if ($oferta['stock'] > 0): ?>
                                            <button class="btn btn-add-cart" style="background: #ee5a24;" onclick="addToCart(<?php echo $oferta['product_id']; ?>)">
                                                <i class="fas fa-cart-plus me-2"></i>¡Comprar!
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary" disabled>
                                                Agotado
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-box me-1"></i>
                                            <?php echo $oferta['stock']; ?> disponibles
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">No hay ofertas disponibles en este momento</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container my-5" id="productos">
        <div class="section-title">
            <h2><i class="fas fa-star me-2"></i>Todos Nuestros Productos</h2>
            <p class="text-muted">Nuestra selección premium de cafés</p>
        </div>
        
        <div class="row g-4">
            <?php 
            $productos->data_seek(0);
            while ($producto = $productos->fetch_assoc()): 
            
                $imagen_path = 'img/' . $producto['imagen'];
            ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card product-card shadow">
                        <div class="product-img-container">
                            <?php if (!empty($producto['imagen']) && file_exists($imagen_path)): ?>
                                <img src="<?php echo $imagen_path; ?>" alt="<?php echo htmlspecialchars($producto['name']); ?>" class="product-img">
                            <?php else: ?>
                                <div class="product-img-placeholder">
                                    <i class="fas fa-mug-hot"></i>
                                </div>
                            <?php endif; ?>
                            <span class="stock-badge <?php echo $producto['stock'] > 10 ? 'text-success' : 'text-warning'; ?>">
                                <i class="fas fa-box me-1"></i><?php echo $producto['stock']; ?> disponibles
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($producto['name']); ?></h5>
                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($producto['description'], 0, 80)) . '...'; ?>
                            </p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="product-price">$<?php echo number_format($producto['price'], 2); ?></span>
                                <?php if ($producto['stock'] > 0): ?>
                                    <button class="btn btn-add-cart" onclick="addToCart(<?php echo $producto['product_id']; ?>)">
                                        <i class="fas fa-cart-plus me-2"></i>Agregar
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>
                                        Agotado
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>


    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><i class="fas fa-coffee me-2"></i><?php echo SITE_NAME; ?></h5>
                    <p class="small">El mejor café artesanal de la región</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Contacto</h5>
                    <p class="small">
                        <i class="fas fa-envelope me-2"></i>coffeeyu@gmail.com<br>
                        <i class="fas fa-phone me-2"></i>+52 652 106 4015
                    </p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Síguenos</h5>
                    <a href="#" class="text-white me-3"><i class="fab fa-facebook fa-lg"></i></a>
                    <a href="#" class="text-white me-3"><i class="fab fa-instagram fa-lg"></i></a>
                    <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                </div>
            </div>
            <hr class="bg-white">
            <p class="mb-0">&copy; 2025 <?php echo SITE_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function addToCart(productId) {
            <?php if (!isLoggedIn()): ?>
                Swal.fire({
                    title: 'Inicia Sesión',
                    text: 'Necesitas iniciar sesión para agregar productos al carrito',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Iniciar Sesión',
                    confirmButtonColor: '#6f4e37',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'login.php';
                    }
                });
                return;
            <?php endif; ?>
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('cantidad', 1);
            
            fetch('ajax/add-to-cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Agregado!',
                        text: 'Producto agregado al carrito',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        confirmButtonColor: '#6f4e37'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#6f4e37'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema al agregar el producto',
                    icon: 'error',
                    confirmButtonColor: '#6f4e37'
                });
            });
        }
        
    
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>