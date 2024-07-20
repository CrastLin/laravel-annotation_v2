<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Input;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class All
{
    public function __construct(public array $keys = [])
    {
    }
}