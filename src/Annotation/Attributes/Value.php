<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Value
{
    /**
     * @param string $name
     * Set bound values by config
     */
    public function __construct(
        public string $name
    )
    {
    }
}
