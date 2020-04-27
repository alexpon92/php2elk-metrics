<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Console\Commands\Collectors\Postgres;

use Illuminate\Console\Command;
use Php2ElkMetrics\Laravel\Collectors\Postgres\DeadRowsMetricsCollector;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;

class CollectDeadRows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php2elk:collect-postgres-dead-rows';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect postgres dead rows';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(MetricsProducer $metricsProducer): void
    {
        $collector = new DeadRowsMetricsCollector(
            $metricsProducer,
            config('php2elk-metrics.collectors.postgres.dead_rows.db_connections'),
            config('php2elk-metrics.collectors.postgres.dead_rows.min_live_tuples'),
            config('php2elk-metrics.collectors.postgres.dead_rows.min_percent_threshold')
        );

        $collector->collect();
    }
}