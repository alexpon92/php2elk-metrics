PHP2ELK Metrics Package
=====================

* [Overview](#overview)
* [Installation](#getting-started)
* [Configuration](#configuratuion)
* [Usage](#usage)
* [Index and default mappings creation](#index-creation)
* [Check mappings](#check-mappings)
* [Non Laravel users](#not-laravel-users)


## Overview
This package helps to transfer application metrics to elasticsearch to some indices with prepared mappings for metrics
fields.
Using Kibana on top of elastic gives you an opportunity to create informative dashboards or trigger some monitoring
events.
You can use it with Laravel applications or with other frameworks.
This package is based on official elasticsearch client (https://github.com/elastic/elasticsearch-php).

## Installation
To install through composer, run the following command from terminal:
```bash
composer require alexpon92/php2elk-metrics
```

## Configuration
Register the main package provider in your applications
```php
Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsProvider::class
```
It gives you an opportunity to publish default config and edit it:
```bash
php artisan vendor:publish --provider="Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsProvider" --tag=config
```

Also, you may publish another provider to register main package services in your service container:
```bash
php artisan vendor:publish --provider="Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsProvider" --tag=service-service-provider
```
It will place package service provider in your ```App\Providers``` directory.  

## Usage
* Create your custom metrics package has ```BaseMetric``` class, and all of your metrics must extend this base class.
* Implement necessary methods:
```php
    /**
     * Unique metric name
     * @return string
     */
    abstract public static function getName(): string;

    /**
     * Method to convert metric contents to array
     *
     * @return array
     */
    abstract public function arraySerialize(): array;
```

```getName``` method provides unique metric name.

```arraySerialize``` method helps to serialize metric fields to associative array.

Example of custom metric:
```php
namespace App\Metrics;

use Php2ElkMetrics\Metrics\BaseMetric;
use DateTime;

class SomeVisitDurationMetric extends BaseMetric
{
    /**
     * @var string
     */
    private $userName;

    /**
     * @var float
     */
    private $duration;

    /**
     * SomeVisitDurationMetric constructor.
     *
     * @param string        $userName
     * @param float         $duration
     * @param DateTime|null $time
     */
    public function __construct(string $userName, float $duration, ?DateTime $time)
    {
        $this->userName = $userName;
        $this->duration = $duration;
        $this->time     = $time; // time is protected field from BaseMetric class
    }


    public static function getName(): string
    {
        return 'some_duration_metric';
    }

    public function arraySerialize(): array
    {
        return [
            'user_name' => $this->userName,
            'duration'  => $this->duration
        ];
    }
}
```

* Add your new metrics to metrics registry

If you have published default package provider with command:
```bash
php artisan vendor:publish --provider="Php2ElkMetrics\Laravel\Providers\Php2ElkMetricsProvider" --tag=service-service-provider
```

You should edit it and add your new metric in register method:
```php
    $this->app->singleton(
        Registry::class,
        static function ($app) {
            /** @var Container $app */
            $metricRegistry = new Registry();

            $defaultIndex = config('php2elk-metrics.default_index');
            $defaultConnection = config('php2elk-metrics.connections.default');

            $metricRegistry->add(
                new MetricsConfig(
                    SomeVisitDurationMetric::getName(),
                    $defaultIndex,
                    $defaultConnection
                )
            );

            return $metricRegistry;
        }
    );
```

* Add metric mappings in elasticsearch for your index, for instance in kibana dev console:
```
PUT <index-name>/_mappings
{
  "properties": {
    "some_duration_metric": {
      "properties": {
        "user_name": {
          "type": "keyword"
        },
        "duration": {
          "type": "float"
        }
      }
    }
  }
}
```

**IMPORTANT!** 
If you will not add mapping for new metrics and produce metrics from your application, your new fields will 
be converted to strings by elastics and you can't use it in aggregations etc., only for concrete search.
Only reindex may help in this situation. 

* Now you can produce it in your application
```php
$producer = app(\Php2ElkMetrics\MetricsProducer\MetricsProducer::class);
$metric = new SomeVisitDurationMetric('John', 12.02);
$producer->produceMetric($metric);
```

After publish, document in elastic will have next structure:

```
{
  "application": "some-app", //may be changed in config
  "instance": "test-instance", //may be changed in config
  "timestamp": 1587735900, //time which is passed in metrics constructor
  "metric_name": "some_duration_metric", //unique metric key, to be able to filter it in elastic index
  "some_duration_metric": { //metric object content
    "user_name": "John",
    "count": 15.02
 }
```

* Asynch Metrics producing
Package has default listener to produce metric in asynch mode to prevent any additional delays.
To use it, you should register ```Php2ElkMetrics\Laravel\Listeners\MetricsEventListener```
in your ```App\Providers\EventServiceProvider``` in ```protected $subscribe``` attribute
```php
$metric = new SomeVisitDurationMetric('John', 12.02);
event(new \Php2ElkMetrics\Events\MetricEvent($metric, new \DateTime()));
```

* Bulk metrics producing
```php
$producer = app(\Php2ElkMetrics\MetricsProducer\MetricsProducer::class);
$metricsCollection = new \Php2ElkMetrics\Metrics\MetricsCollection();
$metricsCollection
    ->add(new SomeVisitDurationMetric('John', 12.02))
    ->add(new SomeVisitDurationMetric('Alex', 30.02));
$producer->bulkProduce($metricsCollection);
```

### Create Index and default mappings
Package has artisan command to prepare an index for your metrics:
```bash
php artisan php2elk:setup-index {--connection_name=} {--index_name=} {--default_metrics}
``` 

```--connection_name``` - to specify connection name from your config (or default connection)
```--index_name``` - index name (or default index from config if not passed)
```--default_metrics``` - flag to add default package metrics

Package has some default metrics and collectors:

* Dead rows collector for PostgreSQL ```\Php2ElkMetrics\Laravel\Collectors\Postgres\DeadRowsMetricsCollector```
This collector estimates number of dead rows in concrete database and produce ```Php2ElkMetrics\Metrics\DefaultMetrics\Postgres\TableDeadRowsMetric```
Also you can use artisan command to launch collection of this metric it in crontab

```bash
php artisan php2elk:php2elk:collect-postgres-dead-rows
```

* Failed queue jobs collector ```\Php2ElkMetrics\Laravel\Collectors\FailedQueues\FailedQueuesMetricsCollector```
This collector estimates number of failed jobs and produce ```\Php2ElkMetrics\Laravel\DefaultMetrics\FailedQueue\FailedQueueJobsMetric```
Also you can use artisan command to launch collection of this metric it in crontab

```bash
php artisan php2elk:php2elk:php2elk:collect-failed-queues
```

* Latency metric ```\Php2ElkMetrics\Metrics\BaseMetric\LatencyMetric```
This metric helps to estimate latency of some actions in your application.

## Check mappings
To check metrics mappings in elasticsearch package has command 
```bash
php artisan php2elk:check-mappings
```
It helps you not to corrupt your index and to check new metrics mappings in elasticsearch before deploy of new version. 


## Non Laravel users
WIP