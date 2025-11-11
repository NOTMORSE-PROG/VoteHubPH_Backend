<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class NeonConnector extends PostgresConnector
{
    /**
     * Get the DSN string for a Neon connection.
     *
     * @param  array  $config
     * @return string
     */
    protected function getDsn(array $config)
    {
        // Extract the parts from the config
        $host = $config['host'];
        $database = $config['database'];

        // Build the base DSN
        $dsn = "pgsql:host={$host};dbname={$database}";

        // Add port if provided
        if (isset($config['port']) && ! empty($config['port'])) {
            $dsn .= ";port={$config['port']}";
        }

        // Add charset if provided
        if (isset($config['charset']) && ! empty($config['charset'])) {
            $dsn .= ";client_encoding={$config['charset']}";
        }

        // Add SSL mode if provided
        if (isset($config['sslmode']) && ! empty($config['sslmode'])) {
            $dsn .= ";sslmode={$config['sslmode']}";
        }

        // Add Neon endpoint to options if provided
        if (isset($config['endpoint']) && ! empty($config['endpoint'])) {
            $dsn .= ";options='endpoint=" . $config['endpoint'] . "'";
        }

        return $dsn;
    }
}
