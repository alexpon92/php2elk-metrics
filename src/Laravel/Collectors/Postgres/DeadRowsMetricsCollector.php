<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Collectors\Postgres;

use Php2ElkMetrics\Metrics\DefaultMetrics\Postgres\TableDeadRowsMetric;
use Php2ElkMetrics\Metrics\MetricsCollection;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;
use Illuminate\Support\Facades\DB;

class DeadRowsMetricsCollector
{
    /**
     * @var MetricsProducer
     */
    private $metricsProducer;

    /**
     * @var array
     */
    private $connections;

    /**
     * @var int
     */
    private $minLiveTuples;

    /**
     * @var float
     */
    private $minPercentToCollectMetric;

    /**
     * DeadRowsMetricsCollector constructor.
     *
     * @param MetricsProducer $metricsProducer
     * @param array           $connections
     * @param int             $minLiveTuples
     * @param float           $minPercentToCollectMetric
     */
    public function __construct(
        MetricsProducer $metricsProducer,
        array $connections,
        int $minLiveTuples,
        float $minPercentToCollectMetric
    ) {
        $this->metricsProducer = $metricsProducer;
        $this->connections = $connections;
        $this->minLiveTuples = $minLiveTuples;
        $this->minPercentToCollectMetric = $minPercentToCollectMetric;
    }


    public function collect(): void
    {
        $time = new \DateTime();

        foreach ($this->connections as $connection) {
            $metricsCollection = new MetricsCollection();

            $deadRowsInfo = DB::connection($connection)
                ->select(
                    'SELECT relname    AS table_name,
                    n_live_tup AS live_tuples,
                    n_dead_tup AS dead_tuples,
                    CASE
                        WHEN n_live_tup = 0 THEN 0
                        ELSE round(n_dead_tup::numeric / n_live_tup::numeric * 100::numeric, 2)
                    END    AS percent
                    FROM pg_stat_user_tables'
                );

            if (empty($deadRowsInfo)) {
                continue;
            }

            foreach ($deadRowsInfo as $tableDeadRows) {
                if (
                    $tableDeadRows->live_tuples < $this->minLiveTuples ||
                    $tableDeadRows->percent < $this->minPercentToCollectMetric
                ) {
                    continue;
                }

                $metricsCollection->addMetric(
                    new TableDeadRowsMetric(
                        DB::connection($connection)->getDatabaseName(),
                        $tableDeadRows->table_name,
                        (float)$tableDeadRows->percent,
                        $time
                    )
                );
            }

            if (!$metricsCollection->isEmpty()) {
                $this->metricsProducer->bulkProduce($metricsCollection);
            }
        }
    }
}