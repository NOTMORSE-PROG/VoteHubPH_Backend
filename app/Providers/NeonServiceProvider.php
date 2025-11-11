<?php

namespace App\Providers;

use App\Database\Connectors\NeonConnector;
use App\Database\NeonConnection;
use Illuminate\Database\Connection;
use Illuminate\Support\ServiceProvider;

class NeonServiceProvider extends ServiceProvider
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
        // Register custom Neon connector
        Connection::resolverFor('neon', function ($connection, $database, $prefix, $config) {
            $connector = new NeonConnector();
            $pdo = $connector->connect($config);

            return new NeonConnection($pdo, $database, $prefix, $config);
        });
    }
}
