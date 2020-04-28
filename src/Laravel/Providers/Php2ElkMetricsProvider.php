<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Providers;

use Illuminate\Support\ServiceProvider;
use Php2ElkMetrics\Laravel\Console\Commands\Collectors\FailedQueues\CollectFailedQueues;
use Php2ElkMetrics\Laravel\Console\Commands\Mappings\CheckMappings;
use Php2ElkMetrics\Laravel\Console\Commands\Migrations\SetUpIndex;
use Php2ElkMetrics\Laravel\Console\Commands\Collectors\Postgres\CollectDeadRows;

class Php2ElkMetricsProvider extends ServiceProvider
{
    public function boot()
    {

    }

    public function register()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/config.php' => config_path('php2elk-metrics.php'),
            ],
            'config'
        );

        $this->publishes(
            [
                __DIR__ . '/publish/Php2ElkMetricsServicesProvider.txt' => app_path(
                    'Providers/Php2ElkMetricsServicesProvider.php'
                )
            ],
            'service-provider'
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php',
            'php2elk-metrics'
        );

        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    CollectFailedQueues::class,
                    CollectDeadRows::class,
                    SetUpIndex::class,
                    CheckMappings::class
                ]
            );
        }
    }
}