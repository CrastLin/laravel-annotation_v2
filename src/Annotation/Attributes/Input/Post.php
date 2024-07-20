<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Input;
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Post
{
    /**
     * @param string $name
     * Set bound values by request input
     */
    public function __construct(public string $name = '')
    {
    }
}