<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;
#[\Attribute(\Attribute::TARGET_CLASS)]
class Service
{
    /**
     * @description Used to implement interface dependency injection and implement layer identification tags
     * @using When using Autowired annotation to inject dependency into interface types, mark the annotation as the implementation of the calling class for that interface
     */
}