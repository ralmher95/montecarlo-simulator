<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'GBMEngine.php'; 
require_once 'repositorio.php';

// --- FASE 1: CONEXIÓN ---
$db_host = "localhost";
$db_name = "simulaciones_db";
$db_user = "root";
$db_pass = "13061955"; 

try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
    $pdo = new PDO($dsn, $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conexión exitosa.<br>";

    // --- FASE 2: INICIALIZACIÓN ---
    $engine = new GBMEngine();
    $repo = new SimulationRepository($pdo);

    // Parámetros de simulación (MSFT)
    $S0 = 420.50;  
    $mu = 0.08;    
    $sigma = 0.20; 
    $dias = 30;
    $num_trayectorias = 100;
    
    $sim_id_hex = str_replace('-', '', bin2hex(random_bytes(16)));

    // --- FASE 3: PERSISTENCIA DE CABECERA ---
    $stmt = $pdo->prepare("INSERT INTO simulaciones (id, nombre, precio_inicial, num_pasos, num_trayectorias) VALUES (UNHEX(?), ?, ?, ?, ?)");
    $stmt->execute([$sim_id_hex, "Simulación MSFT Masiva", $S0, $dias, $num_trayectorias]);

    // --- FASE 4: GENERACIÓN DE DATOS ---
    echo "Generando {$num_trayectorias} trayectorias... <br>";
    $todas_las_filas = [];

    for ($t = 1; $t <= $num_trayectorias; $t++) {
        $path = $engine->generatePath($S0, $mu, $sigma, $dias);
        
        foreach ($path as $step => $price) {
            $todas_las_filas[] = [
                'id' => str_replace('-', '', bin2hex(random_bytes(16))),
                'sim_id' => $sim_id_hex,
                'traj_num' => $t,
                'step' => $step,
                'price' => $price
            ];
        }
    }

    // --- FASE 5: INSERCIÓN MASIVA (CHUNKED) ---
    // Usamos fragmentos de 1000 para no saturar el paquete SQL (max_allowed_packet)
    $chunks = array_chunk($todas_las_filas, 1000);
    foreach ($chunks as $index => $chunk) {
        $repo->saveBatch($chunk);
        echo "Insertando bloque " . ($index + 1) . "... OK<br>";
    }

    echo "✅ Éxito Masivo: Se han insertado " . count($todas_las_filas) . " registros.";

} catch (Exception $e) {
    echo "❌ Error Crítico: " . $e->getMessage();
}