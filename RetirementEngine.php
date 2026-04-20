<?php
declare(strict_types=1);

final class RetirementEngine
{
    public static function whiteNoise(): float {
        do {
            $u1 = mt_rand() / mt_getrandmax();
            $u2 = mt_rand() / mt_getrandmax();
        } while ($u1 <= PHP_FLOAT_EPSILON);
        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }

    public static function simulateRetirement(
        float $currentSavings,
        float $annualContrib,
        float $annualSpend, // Gasto deseado EN DINERO DE HOY
        int $currentAge,
        int $retirementAge,
        int $targetAge,
        float $expectedReturn,
        float $volatility,
        float $inflationRate = 0.03, // 3% anual por defecto
        int $paths = 100
    ): array {
        $years = $targetAge - $currentAge;
        $trajectories = [];
        $drift = ($expectedReturn - 0.5 * $volatility ** 2);

        for ($p = 0; $p < $paths; $p++) {
            $balance = $currentSavings;
            $currentAnnualSpend = $annualSpend;
            $currentAnnualContrib = $annualContrib;
            $history = [['age' => $currentAge, 'balance' => $balance]];

            for ($year = 1; $year <= $years; $year++) {
                $age = $currentAge + $year;
                
                // 1. Efecto Inflación: La vida es más cara y tu ahorro sube
                $currentAnnualSpend *= (1 + $inflationRate);
                $currentAnnualContrib *= (1 + $inflationRate);

                // 2. Mercado (Rendimiento Nominal)
                $z = self::whiteNoise();
                $marketFactor = exp($drift + $volatility * $z);
                $balance *= $marketFactor;

                // 3. Flujo de caja
                if ($age < $retirementAge) {
                    $balance += $currentAnnualContrib; 
                } else {
                    $balance -= $currentAnnualSpend;   
                }

                if ($balance < 0) $balance = 0;

                $history[] = [
                    'age' => $age, 
                    'balance' => round($balance, 2)
                ];
            }
            $trajectories[$p] = $history;
        }

        return $trajectories;
    }
}