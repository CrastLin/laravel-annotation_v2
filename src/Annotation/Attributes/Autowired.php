<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
class Autowired
{
    /**
     * @using Automatic interface dependency injection
     * @description Implementing delayed calls through anonymous proxy classes to achieve intra class dependency injection
     * @description Automatic interface dependency injection conditions:
     * 1. The attribute type must be an interface type.
     * 2. The implementation class must specify a location for the configuration file.
     * 3. The implementation class must define a Service annotation.
     * 4. Multiple implementation classes are specified through the Qualifier annotation.
     *
     * @using Single instance injection without parameters
     * @description Single instance injection can be achieved by defining the type as the class that needs to be injected
     * @description It should be noted that the class must be instantiated, otherwise an exception will be thrown
     */
}
