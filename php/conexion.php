<?php
$host = "turntable.proxy.rlwy.net";
$port = "16238";
$dbname = "Vehiculo";
$user = "postgres";
$password = "HPSeMNLfDQMYBmLKBwYaORRORCwfMRyd";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Agregar esto para verificar conexión
    if (!$conn) {
        die(json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']));
    }
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Error de conexión: ' . $e->getMessage()]));
}
?>