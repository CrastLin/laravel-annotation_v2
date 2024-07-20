<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Input
{
    /**
     * @param string $name
     * Set bound values by request input
     */
    public function __construct(public string $name = '')
    {
    }
}