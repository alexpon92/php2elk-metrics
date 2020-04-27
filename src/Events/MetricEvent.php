<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Events;

use Php2ElkMetrics\Metrics\BaseMetric;
use DateTime;

class MetricEvent
{
    /**
     * @var BaseMetric
     */
    private $metric;

    /**
     * @var DateTime
     */
    private $triggeredAt;

    /**
     * MetricEvent constructor.
     *
     * @param BaseMetric $metric
     * @param DateTime   $triggeredAt
     */
    public function __construct(BaseMetric $metric, DateTime $triggeredAt)
    {
        $this->metric      = $metric;
        $this->triggeredAt = $triggeredAt;
    }

    /**
     * @return BaseMetric
     */
    public function getMetric(): BaseMetric
    {
        return $this->metric;
    }

    /**
     * @return DateTime
     */
    public function getTriggeredAt(): DateTime
    {
        return $this->triggeredAt;
    }

}