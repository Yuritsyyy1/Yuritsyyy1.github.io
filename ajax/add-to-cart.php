<?php
require_once '../config.php';


header('Content-Type: application/json');


if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;


if ($product_id <= 0 || $cantidad <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}


$sql = "SELECT product_id, name, stock FROM productos WHERE product_id = ? AND activo = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit();
}

$producto = $result->fetch_assoc();


if ($producto['stock'] < $cantidad) {
    echo json_encode(['success' => false, 'message' => 'Stock insuficiente. Disponible: ' . $producto['stock']]);
    exit();
}

$sql = "SELECT carrito_id, cantidad FROM carrito WHERE usuario_id = ? AND product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
   
    $carrito = $result->fetch_assoc();
    $nueva_cantidad = $carrito['cantidad'] + $cantidad;
    
 
    if ($nueva_cantidad > $producto['stock']) {
        echo json_encode([
            'success' => false, 
            'message' => 'No puedes agregar más. Stock disponible: ' . $producto['stock'] . ', ya tienes: ' . $carrito['cantidad']
        ]);
        exit();
    }
    
    $sql = "UPDATE carrito SET cantidad = ? WHERE carrito_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nueva_cantidad, $carrito['carrito_id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Cantidad actualizada en el carrito'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
} else {
    
    $sql = "INSERT INTO carrito (usuario_id, product_id, cantidad) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $user_id, $product_id, $cantidad);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Producto agregado al carrito'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar al carrito']);
    }
}
?>