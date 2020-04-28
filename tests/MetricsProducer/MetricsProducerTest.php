<?php
declare(strict_types=1);

namespace Php2ElkMetricsTests\MetricsProducer;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Illuminate\Support\Facades\Artisan;
use Php2ElkMetrics\Metrics\DefaultMetrics\Common\LatencyMetric;
use Php2ElkMetrics\Metrics\MetricsCollection;
use Php2ElkMetrics\MetricsProducer\Exceptions\MetricConfigNotFoundException;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;
use Php2ElkMetrics\MetricsRegistry\MetricsConfig;
use Php2ElkMetrics\MetricsRegistry\Registry;
use Php2ElkMetricsTests\TestCase;
use Mockery;

class MetricsProducerTest extends TestCase
{
    public function testSingleMetricsDelivery(): void
    {
        $metricsRegistry = new Registry();

        $metricsRegistry->add(
            new MetricsConfig(
                LatencyMetric::getName(),
                $this->indexName,
                $this->connectionParams
            )
        );

        $metric = new LatencyMetric(
            'some_method',
            2.1,
            new \DateTime()
        );

        $id = 'some_id';

        $this->mockElasticBuilder($id, $this->connectionParams);

        $producer = new MetricsProducer(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class),
            $this->app['config']->get('php2elk-metrics.instance'),
            $this->app['config']->get('php2elk-metrics.application')
        );

        $response = $producer->produceMetric($metric);

        $this->assertEquals($this->indexName, $response->getIndex());
        $this->assertEquals($id, $response->getDocId());
    }

    public function testBulkProduce(): void
    {
        $metricsRegistry = new Registry();

        $metricsRegistry->add(
            new MetricsConfig(
                LatencyMetric::getName(),
                $this->indexName,
                $this->connectionParams
            )
        );

        $metricsCollection = new MetricsCollection();

        $metricsCollection->addMetric(
            new LatencyMetric(
                'some_method',
                2.1,
                new \DateTime()
            )
        )->addMetric(
            new LatencyMetric(
                'some_method2',
                2.3,
                new \DateTime()
            )
        );

        $this->mockElasticBuilder(null, $this->connectionParams);

        $producer = new MetricsProducer(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class),
            $this->app['config']->get('php2elk-metrics.instance'),
            $this->app['config']->get('php2elk-metrics.application')
        );

        $producer->bulkProduce($metricsCollection);
    }

    public function testMetricConfigNotFound(): void
    {
        $metricsRegistry = new Registry();

        $metric = new LatencyMetric(
            'some_method',
            2.1,
            new \DateTime()
        );

        $this->expectException(MetricConfigNotFoundException::class);

        $this->mockElasticBuilder(null, $this->connectionParams, 0);

        $producer = new MetricsProducer(
            $metricsRegistry,
            $this->app->make(ClientBuilder::class),
            $this->app['config']->get('php2elk-metrics.instance'),
            $this->app['config']->get('php2elk-metrics.application')
        );

        $producer->produceMetric($metric);
    }

    private function mockElasticBuilder(?string $id, array $connectionParams, int $timesShouldBeCalled = 1): void
    {
        $this->instance(
            ClientBuilder::class,
            Mockery::mock(
                ClientBuilder::class,
                static function ($mock) use ($id, $connectionParams, $timesShouldBeCalled) {
                    /** @var Mockery\Mock $mock */
                    $mock->shouldReceive('build')
                        ->andReturn(
                            new class ($id) extends Client {
                                private $id;

                                public function __construct(?string $id)
                                {
                                    $this->id = $id;
                                }

                                public function index(array $params = [])
                                {
                                    return [
                                        'result' => 'created',
                                        'type'   => '_doc',
                                        '_id'    => $this->id,
                                        '_index' => $params['index']
                                    ];
                                }

                                public function bulk(array $params = [])
                                {
                                    return [
                                        'errors' => false
                                    ];
                                }
                            }
                        )->times($timesShouldBeCalled);
                    $mock->shouldReceive('setHosts')
                        ->with($connectionParams)
                        ->andReturn($mock)
                        ->times($timesShouldBeCalled);
                    $mock->shouldReceive('create')
                        ->andReturn($mock)
                        ->times($timesShouldBeCalled);;
                }
            )
        );
    }
}