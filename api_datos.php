<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { exit; }

require_once 'RetirementEngine.php';

try {
    // Lee parámetros desde GET o POST, con valores por defecto
    $input = $_SERVER['REQUEST_METHOD'] === 'POST'
        ? json_decode(file_get_contents('php://input'), true) ?? []
        : $_GET;

    $ahorrosActuales  = isset($input['ahorros'])      ? (float)$input['ahorros']      : 15000;
    $ahorroAnual      = isset($input['ahorro_anual'])  ? (float)$input['ahorro_anual']  : 5000;
    $gastoAnual       = isset($input['gasto_anual'])   ? (float)$input['gasto_anual']   : 1000;
    $currentAge       = isset($input['edad_actual'])   ? (int)$input['edad_actual']     : 31;
    $edadJubilacion   = isset($input['edad_jubilacion'])? (int)$input['edad_jubilacion']: 65;
    $targetAge        = isset($input['edad_fin'])      ? (int)$input['edad_fin']        : 95;
    $rentabilidad     = isset($input['rentabilidad'])  ? (float)$input['rentabilidad']  : 0.07;
    $volatilidad      = isset($input['volatilidad'])   ? (float)$input['volatilidad']   : 0.15;
    $inflacion        = isset($input['inflacion'])     ? (float)$input['inflacion']     : 0.033;

    $results = RetirementEngine::simulateRetirement(
        $ahorrosActuales,
        $ahorroAnual,
        $gastoAnual,
        $currentAge,
        $edadJubilacion,
        $targetAge,
        $rentabilidad,
        $volatilidad,
        $inflacion
    );

    $formatted = [];
    for ($i = 0; $i <= ($targetAge - $currentAge); $i++) {
        $age = $currentAge + $i;
        $formatted[$i] = ['step' => $age];
    }

    foreach ($results as $pathIdx => $pathData) {
        foreach ($pathData as $stepIdx => $dataPoint) {
            $formatted[$stepIdx]['traj_' . $pathIdx] = $dataPoint['balance'];
        }
    }

    echo json_encode(array_values($formatted));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la simulación: ' . $e->getMessage()]);
}