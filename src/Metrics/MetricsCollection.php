<?php

declare(strict_types=1);

namespace Php2ElkMetrics\Metrics;

final class MetricsCollection
{
    /**
     * @var array|BaseMetric[]
     */
    private $map;

    /**
     * MetricsCollection constructor.
     */
    public function __construct()
    {
        $this->map = [];
    }

    public function addMetric(BaseMetric $metric): self
    {
        $this->map[] = $metric;
        return $this;
    }

    public function getAll(): array
    {
        return $this->map;
    }

    public function isEmpty(): bool
    {
        return empty($this->map);
    }
}