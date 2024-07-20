<?php

namespace Crastlin\LaravelAnnotation\Utils\Traits;

trait SetterTrait
{
    function setProperty(string $name, $value): void
    {
        if (!is_null($value) && property_exists($this, $name))
            $this->{$name} = $value;
    }
}
