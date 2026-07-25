<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Facades;

use Crastlin\LaravelAnnotation\Enum\InjectionEnum;
use Illuminate\Support\Facades\Facade;
use Crastlin\LaravelAnnotation\Annotation\InjectionAnnotation;

/**
 * @package Inject
 * @mixin InjectionAnnotation
 * @method static void bind(string $name, mixed $value)
 * @method static void hSet(string $name, string $key, mixed $value)
 * @method static void append(string $name, mixed $value): void
 * @method static void bindAndRefresh(object $object, string $name, mixed $value)
 * @method static void offsetSet(string $name, $value)
 * @method static void bindAll(array $attributes, bool $recover = false)
 * @method static void into(array $attributes, bool $recover = false)
 * @method static mixed take(string $name)
 * @method static string takeToJson(string $name)
 * @method static mixed offsetGet(string $name)
 * @method static mixed hGet(string $name, string $key)
 * @method static array takeAll()
 * @method static string takeAllToJson()
 * @method static mixed takeByParameter(\ReflectionParameter $parameter, object $annotation, string $action)
 * @method static void clearAll()
 * @method static void unbind(string $name)
 * @method static void offsetUnset(string $name)
 * @method static bool exists(string $name)
 * @method static bool hExists(string $name)
 * @method static int getAttributeSerialNum()
 * @method static bool offsetExists(string $name)
 * @method static void injectWithObject($instance, ?\ReflectionClass $reflectionClass = null, InjectionEnum $injectType = InjectionEnum::ALL)
 * @method static object injectWithClass(string $class)
 * @method static mixed getInjectTypeofValue(string $class, \stdClass $property, bool $bindInjectMap = true)
 * @method static \ReflectionClass getReflectClass(string $class)
 */
class Injection extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'crastlin.annotation.injection';
    }
}
