<?php
require_once '../config.php';

if (!isAdmin()) {
    echo "<div class='alert alert-danger'>Acceso denegado</div>";
    exit();
}

$orden_id = isset($_GET['orden_id']) ? (int)$_GET['orden_id'] : 0;

$sql = "SELECT o.*, u.username, u.email 
        FROM ordenes o
        INNER JOIN usuarios u ON o.usuario_id = u.id
        WHERE o.orden_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$orden = $stmt->get_result()->fetch_assoc();

if (!$orden) {
    echo "<div class='alert alert-danger'>Orden no encontrada</div>";
    exit();
}


$sql = "SELECT od.*, p.name 
        FROM orden_detalles od
        INNER JOIN productos p ON od.product_id = p.product_id
        WHERE od.orden_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $orden_id);
$stmt->execute();
$detalles = $stmt->get_result();
?>

<div class="order-detail">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Información del Cliente</h6>
            <p class="mb-1"><strong>Usuario:</strong> <?php echo htmlspecialchars($orden['username']); ?></p>
            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($orden['email']); ?></p>
        </div>
        <div class="col-md-6">
            <h6>Información de la Orden</h6>
            <p class="mb-1"><strong>Orden #:</strong> <?php echo str_pad($orden['orden_id'], 6, '0', STR_PAD_LEFT); ?></p>
            <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($orden['fecha_orden'])); ?></p>
            <p class="mb-1"><strong>Estado:</strong> 
                <span class="badge bg-<?php echo $orden['estado'] === 'Completada' ? 'success' : 'warning'; ?>">
                    <?php echo $orden['estado']; ?>
                </span>
            </p>
        </div>
    </div>

    <h6 class="mb-3">Productos</h6>
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($detalle = $detalles->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['name']); ?></td>
                    <td><?php echo $detalle['cantidad']; ?></td>
                    <td>$<?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td><strong>$<?php echo number_format($detalle['subtotal'], 2); ?></strong></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th><span class="text-primary fs-5">$<?php echo number_format($orden['total'], 2); ?></span></th>
            </tr>
        </tfoot>
    </table>
</div>