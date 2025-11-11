<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        if (isset($config['endpoint']) && !empty($config['endpoint'])) {
            // Neon requires: options=endpoint=<endpoint-id>
            // The '-c endpoint=' format is for command-line options
            $dsn .= ";options=-c endpoint={$config['endpoint']}";
        }

        return $dsn;
    }
}
