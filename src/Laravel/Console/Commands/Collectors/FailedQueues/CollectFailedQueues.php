<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Console\Commands\Collectors\FailedQueues;

use Illuminate\Console\Command;
use Php2ElkMetrics\Laravel\Collectors\FailedQueues\FailedQueuesMetricsCollector;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;

class CollectFailedQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php2elk:collect-failed-queues';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect failed queues metrics';

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
        $collector = new FailedQueuesMetricsCollector(
            $metricsProducer,
            config('php2elk-metrics.collectors.failed_queue.db_connection'),
            config('php2elk-metrics.collectors.failed_queue.table_name')
        );

        $collector->collect();
    }
}