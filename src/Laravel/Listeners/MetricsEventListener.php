<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Php2ElkMetrics\Events\MetricEvent;
use Php2ElkMetrics\MetricsProducer\MetricsProducer;

class MetricsEventListener implements ShouldQueue
{
    /**
     * @var MetricsProducer
     */
    private $metricsProducer;

    public function initDependencies(): void
    {
        $this->metricsProducer = app(MetricsProducer::class);
    }

    public function onMetricEvent(MetricEvent $event): void
    {
        $this->initDependencies();

        $this->metricsProducer->produceMetric($event->getMetric());
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            MetricEvent::class,
            self::class . '@onMetricEvent'
        );
    }

    public function getConnection(): string
    {
        return (string)config('php2elk-metrics.listeners.metrics_events.connection');
    }

    public function getQueue(): string
    {
        return (string)config('php2elk-metrics.listeners.metrics_events.queue');
    }

    public function __get($name)
    {
        if ($name === 'connection') {
            return $this->getConnection();
        }

        if ($name === 'queue') {
            return $this->getQueue();
        }

        return null;
    }
}