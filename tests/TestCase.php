<?php
declare(strict_types=1);

namespace Php2ElkMetricsTests;

use Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsProvider;
use Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsServicesProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected $indexName        = 'test-monitoring-index';
    protected $connectionParams = [
        [
            'host'   => 'example.com',
            'port'   => '9200',
            'scheme' => 'http'
        ]
    ];

    /**
     * Setup the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * add the package provider
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            Php2ElkMetricsProvider::class,
            Php2ElkMetricsServicesProvider::class
        ];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('php2elk-metrics.application', 'test-application');
        $app['config']->set('php2elk-metrics.instance', 'test-instance');
        $app['config']->set('php2elk-metrics.connections.default', $this->connectionParams);
        $app['config']->set('php2elk-metrics.default_index', $this->indexName);
    }
}