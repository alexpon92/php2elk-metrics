<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Mappings;

use Php2ElkMetrics\Mappings\Exceptions\IndexMappingsNotFoundException;
use Php2ElkMetrics\Mappings\Exceptions\IndexNotMatchedException;
use Php2ElkMetrics\Mappings\Exceptions\MetricMappingNotFoundException;
use Php2ElkMetrics\MetricsRegistry\MetricsConfig;
use Php2ElkMetrics\MetricsRegistry\Registry;
use Elasticsearch\ClientBuilder;

final class MappingsChecker
{
    /**
     * @var Registry
     */
    private $metricsRegistry;

    /**
     * @var ClientBuilder
     */
    private $clientBuilder;

    /**
     * @var array
     */
    private $indexMappingsMap;

    /**
     * MappingsChecker constructor.
     *
     * @param Registry $metricsRegistry
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(Registry $metricsRegistry, ClientBuilder $clientBuilder)
    {
        $this->metricsRegistry = $metricsRegistry;
        $this->clientBuilder   = $clientBuilder;

        $this->indexMappingsMap = [];
    }

    /**
     * @throws IndexMappingsNotFoundException
     * @throws MetricMappingNotFoundException
     */
    public function checkMappings(): void
    {
        $metricsConfigs = $this->metricsRegistry->getAll();

        foreach ($metricsConfigs as $config) {
            if (isset($this->indexMappingsMap[$config->getIndexName()])) {
                $this->checkMapping($this->indexMappingsMap[$config->getIndexName()], $config);
            } else {
                $client = $this->clientBuilder::create()
                                              ->setHosts($config->getConnectionParams())
                                              ->build();

                $response = $client->indices()->getMapping(
                    [
                        'index' => $config->getIndexName()
                    ]
                );

                if (!$response) {
                    throw new IndexMappingsNotFoundException(
                        "Mappings for index {$config->getIndexName()} is not found"
                    );
                }

                $this->checkMapping($response, $config);

                $this->indexMappingsMap[$config->getIndexName()] = $response;
            }
        }
    }

    /**
     * @param array         $mappings
     * @param MetricsConfig $config
     *
     * @throws MetricMappingNotFoundException|IndexNotMatchedException
     */
    private function checkMapping(array $mappings, MetricsConfig $config): void
    {
        $indexes = array_keys($mappings);

        $pattern = $config->getIndexPattern();

        $matchedIndex = null;

        foreach ($indexes as $index) {
            if (preg_match("/$pattern/u", $index) === 1) {
                $matchedIndex = $index;
                break;
            }
        }

        if(!$matchedIndex) {
            throw new IndexNotMatchedException(
                "Index not matched by expression {$config->getIndexPattern()}"
            );
        }

        if (!isset($mappings[$matchedIndex]['mappings']['properties'][$config->getMetricName()])) {
            throw new MetricMappingNotFoundException(
                "Mapping for {$config->getMetricName()} is not found in index {$config->getIndexName()}"
            );
        }
    }

}