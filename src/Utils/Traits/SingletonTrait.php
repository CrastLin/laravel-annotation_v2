<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Traits;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Crastlin\LaravelAnnotation\Facades\Injection;
use ErrorException;
use ReflectionClass;


trait SingletonTrait
{
    /**
     * @var static[] $singleton
     */
    protected static array $singleton;

    /**
     * get singleton instance
     * @param string $name
     * @param mixed $params
     * @return static
     */
    static function singletonByParent(string $name = '', ...$params): static
    {
        $baseNameSpace = explode('\\', static::class);
        array_pop($baseNameSpace);
        $baseNameSpace = join('\\', $baseNameSpace);
        $name = $name ? (str_contains($name, '\\') ? $name : $baseNameSpace . '\\' . $name) : static::class;
        $key = md5("{$name}_" . serialize($params));
        $reflectClass = Injection::exists("reflect.{$name}") ? Injection::take("reflect.{$name}") : null;
        if (!isset(self::$singleton[$key])) {
            if (!class_exists($name))
                throw new AnnotationException("class: {$name} is not exists", 407);
            // inject constructor
            $reflectClass = $reflectClass ?: new ReflectionClass($name);
            if ($constructor = $reflectClass->getConstructor())
                Annotation::handleInvokeAnnotation($name, $constructor, [], $params, true, true);
            self::$singleton[$key] = new $name(...$params);
        }
        if (!self::$singleton[$key] instanceof static)
            throw new AnnotationException("sub class: {$name} must instanceof " . static::class, 408);
        if (method_exists(self::$singleton[$key], 'init')) {
            if ($reflectClass) {
                // inject init method
                if ($method = $reflectClass->getMethod('init'))
                    Annotation::handleInvokeAnnotation($name, $method, [], $params, true, true);
            }
            self::$singleton[$key]->init(...$params);
        }
        // auto inject all properties
        Injection::injectWithObject(self::$singleton[$key], $reflectClass);
        return self::$singleton[$key];
    }


    static function singleton(...$params): static
    {
        return static::singletonByParent('', ...$params);
    }

    /**
     * clear singleton instance
     */
    static function clear(): void
    {
        self::$singleton = [];
    }

    /**
     * call static method
     * @param string $method
     * @param array $args
     */
    static function __callStatus(string $method, array $args = [])
    {
        $object = static::singleton();
        if (!method_exists($object, $method))
            throw new \Exception("method: {$method} is not exists");
        return call_user_func_array([$object, $method], $args);
    }

}
