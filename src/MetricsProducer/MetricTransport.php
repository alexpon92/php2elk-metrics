<?php
declare(strict_types=1);

namespace Php2ElkMetrics\MetricsProducer;

use Php2ElkMetrics\Metrics\BaseMetric;
use JsonSerializable;

final class MetricTransport implements JsonSerializable
{
    /**
     * @var string
     */
    private $instance;

    /**
     * @var int
     */
    private $timestamp;

    /**
     * @var string
     */
    private $application;

    /**
     * @var BaseMetric
     */
    private $metric;

    /**
     * MetricTransport constructor.
     *
     * @param string     $instance
     * @param int        $timestamp
     * @param string     $application
     * @param BaseMetric $metric
     */
    public function __construct(string $instance, int $timestamp, string $application, BaseMetric $metric)
    {
        $this->instance    = $instance;
        $this->timestamp   = $timestamp;
        $this->application = $application;
        $this->metric      = $metric;
    }

    /**
     * @return string
     */
    public function getInstance(): string
    {
        return $this->instance;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    /**
     * @return string
     */
    public function getApplication(): string
    {
        return $this->application;
    }

    /**
     * @return BaseMetric
     */
    public function getMetric(): BaseMetric
    {
        return $this->metric;
    }

    public function jsonSerialize(): array
    {
        return [
            'instance'    => $this->instance,
            'application' => $this->application,
            'timestamp'   => $this->timestamp,
            'metric_name' => $this->metric::getName(),

            $this->metric::getName() => $this->metric->arraySerialize()
        ];
    }
}