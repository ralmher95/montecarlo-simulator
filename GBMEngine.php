<?php

declare(strict_types=1);

final class GBMEngine
{
    /**
     * Generates a standard normal random variable using the Box-Muller transform.
     * Uses mt_rand() / mt_getrandmax() as the uniform random source.
     */
    public static function whiteNoise(): float
    {
        do {
            $u1 = mt_rand() / mt_getrandmax();
            $u2 = mt_rand() / mt_getrandmax();
        } while ($u1 <= PHP_FLOAT_EPSILON);

        return sqrt(-2.0 * log($u1)) * cos(2.0 * M_PI * $u2);
    }

    /**
     * Simulates multiple Geometric Brownian Motion trajectories.
     *
     * @param float $s0          Initial asset price
     * @param float $mu          Drift (annualized expected return)
     * @param float $sigma       Volatility (annualized standard deviation)
     * @param int   $steps       Number of time steps
     * @param int   $paths       Number of trajectories to simulate
     * @param float $dt          Time increment per step (default: 1/252 trading day)
     *
     * @return array<int, array<int, float>> 2D array [path][step], step 0 is S0
     */
    public static function simulate(
        float $s0,
        float $mu,
        float $sigma,
        int $steps,
        int $paths,
        float $dt = 1.0 / 252.0,
    ): array {
        if ($s0 <= 0.0) {
            throw new \InvalidArgumentException('Initial price S0 must be positive.');
        }
        if ($sigma < 0.0) {
            throw new \InvalidArgumentException('Volatility sigma must be non-negative.');
        }
        if ($steps < 1) {
            throw new \InvalidArgumentException('steps must be at least 1.');
        }
        if ($paths < 1) {
            throw new \InvalidArgumentException('paths must be at least 1.');
        }
        if ($dt <= 0.0) {
            throw new \InvalidArgumentException('Time increment dt must be positive.');
        }

        // Pre-compute constants shared across all steps and paths
        $drift   = ($mu - 0.5 * $sigma ** 2) * $dt;
        $diffusion = $sigma * sqrt($dt);

        $trajectories = [];

        for ($p = 0; $p < $paths; $p++) {
            $trajectory    = array_fill(0, $steps + 1, 0.0);
            $trajectory[0] = $s0;

            for ($t = 1; $t <= $steps; $t++) {
                $z = self::whiteNoise();
                $trajectory[$t] = $trajectory[$t - 1] * exp($drift + $diffusion * $z);
            }

            $trajectories[$p] = $trajectory;
        }

        return $trajectories;
    }
}


/*
1. Generación de Ruido Blanco (whiteNoise)
El método whiteNoise es el motor de aleatoriedad.
Transformada de Box-Muller: Los lenguajes de programación suelen generar números aleatorios uniformes (entre 0 y 1). Sin embargo, los mercados financieros se modelan mejor usando una distribución normal (campana de Gauss). Este algoritmo transforma dos números aleatorios uniformes en un número con distribución normal estándar ($\mu=0, \sigma=1$).
Seguridad: El bucle do-while evita errores logarítmicos si el número generado es extremadamente cercano a cero.
2. El Modelo GBM (simulate)
La función simulate aplica la ecuación diferencial estocástica del GBM, que es el estándar para modelar precios de acciones porque garantiza que los precios nunca sean negativos.
Deriva ($mu$): Representa el crecimiento esperado del activo a largo plazo.
Volatilidad ($sigma$): Representa el riesgo o la variabilidad del mercado.
Componente Determinista ($drift): Es la parte del movimiento que "sabemos" que ocurrirá en promedio: $(\mu - 0.5 \cdot \sigma^2) \cdot dt$.
Componente Aleatorio ($diffusion): Es el "choque" del mercado: $\sigma \cdot \sqrt{dt} \cdot Z$.
3. Ejecución y Eficiencia
Pre-cómputo: Inteligentemente, calculas $drift y $diffusion fuera de los bucles. Esto ahorra miles de operaciones matemáticas repetitivas, haciendo la simulación mucho más rápida cuando pides, por ejemplo, 10,000 trayectorias.
Estructura de Datos: Devuelve un array 2D [trayectoria][paso], lo cual es ideal para ser procesado por el bucle de guardado en base de datos que ya tienes configurado.
Este motor es matemáticamente sólido para valorar opciones financieras o calcular el VaR (Value at Risk) de una cartera. El uso de mt_rand() es adecuado para simulaciones estadísticas, ofreciendo un buen equilibrio entre velocidad y calidad de aleatoriedad.*/