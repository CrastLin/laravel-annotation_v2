<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Env
{
    /**
     * @param string $name
     * Set bound values by env
     */
    public function __construct(
        public string $name
    )
    {
    }
}
