<?php
declare(strict_types=1);

namespace App\Providers;

use Elasticsearch\ClientBuilder;
use Illuminate\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Php2ElkMetrics\Laravel\DefaultMetrics\FailedQueue\FailedQueueJobsMetric;
use Php2ElkMetrics\Metrics\DefaultMetrics\Postgres\TableDeadRowsMetric;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;
use Php2ElkMetrics\MetricsRegistry\MetricsConfig;
use Php2ElkMetrics\MetricsRegistry\Registry;
use Psr\Log\LoggerInterface;

class Php2ElkMetricsServicesProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function register(): void
    {
        $this->app->singleton(
            Registry::class,
            static function ($app) {
                /** @var Container $app */
                $metricRegistry = new Registry();

                $defaultIndex = config('php2elk-metrics.default_index');
                $defaultConnection = config('php2elk-metrics.connections.default');

                $metricRegistry->add(
                    new MetricsConfig(
                        FailedQueueJobsMetric::getName(),
                        $defaultIndex,
                        $defaultConnection
                    )
                )->add(
                    new MetricsConfig(
                        TableDeadRowsMetric::getName(),
                        $defaultIndex,
                        $defaultConnection
                    )
                );

                return $metricRegistry;
            }
        );

        $this->app->singleton(
            MetricsProducer::class,
            static function ($app) {
                /** @var Container $app */
                $producer = new MetricsProducer(
                    $app->make(Registry::class),
                    $app->make(ClientBuilder::class),
                    config('php2elk-metrics.instance'),
                    config('php2elk-metrics.application')
                );

                $producer->setLogger($app->make(LoggerInterface::class));

                return $producer;
            }
        );
    }

    public function provides(): array
    {
        return [
            Registry::class,
            MetricsProducer::class
        ];
    }
}