<?php

class SimulationRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function saveBatch(array $rows): void {
        if (empty($rows)) return;

        // Estructura de la consulta masiva
        // INSERT INTO trayectorias (...) VALUES (...), (...), (...);
        $columns = ['id', 'simulacion_id', 'trayectoria_num', 'step_index', 'precio'];
        $colString = implode(',', $columns);
        
        $placeholders = [];
        $values = [];

        foreach ($rows as $row) {
            $placeholders[] = "(UNHEX(?), UNHEX(?), ?, ?, ?)";
            $values[] = $row['id'];           // UUID string sin guiones
            $values[] = $row['sim_id'];       // UUID string sin guiones
            $values[] = $row['traj_num'];
            $values[] = $row['step'];
            $values[] = $row['price'];
        }

        $sql = "INSERT INTO trayectorias ($colString) VALUES " . implode(',', $placeholders);
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
    }
}