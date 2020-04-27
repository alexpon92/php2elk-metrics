<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Console\Commands\Mappings;

use Illuminate\Console\Command;
use Php2ElkMetrics\Mappings\MappingsChecker;

class CheckMappings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'php2elk:check-mappings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check mappings for registered metrics';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle(MappingsChecker $checker): void
    {
        $checker->checkMappings();
    }
}