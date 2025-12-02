<?php
require_once 'config.php';

$error = '';
$success = '';


if (isLoggedIn()) {
    if (isAdmin()) {
        header("Location: dashboard.php");
    } else {
        header("Location: index.php");
    }
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = cleanInput($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } else {
        $sql = "SELECT id, username, password, email, role FROM usuarios WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                if ($user['role'] === 'Admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    }
}

// Procesar registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = cleanInput($_POST['reg_username']);
    $email = cleanInput($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['reg_confirm_password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "Por favor, complete todos los campos.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres.";
    } else {
      
        $sql = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "El usuario o email ya existe.";
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (username, password, email, role) VALUES (?, ?, ?, 'Cliente')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $username, $hashed_password, $email);
            
            if ($stmt->execute()) {
                $success = "Registro exitoso. Ahora puede iniciar sesión.";
            } else {
                $error = "Error al registrar usuario.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-container {
            max-width: 450px;
            margin: 0 auto;
        }
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .auth-header i {
            font-size: 3rem;
            margin-bottom: 15px;
        }
        .auth-body {
            padding: 40px;
        }
        .form-control:focus {
            border-color: #8b6f47;
            box-shadow: 0 0 0 0.2rem rgba(75, 55, 25, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #63491fff 0%, #8b6f47 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
            transition: transform 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(75, 55, 25, 0.25);
        }
        .nav-tabs .nav-link {
            color: #553c20ff;
            font-weight: 600;
        }
        .nav-tabs .nav-link.active {
            color: #553d18ff;
            border-bottom: 3px solid #574429ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <i class="fas fa-coffee"></i>
                    <h2 class="mb-0"><?php echo SITE_NAME; ?></h2>
                    <p class="mb-0">Bienvenido de vuelta</p>
                </div>
                
                <div class="auth-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <ul class="nav nav-tabs mb-4" id="authTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login" type="button">
                                <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button">
                                <i class="fas fa-user-plus me-2"></i>Registrarse
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="authTabsContent">
                        <!-- Login Form -->
                        <div class="tab-pane fade show active" id="login" role="tabpanel">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-user me-2"></i>Usuario</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <button type="submit" name="login" class="btn btn-primary w-100">
                                    <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                                </button>
                            </form>
                        </div>
                        
                        
                        <div class="tab-pane fade" id="register" role="tabpanel">
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-user me-2"></i>Usuario</label>
                                    <input type="text" class="form-control" name="reg_username" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                                    <input type="email" class="form-control" name="reg_email" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-lock me-2"></i>Contraseña</label>
                                    <input type="password" class="form-control" name="reg_password" required minlength="6">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-lock me-2"></i>Confirmar Contraseña</label>
                                    <input type="password" class="form-control" name="reg_confirm_password" required>
                                </div>
                                <button type="submit" name="register" class="btn btn-primary w-100">
                                    <i class="fas fa-user-plus me-2"></i>Registrarse
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Volver a la tienda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>