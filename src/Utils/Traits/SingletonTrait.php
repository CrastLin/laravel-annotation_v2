<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Traits;

use Crastlin\LaravelAnnotation\Annotation\Annotation;
use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Crastlin\LaravelAnnotation\Facades\Context;
use Crastlin\LaravelAnnotation\Facades\Injection;
use ErrorException;
use ReflectionClass;


trait SingletonTrait
{

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
        $key = "singleton.{$name}_" . md5(serialize($params));
        $reflectClass = Injection::exists("reflect.{$name}") ? Injection::take("reflect.{$name}") : null;
        $instance = Context::exists($key) ? Context::get($key) : null;
        if (!$instance) {
            if (!class_exists($name))
                throw new AnnotationException("class: {$name} is not exists", 407);
            // inject constructor
            $reflectClass = $reflectClass ?: new ReflectionClass($name);
            if ($constructor = $reflectClass->getConstructor())
                Annotation::handleInvokeAnnotation($name, $constructor, [], $params, true, true);
            $instance = new $name(...$params);
            Context::set($key, $instance);
        }
        if (!$instance instanceof static)
            throw new AnnotationException("sub class: {$name} must instanceof " . static::class, 408);
        if (method_exists($instance, 'init')) {
            if ($reflectClass) {
                // inject init method
                if ($method = $reflectClass->getMethod('init'))
                    Annotation::handleInvokeAnnotation($name, $method, [], $params, true, true);
            }
            $instance->init(...$params);
        }
        // auto inject all properties
        Injection::injectWithObject($instance, $reflectClass);
        return $instance;
    }


    static function singleton(...$params): static
    {
        return static::singletonByParent('', ...$params);
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
