<?php

declare(strict_types=1);

namespace Php2ElkMetrics\MetricsRegistry;

final class MetricsConfig
{
    /**
     * @var string
     */
    private $metricName;

    /**
     * @var string
     */
    private $indexName;

    /**
     * @var array
     */
    private $connectionParams;

    /**
     * MetricsConfig constructor.
     *
     * @param string $metricName
     * @param string $indexName
     * @param array  $connectionParams
     */
    public function __construct(string $metricName, string $indexName, array $connectionParams)
    {
        $this->metricName       = $metricName;
        $this->indexName        = $indexName;
        $this->connectionParams = $connectionParams;
    }

    /**
     * @return string
     */
    public function getMetricName(): string
    {
        return $this->metricName;
    }

    /**
     * @return string
     */
    public function getIndexName(): string
    {
        return $this->indexName;
    }

    /**
     * @return array
     */
    public function getConnectionParams(): array
    {
        return $this->connectionParams;
    }
}