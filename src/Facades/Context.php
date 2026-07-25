<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Facades;

use Illuminate\Support\Facades\Facade;
use Crastlin\LaravelAnnotation\Annotation\Context as ContextAnnotation;

/**
 * @package Inject
 * @mixin ContextAnnotation
 * @method static bool exists(string $name)
 * @method static void set(string $name, mixed $value)
 * @method static mixed get(string $name)
 * @method static void unset(string $name)
 * @method static void clear()
 */
class Context extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crastlin.annotation.context';
    }
}
