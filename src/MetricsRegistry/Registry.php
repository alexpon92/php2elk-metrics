<?php
declare(strict_types=1);

namespace Php2ElkMetrics\MetricsRegistry;

use Php2ElkMetrics\Metrics\BaseMetric;
use Php2ElkMetrics\MetricsRegistry\Exceptions\DuplicateMetricConfigException;

final class Registry
{
    /**
     * @var array|MetricsConfig[]
     */
    private $map;

    /**
     * Registry constructor.
     */
    public function __construct()
    {
        $this->map = [];
    }

    /**
     * @param MetricsConfig $config
     *
     * @return $this
     * @throws DuplicateMetricConfigException
     */
    public function add(MetricsConfig $config): self
    {
        if ($this->hasConfig($config)) {
            throw new DuplicateMetricConfigException(
                "Duplicate config for metric: {$config->getMetricName()}"
            );
        }

        $this->map[$config->getMetricName()] = $config;
        return $this;
    }

    public function hasMetric(string $metricName): bool
    {
        return isset($this->map[$metricName]);
    }

    public function hasConfig(MetricsConfig $config): bool
    {
        return  $this->hasMetric($config->getMetricName());
    }

    public function findByMetric(BaseMetric $metric): ?MetricsConfig
    {
        if ($this->hasMetric($metric::getName())) {
            return $this->map[$metric::getName()];
        }

        return null;
    }

    /**
     * @return array|MetricsConfig[]
     */
    public function getAll(): array
    {
        $res = [];
        foreach ($this->map as $config) {
            $res[] = $config;
        }

        return $res;
    }
}