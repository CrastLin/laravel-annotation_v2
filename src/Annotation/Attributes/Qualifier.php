<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
class Qualifier
{
    public function __construct(public string $implName)
    {
    }
}
