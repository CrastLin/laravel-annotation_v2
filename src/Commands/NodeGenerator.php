<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Commands;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\NodeAnnotation;
use Illuminate\Console\Command;

class NodeGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'annotation:node';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Node Generator';

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->info("Start scanning all node data for the construction target...");
        Annotation::scanAnnotation([NodeAnnotation::class,], [], $this);
        $this->info("All nodes generated completed.");
    }

}
