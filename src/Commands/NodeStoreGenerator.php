<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Commands;

use Crastlin\LaravelAnnotation\Store\GeneratorStoreTable;
use Illuminate\Console\Command;

class NodeStoreGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'annotation:node_store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create save menu and permission tree node data table';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info("Start creating basic table structure...");
        $config = config('annotation');
        $driver = $config['node']['driver'] ?? 'database';
        if (empty($driver) || $driver != 'database') {
            $this->error("The current version only supports database driver configuration");
            return;
        }
        $table = $config['node']['table'] ?? 'node';
        $connection = $config['node']['connection'] ?? 'mysql';
        if ($errText = GeneratorStoreTable::builder($table, $connection)) {
            $this->warn($errText);
            return;
        }
        $this->info("creating basic table structure completed");
    }
}
