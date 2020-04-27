<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\DefaultMetrics\FailedQueue;

use Php2ElkMetrics\Metrics\BaseMetric;
use DateTime;

class FailedQueueJobsMetric extends BaseMetric
{
    /**
     * @var string
     */
    private $queueName;

    /**
     * @var int
     */
    private $count;

    /**
     * FailedQueueMetric constructor.
     *
     * @param string $queueName
     * @param int    $count
     */
    public function __construct(string $queueName, int $count, ?DateTime $dateTime)
    {
        $this->queueName = $queueName;
        $this->count     = $count;
        $this->time      = $dateTime;
    }

    public static function getName(): string
    {
        return 'failed_queue_jobs';
    }

    public function arraySerialize(): array
    {
        return [
            'queue_name' => $this->queueName,
            'count'      => $this->count
        ];
    }

    /**
     * @return string
     */
    public function getQueueName(): string
    {
        return $this->queueName;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}