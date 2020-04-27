<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Collectors\FailedQueues;

use Php2ElkMetrics\Laravel\DefaultMetrics\FailedQueue\FailedQueueJobsMetric;
use Php2ElkMetrics\Metrics\MetricsCollection;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;
use Illuminate\Support\Facades\DB;

class FailedQueuesMetricsCollector
{
    /**
     * @var MetricsProducer
     */
    private $metricsProducer;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var string
     */
    private $tableName;

    /**
     * FailedQueuesMetricsCollector constructor.
     *
     * @param MetricsProducer $metricsProducer
     * @param string          $connectionName
     * @param string          $tableName
     */
    public function __construct(MetricsProducer $metricsProducer, string $connectionName, string $tableName)
    {
        $this->metricsProducer = $metricsProducer;
        $this->connectionName  = $connectionName;
        $this->tableName       = $tableName;
    }

    public function collect(): void
    {
        $failedQueues = DB::connection($this->connectionName)
            ->table($this->tableName)
            ->selectRaw('queue, count(id) as count')
            ->groupBy('queue')
            ->get();

        if ($failedQueues->isEmpty()) {
            return;
        }

        $metricsCollection = new MetricsCollection();
        $date = new \DateTime();

        foreach ($failedQueues as $queue) {
            $metricsCollection->addMetric(
                new FailedQueueJobsMetric(
                    $queue->queue,
                    (int)$queue->count,
                    $date
                )
            );
        }

        $this->metricsProducer->bulkProduce($metricsCollection);
    }
}