<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Facades;

use Crastlin\LaravelAnnotation\Annotation\InterceptorAnnotation;
use Crastlin\LaravelAnnotation\Utils\TurnBack;
use Illuminate\Support\Facades\Facade;

/**
 * @package Validation
 * @mixin InterceptorAnnotation
 * @method static TurnBack handle(string $action, ?array $datum = [], ?callable $callback = null, ?\stdClass &$interceptor = null, ?callable $header = null)
 * @method static string runValidation(array $validatorList, array $data)
 */
class Interceptor extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crastlin.annotation.interceptor';
    }
}
