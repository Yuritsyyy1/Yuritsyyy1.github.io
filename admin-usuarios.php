<?php
require_once 'config.php';
requireAdmin();

$success = '';
$error = '';


if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    
    if ($user_id == $_SESSION['user_id']) {
        $error = "No puedes eliminar tu propia cuenta.";
    } else {
        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            $success = "Usuario eliminado exitosamente.";
        } else {
            $error = "Error al eliminar usuario.";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $role = cleanInput($_POST['role']);
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Todos los campos son requeridos.";
    } else {
        $sql = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "El usuario o email ya existe.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (username, password, email, role) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $username, $hashed_password, $email, $role);
            
            if ($stmt->execute()) {
                $success = "Usuario creado exitosamente.";
            } else {
                $error = "Error al crear usuario.";
            }
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $user_id = (int)$_POST['user_id'];
    $username = cleanInput($_POST['username']);
    $email = cleanInput($_POST['email']);
    $role = cleanInput($_POST['role']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($email)) {
        $error = "El usuario y email son requeridos.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET username = ?, email = ?, role = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $username, $email, $role, $hashed_password, $user_id);
        } else {
            $sql = "UPDATE usuarios SET username = ?, email = ?, role = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $username, $email, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            $success = "Usuario actualizado exitosamente.";
        } else {
            $error = "Error al actualizar usuario.";
        }
    }
}

$sql = "SELECT * FROM usuarios ORDER BY fecha_registro DESC";
$usuarios = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    
</head>
<body>

    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-coffee"></i>
            <?php echo SITE_NAME; ?>
        </div>
        <nav class="mt-3">
            <a class="nav-link" href="dashboard.php">
                <i class="fas fa-mug-hot"></i>
                Dashboard
            </a>
            <a class="nav-link" href="admin-productos.php">
                <i class="fas fa-coffee-bean"></i>
                Productos
            </a>
            <a class="nav-link active" href="admin-usuarios.php">
                <i class="fas fa-users"></i>
                Usuarios
            </a>
            <a class="nav-link" href="admin-ordenes.php">
                <i class="fas fa-receipt"></i>
                Órdenes
            </a>
            <a class="nav-link" href="admin-reportes.php">
                <i class="fas fa-chart-line"></i>
                Reportes
            </a>
            <a class="nav-link mt-4" href="index.php">
                <i class="fas fa-store-alt"></i>
                Ver Tienda
            </a>
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </a>
        </nav>
    </div>


    <div class="main-content">
        <div class="page-header">
            <h2>
                <div class="coffee-icon">
                    <i class="fas fa-users"></i>
                </div>
                Gestión de Usuarios
            </h2>
            <button class="btn btn-add" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
            </button>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="users-card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $usuario['id']; ?></strong></td>
                                <td>
                                    <i class="fas fa-user-circle me-2"></i>
                                    <strong><?php echo htmlspecialchars($usuario['username']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <span class="badge <?php echo $usuario['role'] === 'Admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                        <i class="fas <?php echo $usuario['role'] === 'Admin' ? 'fa-shield-alt' : 'fa-user'; ?> me-1"></i>
                                        <?php echo $usuario['role']; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($usuario['fecha_registro'])); ?></td>
                                <td>
                                    <button class="btn btn-action btn-edit" 
                                            onclick='editUser(<?php echo json_encode($usuario); ?>)'>
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($usuario['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $usuario['id']; ?>" 
                                           class="btn btn-action btn-delete"
                                           onclick="return confirm('¿Eliminar este usuario?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user me-2"></i>Usuario</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
                            <input type="password" class="form-control" name="password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-shield-alt me-2"></i>Rol</label>
                            <select class="form-select" name="role" required>
                                <option value="Cliente">Cliente</option>
                                <option value="Admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="create_user" class="btn btn-add">
                            <i class="fas fa-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-user me-2"></i>Usuario</label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                            <input type="email" class="form-control" name="email" id="edit_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-lock me-2"></i>Nueva Contraseña (dejar vacío para no cambiar)</label>
                            <input type="password" class="form-control" name="password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"><i class="fas fa-shield-alt me-2"></i>Rol</label>
                            <select class="form-select" name="role" id="edit_role" required>
                                <option value="Cliente">Cliente</option>
                                <option value="Admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" name="update_user" class="btn btn-add">
                            <i class="fas fa-save me-2"></i>Actualizar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_role').value = user.role;
            
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
    </script>
</body>
</html>