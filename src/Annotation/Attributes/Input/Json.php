<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Input;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Json
{
    /**
     * @param string $name
     * Set bound values by request input
     */
    public function __construct(public string $name = '')
    {
    }
}