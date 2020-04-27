<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Php2ElkMetrics\Mappings\PrepareIndex;

class SetUpIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php2elk:setup-index {--connection_name=} {--index_name=} {--default_metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Collect failed queues metrics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(PrepareIndex $prepareIndex)
    {
        $connection = $this->option('connection_name');
        if (!$connection) {
            $connection = 'default';
        }

        $connectionParams = config("php2elk-metrics.connections.$connection");
        if (empty($connectionParams)) {
            $this->error("Cannot find connection parameters for connection {$connection}");
            return;
        }

        $index = $this->option('index_name');
        if (!$index) {
            $index = config('php2elk-metrics.default_index');
        }

        $indexSettings = config("php2elk-metrics.index_settings.{$index}", []);

        $prepareIndex->prepareMonitoringIndex(
            $connectionParams,
            $indexSettings,
            $index,
            $this->hasOption('default_metrics')
        );
    }
}