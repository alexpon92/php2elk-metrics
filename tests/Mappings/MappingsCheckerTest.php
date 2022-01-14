<?php
declare(strict_types=1);

namespace Php2ElkMetricsTests\Mappings;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Namespaces\IndicesNamespace;
use Php2ElkMetrics\Mappings\Exceptions\IndexMappingsNotFoundException;
use Php2ElkMetrics\Mappings\Exceptions\MetricMappingNotFoundException;
use Php2ElkMetrics\Mappings\MappingsChecker;
use Php2ElkMetrics\Metrics\DefaultMetrics\Common\LatencyMetric;
use Php2ElkMetrics\Metrics\DefaultMetrics\Postgres\TableDeadRowsMetric;
use Php2ElkMetrics\MetricsRegistry\MetricsConfig;
use Php2ElkMetrics\MetricsRegistry\Registry;
use Php2ElkMetricsTests\TestCase;
use Mockery;

class MappingsCheckerTest extends TestCase
{
    public function testMappingIsOk(): void
    {
        $metricsRegistry = new Registry();

        $metricsRegistry->add(
            new MetricsConfig(
                LatencyMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        )->add(
            new MetricsConfig(
                TableDeadRowsMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        );

        $this->mockElasticBuilder(
            [
                'test-rollover-monitoring-index-000001' => [
                    'mappings' => [
                        'properties' => [
                            'application' => [
                                'type' => 'keyword'
                            ],
                            'instance'    => [
                                'type' => 'keyword'
                            ],
                            'timestamp'   => [
                                'type'   => 'date',
                                'format' => 'epoch_second'
                            ],
                            'metric_name' => [
                                'keyword'
                            ],
                            'latency_metric' => [
                                'properties' => [
                                    'latency' => [
                                        'type' => 'float'
                                    ],
                                    'method_name' => [
                                        'type' => 'keyword'
                                    ]
                                ]
                            ],
                            'postgres_table_dead_rows' => [
                                'properties' => [
                                    'percent' => [
                                        'type' => 'float'
                                    ],
                                    'db_name' => [
                                        'type' => 'keyword'
                                    ],
                                    'table_name' => [
                                        'type' => 'keyword'
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            $this->connectionParams
        );

        $mappingsChecker = new MappingsChecker(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class)
        );

        $mappingsChecker->checkMappings();
    }

    public function testMappingIsNotFound(): void
    {
        $metricsRegistry = new Registry();

        $metricsRegistry->add(
            new MetricsConfig(
                LatencyMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        )->add(
            new MetricsConfig(
                TableDeadRowsMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        );

        $this->expectException(MetricMappingNotFoundException::class);

        $this->mockElasticBuilder(
            [
                'test-rollover-monitoring-index-000001' => [
                    'mappings' => [
                        'properties' => [
                            'application' => [
                                'type' => 'keyword'
                            ],
                            'instance'    => [
                                'type' => 'keyword'
                            ],
                            'timestamp'   => [
                                'type'   => 'date',
                                'format' => 'epoch_second'
                            ],
                            'metric_name' => [
                                'keyword'
                            ],
                            'postgres_table_dead_rows' => [
                                'properties' => [
                                    'percent' => [
                                        'type' => 'float'
                                    ],
                                    'db_name' => [
                                        'type' => 'keyword'
                                    ],
                                    'table_name' => [
                                        'type' => 'keyword'
                                    ],
                                ]
                            ],
                        ]
                    ]
                ]
            ],
            $this->connectionParams
        );

        $mappingsChecker = new MappingsChecker(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class)
        );

        $mappingsChecker->checkMappings();
    }

    public function testIndexNotFound(): void
    {
        $metricsRegistry = new Registry();

        $metricsRegistry->add(
            new MetricsConfig(
                LatencyMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        )->add(
            new MetricsConfig(
                TableDeadRowsMetric::getName(),
                $this->indexName,
                $this->indexPattern,
                $this->connectionParams
            )
        );

        $this->expectException(IndexMappingsNotFoundException::class);

        $this->mockElasticBuilder(
            [],
            $this->connectionParams
        );

        $mappingsChecker = new MappingsChecker(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class)
        );

        $mappingsChecker->checkMappings();
    }

    private function mockElasticBuilder(
        array $mappingsResponse,
        array $connectionParams,
        int $timesShouldBeCalled = 1
    ): void {
        $this->instance(
            ClientBuilder::class,
            Mockery::mock(
                ClientBuilder::class,
                static function ($mock) use ($mappingsResponse, $connectionParams, $timesShouldBeCalled) {
                    /** @var Mockery\Mock $mock */
                    $mock->shouldReceive('build')
                        ->andReturn(
                            new class ($mappingsResponse) extends Client {
                                private $mappingsResponse;

                                public function __construct(array $mappingsResponse)
                                {
                                    $this->mappingsResponse = $mappingsResponse;
                                }

                                public function indices(): IndicesNamespace
                                {
                                    return new class($this->mappingsResponse) extends IndicesNamespace {
                                        private $mappingsResponse;

                                        public function __construct(array $mappingsResponse)
                                        {
                                            $this->mappingsResponse = $mappingsResponse;
                                        }

                                        public function getMapping(array $params = [])
                                        {
                                            return $this->mappingsResponse;
                                        }
                                    };
                                }
                            }
                        )->times($timesShouldBeCalled);
                    $mock->shouldReceive('setHosts')
                        ->with($connectionParams)
                        ->andReturn($mock)
                        ->times($timesShouldBeCalled);;
                    $mock->shouldReceive('create')
                        ->andReturn($mock)
                        ->times($timesShouldBeCalled);;
                }
            )
        );
    }
}