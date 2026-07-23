<?php

namespace Crastlin\LaravelAnnotation\Utils\Traits;

use Crastlin\LaravelAnnotation\Enum\InjectionEnum;
use Crastlin\LaravelAnnotation\Facades\Injection;

trait SetterTrait
{

    function setProperty(string $name, $value): void
    {
        if (!is_null($value) && property_exists($this, $name))
            $this->{$name} = $value;
    }

    /**
     * When custom binding data is added after initialization, force synchronous update of injected data
     * @param InjectionEnum $enum
     * @return self
     */
    function sync(InjectionEnum $enum = InjectionEnum::ALL): self
    {
        $class = static::class;
        $reflectClass = Injection::exists("reflect.{$class}") ? Injection::take("reflect.{$class}") : new \ReflectionClass($class);
        Injection::injectWithObject($this, $reflectClass, $enum);
        return $this;
    }

    /**
     * When custom binding data is initialized and added, force synchronous update of attribute injection data
     * @return self
     */
    function syncProperty(): self
    {
        return $this->sync(InjectionEnum::PROPERTY);
    }
}
