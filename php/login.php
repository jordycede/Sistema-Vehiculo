
<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['nombre_usuario'];
    $clave = $_POST['contraseña'];

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cambio aquí: Verificar contraseña sin hash para el admin (solo para desarrollo)
    if ($user) {
        if ($user['nombre_usuario'] === 'admin' && $clave === '123') {
            // Login directo para admin (solo en desarrollo)
            $_SESSION['usuario'] = $user['nombre_usuario'];
            $_SESSION['user_id'] = $user['id'];
            
            if ($user['es_admin']) {
                header("Location: ../admin.html");
            } else {
                header("Location: ../user.html");
            }
            exit;
        } elseif (password_verify($clave, $user['contraseña'])) {
            // Login normal para otros usuarios
            $_SESSION['usuario'] = $user['nombre_usuario'];
            $_SESSION['user_id'] = $user['id'];
            
            if ($user['es_admin']) {
                header("Location: ../admin.html");
            } else {
                header("Location: ../user.html");
            }
            exit;
        }
    }
    
    echo "❌ Usuario o contraseña incorrectos.";
}
?>