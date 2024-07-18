<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Commands;

use Illuminate\Console\Command;

class ConfigGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'annotation:config';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configuration Generator';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info("Start generating annotation configuration file...");
        $config = config_path('annotation.php');
        if (!is_file($config)) {
            $source = file_get_contents(realpath(__DIR__ . '/../config.php'));
            file_put_contents($config, $source);
        }
        $this->info("Annotation configuration file generation completed");
    }
}
