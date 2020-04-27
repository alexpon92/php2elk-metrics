<?php
declare(strict_types=1);

namespace Php2ElkMetricsTests\MetricsProducer;

use Php2ElkMetrics\Metrics\DefaultMetrics\Common\LatencyMetric;
use Php2ElkMetrics\MetricsProducer\MetricTransport;
use Php2ElkMetricsTests\TestCase;

class MetricTransportTest extends TestCase
{
    public function testSerialization(): void
    {
        $instance    = 'instance';
        $application = 'application';
        $time        = new \DateTime();
        $metric      = new LatencyMetric(
            'some_method',
            2.1,
            $time
        );

        $metricTransport = new MetricTransport(
            $instance,
            $time->getTimestamp(),
            $application,
            $metric
        );
        $this->assertEquals(
            [
                'instance'    => $instance,
                'application' => $application,
                'timestamp'   => $time->getTimestamp(),
                'metric_name' => $metric::getName(),

                $metric::getName() => $metric->arraySerialize()
            ],
            $metricTransport->jsonSerialize()
        );
    }
}