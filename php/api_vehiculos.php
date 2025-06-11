<?php
require 'conexion.php';
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $id = $_GET['id'] ?? null;
            
            if ($id) {
                $stmt = $conn->prepare("SELECT * FROM vehiculos WHERE id = :id");
                $stmt->execute(['id' => $id]);
                $vehiculo = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$vehiculo) {
                    throw new Exception("Vehículo no encontrado");
                }
                
                echo json_encode($vehiculo);
            } else {
                $stmt = $conn->query("SELECT * FROM vehiculos ORDER BY marca, modelo");
                $vehiculos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($vehiculos);
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validar y limpiar datos
            $marca = trim($data['marca']);
            $modelo = trim($data['modelo']);
            $anio = intval($data['anio']); // Cambiamos a 'anio' sin acento
            $placa = trim($data['placa']);
            
            if (empty($marca) || empty($modelo) || empty($anio) || empty($placa)) {
                throw new Exception("Todos los campos son requeridos");
            }
            
            $stmt = $conn->prepare("SELECT id FROM vehiculos WHERE placa = :placa");
            $stmt->execute(['placa' => $placa]);
            
            if ($stmt->fetch()) {
                throw new Exception("La placa ya está registrada");
            }
            
            // Usar 'anio' en lugar de 'año' en la consulta
            $stmt = $conn->prepare("INSERT INTO vehiculos (marca, modelo, año, placa, estado) VALUES (:marca, :modelo, :anio, :placa, 'disponible')");
            $stmt->execute([
                'marca' => $marca,
                'modelo' => $modelo,
                'anio' => $anio,
                'placa' => $placa
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Vehículo creado correctamente',
                'id' => $conn->lastInsertId()
            ]);
            break;

        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                throw new Exception("ID de vehículo no especificado");
            }
            
            // Usar 'anio' en lugar de 'año'
            $stmt = $conn->prepare("UPDATE vehiculos SET marca=:marca, modelo=:modelo, año=:anio, placa=:placa WHERE id=:id");
            $stmt->execute([
                'id' => $data['id'],
                'marca' => trim($data['marca']),
                'modelo' => trim($data['modelo']),
                'anio' => intval($data['anio']), // Cambiamos a 'anio'
                'placa' => trim($data['placa'])
            ]);
            
            echo json_encode(['success' => true, 'message' => 'Vehículo actualizado correctamente']);
            break;

        case 'DELETE':
            parse_str(file_get_contents("php://input"), $data);
            
            if (empty($data['id'])) {
                throw new Exception("ID de vehículo no especificado");
            }
            
            $stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE vehiculo_id = :id AND fecha_fin >= CURRENT_DATE");
            $stmt->execute(['id' => $data['id']]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("No se puede eliminar: el vehículo tiene reservas activas");
            }
            
            $stmt = $conn->prepare("DELETE FROM vehiculos WHERE id = :id");
            $stmt->execute(['id' => $data['id']]);
            
            echo json_encode(['success' => true, 'message' => 'Vehículo eliminado correctamente']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>