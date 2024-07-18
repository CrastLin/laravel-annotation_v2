<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Commands;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\RouteAnnotation;
use Illuminate\Console\Command;

class RouteGenerator extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'annotation:route';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Routing Generator';

    /**
     * Execute the console command.
     * @throws \Throwable
     */
    public function handle(): void
    {
        $this->info("Start scanning all route data for the construction target...");
        Annotation::scanAnnotation([RouteAnnotation::class]);
        $this->info("All routes generated completed.");
    }
}
