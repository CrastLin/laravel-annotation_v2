<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER)]
class Qualifier
{
    /**
     * @param string $implName Specify the implementation layer class address
     * @description Autowired annotation can be configured to specify the interface injection implementation layer class name
     * @description It should be noted that the class must be instantiated, otherwise an exception will be thrown
     */
    public function __construct(public string $implName)
    {
    }
}
