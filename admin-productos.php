<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error = '';

if (isset($_GET['delete'])) {
    $product_id = (int)$_GET['delete'];
    $sql = "UPDATE productos SET activo = 0 WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    
    if ($stmt->execute()) {
        $success = "Producto eliminado exitosamente.";
    } else {
        $error = "Error al eliminar producto.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $name = cleanInput($_POST['name']);
    $price = (float)$_POST['price'];
    $description = cleanInput($_POST['description']);
    $stock = (int)$_POST['stock'];
    
    if (empty($name) || $price <= 0) {
        $error = "Nombre y precio son requeridos.";
    } else {
        $sql = "INSERT INTO productos (name, price, description, stock) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsi", $name, $price, $description, $stock);
        
        if ($stmt->execute()) {
            $success = "Producto creado exitosamente.";
        } else {
            $error = "Error al crear producto.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = (int)$_POST['product_id'];
    $name = cleanInput($_POST['name']);
    $price = (float)$_POST['price'];
    $description = cleanInput($_POST['description']);
    $stock = (int)$_POST['stock'];
    
    if (empty($name) || $price <= 0) {
        $error = "Nombre y precio son requeridos.";
    } else {
        $sql = "UPDATE productos SET name = ?, price = ?, description = ?, stock = ? WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsii", $name, $price, $description, $stock, $product_id);
        
        if ($stmt->execute()) {
            $success = "Producto actualizado exitosamente.";
        } else {
            $error = "Error al actualizar producto.";
        }
    }
}

$sql = "SELECT * FROM productos WHERE activo = 1 ORDER BY product_id DESC";
$productos = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">

</head>
<body>

    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-coffee"></i>
            <span><?php echo SITE_NAME; ?></span>
        </div>
        <nav>
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            <a class="nav-link active" href="admin-productos.php">
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
                <i class="fas fa-box"></i>
                Gestión de Productos
            </h2>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#createProductModal">
                <i class="fas fa-plus me-2"></i>Nuevo Producto
            </button>
        </div>

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

        <div class="content-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($producto = $productos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $producto['product_id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($producto['name']); ?></strong></td>
                                <td><strong class="text-success">$<?php echo number_format($producto['price'], 2); ?></strong></td>
                                <td>
                                    <span class="badge <?php echo $producto['stock'] > 10 ? 'bg-success' : ($producto['stock'] > 0 ? 'bg-warning' : 'bg-danger'); ?>">
                                        <?php echo $producto['stock']; ?>
                                    </span>
                                </td>
                                <td><small><?php echo substr(htmlspecialchars($producto['description']), 0, 50); ?>...</small></td>
                                <td>
                                    <button class="btn btn-warning btn-sm" 
                                            onclick='editProduct(<?php echo json_encode($producto); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <a href="?delete=<?php echo $producto['product_id']; ?>" 
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('¿Eliminar este producto?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

                
    <div class="modal fade" id="createProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" min="0" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="create_product" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <input type="number" class="form-control" name="price" id="edit_price" step="0.01" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update_product" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.product_id;
            document.getElementById('edit_name').value = product.name;
            document.getElementById('edit_price').value = product.price;
            document.getElementById('edit_stock').value = product.stock;
            document.getElementById('edit_description').value = product.description;
            
            new bootstrap.Modal(document.getElementById('editProductModal')).show();
        }
    </script>
</body>
</html>