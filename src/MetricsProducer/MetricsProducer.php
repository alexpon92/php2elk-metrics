<?php
declare(strict_types=1);

namespace Php2ElkMetrics\MetricsProducer;

use Elasticsearch\ClientBuilder;
use Php2ElkMetrics\Metrics\BaseMetric;
use Php2ElkMetrics\Metrics\MetricsCollection;
use Php2ElkMetrics\MetricsProducer\Exceptions\BadResponseFormatException;
use Php2ElkMetrics\MetricsProducer\Exceptions\MetricConfigNotFoundException;
use Php2ElkMetrics\MetricsProducer\Exceptions\ProduceMetricException;
use Php2ElkMetrics\MetricsProducer\Responses\Response;
use Php2ElkMetrics\MetricsRegistry\Registry;
use Psr\Log\LoggerInterface;
use DateTime;

final class MetricsProducer
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
     * @var string
     */
    private $instance;

    /**
     * @var string
     */
    private $application;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MetricsProducer constructor.
     *
     * @param Registry $metricsRegistry
     * @param ClientBuilder $clientBuilder
     * @param string $instance
     * @param string $application
     */
    public function __construct(
        Registry $metricsRegistry,
        ClientBuilder $clientBuilder,
        string $instance,
        string $application
    ) {
        $this->metricsRegistry = $metricsRegistry;
        $this->clientBuilder   = $clientBuilder;
        $this->instance        = $instance;
        $this->application     = $application;
    }

    /**
     * @param BaseMetric $metric
     *
     * @throws BadResponseFormatException
     * @throws MetricConfigNotFoundException
     * @throws ProduceMetricException
     */
    public function produceMetric(BaseMetric $metric): Response
    {
        $metricConfig = $this->metricsRegistry->findByMetric($metric);
        if (!$metricConfig) {
            if ($this->logger) {
                $this->logger->error(
                    'Metrics config is not found for metric',
                    [
                        'metric_name' => $metric::getName()
                    ]
                );
            }
            throw new MetricConfigNotFoundException("Metrics config is not found for metric {$metric::getName()}");
        }
        $client = $this->clientBuilder::create()
            ->setHosts($metricConfig->getConnectionParams())
            ->build();

        $metricsTransport = new MetricTransport(
            $this->instance,
            $metric->getTimestamp() ?? (new DateTime())->getTimestamp(),
            $this->application,
            $metric
        );

        try {
            $response = $client->index(
                [
                    'index' => $metricConfig->getIndexName(),
                    'body'  => $metricsTransport->jsonSerialize()
                ]
            );

        } catch (\Throwable $exception) {
            if ($this->logger) {
                $this->logger->error(
                    'Exception on producing metrics in elastic',
                    [
                        'exception' => $exception->getMessage()
                    ]
                );
            }
            throw new ProduceMetricException(
                "Exception on producing metrics in elastic: {$exception->getMessage()}",
                0,
                $exception
            );
        }

        if (!$response || !is_array($response) || (isset($response['errors']) && $response['errors'])) {
            if ($this->logger) {
                $this->logger->error(
                    'Malformed or error response on sending bulk request to elastic',
                    [
                        'response' => $response
                    ]
                );
            }
            throw new ProduceMetricException('Malformed or error response on sending bulk request to elastic');
        }

        if (!isset($response['result'], $response['_id'], $response['_index']) || $response['result'] !== 'created') {
            if ($this->logger) {
                $this->logger->error(
                    'Bad response format exception. Result is not equals to created',
                    [
                        'response' => $response
                    ]
                );
            }
            throw new BadResponseFormatException('Bad response format exception. Result is not equals to created');
        }

        return new Response($response['_index'], $response['_id']);
    }

    /**
     * All metrics will be sent to one connection (first from collection)
     *
     * @param MetricsCollection $collection
     *
     * @throws MetricConfigNotFoundException
     * @throws ProduceMetricException
     */
    public function bulkProduce(MetricsCollection $collection): void
    {
        $metrics = $collection->getAll();
        if (empty($metrics)) {
            return;
        }

        $metric = $metrics[0];
        $config = $this->metricsRegistry->findByMetric($metric);
        if (!$config) {
            if ($this->logger) {
                $this->logger->error(
                    'Metrics config is not found for metric in bulk produce',
                    [
                        'metric_name' => $metric::getName()
                    ]
                );
            }
            throw new MetricConfigNotFoundException(
                "Metrics config is not found for metric {$metric::getName()} in bulk produce"
            );
        }

        $client = $this->clientBuilder::create()
            ->setHosts($config->getConnectionParams())
            ->build();

        $params = ['body' => []];

        foreach ($metrics as $metric) {
            $config = $this->metricsRegistry->findByMetric($metric);
            if (!$config) {
                if ($this->logger) {
                    $this->logger->error(
                        'Metrics config is not found for metric',
                        [
                            'metric_name' => $metric::getName()
                        ]
                    );
                }
                throw new MetricConfigNotFoundException("Metrics config is not found for metric {$metric::getName()}");
            }

            $params['body'][] = [
                'index' => [
                    '_index' => $config->getIndexName()
                ]
            ];

            $params['body'][] = (new MetricTransport(
                $this->instance,
                $metric->getTimestamp() ?? (new DateTime())->getTimestamp(),
                $this->application,
                $metric
            ))->jsonSerialize();
        }

        try {
            $response = $client->bulk($params);
        } catch (\Throwable $exception) {
            if ($this->logger) {
                $this->logger->error(
                    'Exception on producing metrics in elastic',
                    [
                        'exception' => $exception->getMessage()
                    ]
                );
            }
            throw new ProduceMetricException(
                "Exception on sending bulk request to elastic: {$exception->getMessage()}",
                0,
                $exception
            );
        }

        if (!$response || !is_array($response) || (isset($response['errors']) && $response['errors'])) {
            if ($this->logger) {
                $this->logger->error(
                    'Malformed or error response on sending bulk request to elastic',
                    [
                        'response' => $response
                    ]
                );
            }
            throw new ProduceMetricException('Malformed or error response on sending bulk request to elastic');
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}