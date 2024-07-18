<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
class Inject
{
    /**
     * @param string $name
     * Inject Object Name Definition
     * The injected object can be a general data type and an instance object. If an instance object is injected, a class namespace name needs to be defined
     * 1. When injecting general data type objects, define the bound data name：
     *  using example bind: InjectionAnnotation::bind("name", mixed object); define annotation: #[Inject("name")]
     *  using example bind: Injection::bind("prefix.name", mixed object); define annotation: #[Inject("prefix.name")]
     *
     * 2. When the injected object is an instance object：
     * using example for general: #[Inject]
     * using example for with parameters：#[Inject(\App\Library\ServiceApi::class, [argument1, argument2, ...])]
     *
     * Injection optimization stage
     * 1. When the type is an object and the annotation property name is not configured, the object instance is automatically injected.
     * If parameters are configured, the instance constructor is passed in
     *
     * 2. When name has bound and with an object class namespace address, inject the instance of that class; otherwise, only inject the bound data
     *
     * @param ?array $parameters
     */
    public function __construct(
        public string $name = '',
        public ?array $parameters = null
    )
    {
    }
}
