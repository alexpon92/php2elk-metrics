<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Mappings;

use Elasticsearch\ClientBuilder;

class PrepareIndex
{
    /**
     * @var ClientBuilder
     */
    private $clientBuilder;

    /**
     * DefaultMappingsSetUp constructor.
     *
     * @param ClientBuilder $clientBuilder
     */
    public function __construct(ClientBuilder $clientBuilder)
    {
        $this->clientBuilder = $clientBuilder;
    }

    public function prepareMonitoringIndex(
        array $connectionParams,
        ?array $indexSettings,
        string $index,
        bool $addDefaultMetrics
    ): void {
        $client = $this->clientBuilder::create()
            ->setHosts($connectionParams)
            ->build();

        $params = [
            'index' => $index,
            'body'  => []
        ];

        if ($indexSettings) {
            $params['body']['settings'] = $indexSettings;
        }

        $params['body']['mappings']               = [];
        $params['body']['mappings']['properties'] = $this->getDefaultMapping($addDefaultMetrics);

        $client->indices()->create($params);
    }

    private function getDefaultMapping(bool $addDefaultMetrics): array
    {
        $defaultMapping = [
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
                'type' => 'keyword'
            ],
        ];

        if ($addDefaultMetrics) {
            $defaultMapping = array_merge($defaultMapping, $this->getDefaultMetricsMapping());
        }

        return $defaultMapping;
    }

    private function getDefaultMetricsMapping(): array
    {
        return [
            'latency_metric'           => [
                'properties' => [
                    'latency'     => [
                        'type' => 'float'
                    ],
                    'method_name' => [
                        'type' => 'keyword'
                    ]
                ]
            ],
            'postgres_table_dead_rows' => [
                'properties' => [
                    'percent'    => [
                        'type' => 'float'
                    ],
                    'db_name'    => [
                        'type' => 'keyword'
                    ],
                    'table_name' => [
                        'type' => 'keyword'
                    ],
                ]
            ],
            'failed_queue_jobs'        => [
                'properties' => [
                    'queue_name' => [
                        'type' => 'keyword'
                    ],
                    'count'      => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ];
    }
}