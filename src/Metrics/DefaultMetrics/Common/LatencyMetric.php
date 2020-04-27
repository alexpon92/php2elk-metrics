<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Metrics\DefaultMetrics\Common;

use Php2ElkMetrics\Metrics\BaseMetric;
use DateTime;

class LatencyMetric extends BaseMetric
{
    /**
     * @var string
     */
    private $methodName;

    /**
     * @var float
     */
    private $latency;

    /**
     * LatencyMetric constructor.
     *
     * @param string        $methodName
     * @param float         $latency
     * @param DateTime|null $time
     */
    public function __construct(string $methodName, float $latency, ?DateTime $time)
    {
        $this->methodName = $methodName;
        $this->latency    = $latency;
        $this->time       = $time;
    }


    public static function getName(): string
    {
        return 'latency_metric';
    }

    public function arraySerialize(): array
    {
        return [
            'method_name' => $this->methodName,
            'latency'     => $this->latency
        ];
    }

    /**
     * @return string
     */
    public function getMethodName(): string
    {
        return $this->methodName;
    }

    /**
     * @return float
     */
    public function getLatency(): float
    {
        return $this->latency;
    }
}