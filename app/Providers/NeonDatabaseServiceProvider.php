<?php

namespace App\Providers;

use App\Database\Connectors\NeonPostgresConnector;
use Illuminate\Database\PostgresConnection;
use Illuminate\Support\ServiceProvider;

class NeonDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Extend the database manager to use our custom Neon connector
        $this->app['db']->extend('pgsql', function ($config, $name) {
            // Ensure driver is set
            $config['driver'] = 'pgsql';

            $connector = new NeonPostgresConnector();
            $connection = $connector->connect($config);

            return new PostgresConnection(
                $connection, $config['database'], $config['prefix'] ?? '', $config
            );
        });
    }
}
