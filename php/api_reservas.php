<?php
require 'conexion.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $usuario_id = $_GET['usuario_id'] ?? null;
            
            if ($usuario_id) {
                $stmt = $conn->prepare("
                    SELECT r.*, v.marca, v.modelo, v.placa 
                    FROM reservas r
                    JOIN vehiculos v ON r.vehiculo_id = v.id
                    WHERE r.usuario_id = :usuario_id
                    ORDER BY r.fecha_inicio DESC
                ");
                $stmt->execute(['usuario_id' => $usuario_id]);
            } else {
                $stmt = $conn->query("
                    SELECT r.*, v.marca, v.modelo, v.placa, u.nombre_usuario
                    FROM reservas r
                    JOIN vehiculos v ON r.vehiculo_id = v.id
                    JOIN usuarios u ON r.usuario_id = u.id
                    ORDER BY r.fecha_inicio DESC
                ");
            }
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos
            if (empty($data['vehiculo_id']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
                throw new Exception("Datos incompletos");
            }

            // Verificar disponibilidad
            $stmt = $conn->prepare("
                SELECT COUNT(*) FROM reservas 
                WHERE vehiculo_id = :vehiculo_id 
                AND (
                    (fecha_inicio BETWEEN :fecha_inicio AND :fecha_fin)
                    OR (fecha_fin BETWEEN :fecha_inicio AND :fecha_fin)
                )
            ");
            $stmt->execute([
                'vehiculo_id' => $data['vehiculo_id'],
                'fecha_inicio' => $data['fecha_inicio'],
                'fecha_fin' => $data['fecha_fin']
            ]);
            
            if ($stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'El vehículo no está disponible en esas fechas']);
                exit;
            }
            
            // Crear reserva
            $stmt = $conn->prepare("
                INSERT INTO reservas (usuario_id, vehiculo_id, fecha_inicio, fecha_fin) 
                VALUES (:usuario_id, :vehiculo_id, :fecha_inicio, :fecha_fin)
            ");
            $stmt->execute($data);
            
            // Actualizar estado del vehículo
            $conn->prepare("UPDATE vehiculos SET estado = 'reservado' WHERE id = :id")
                 ->execute(['id' => $data['vehiculo_id']]);
            
            echo json_encode(['success' => true]);
            break;

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $data);
            
            // Obtener vehículo_id antes de eliminar
            $stmt = $conn->prepare("SELECT vehiculo_id FROM reservas WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            $vehiculo_id = $stmt->fetchColumn();
            
            // Eliminar reserva
            $stmt = $conn->prepare("DELETE FROM reservas WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            // Actualizar estado del vehículo
            $conn->prepare("UPDATE vehiculos SET estado = 'disponible' WHERE id = :id")
                 ->execute(['id' => $vehiculo_id]);
            
            echo json_encode(['success' => true]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents("php://input"), true);
            $stmt = $conn->prepare("UPDATE reservas SET estado = :estado WHERE id = :id");
            $stmt->execute($data);
            echo json_encode(['success' => true]);
            break;
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>