<?php
require 'conexion.php';
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['nombre_usuario'];
    $clave = $_POST['contraseña'];
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nombre_usuario = :usuario");
    $stmt->execute(['usuario' => $usuario]);

    if ($stmt->rowCount() > 0) {
        echo json_encode([
            "success" => false,
            "message" => "❌ El usuario ya existe."
        ]);
    } else {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre_usuario, contraseña, es_admin) VALUES (:usuario, :clave, false)");
        $stmt->execute(['usuario' => $usuario, 'clave' => $clave_hash]);
        echo json_encode([
            "success" => true,
            "message" => "✅ Usuario registrado correctamente."
        ]);
    }
}
?>