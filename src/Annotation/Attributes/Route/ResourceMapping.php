<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Enum\ResourceEnum;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ResourceMapping
{
    /**
     * ResourceEnum Controller Annotations
     * By binding this annotation, resource controller routing can be automatically generated
     * @param string $path ResourceMapping routing path
     * @param ResourceEnum[] $only Only limited bound resource methods, method names can be found in the enumeration class: ResourceEnum
     * @param ResourceEnum[] $except Excluding resource routing methods
     * @param array<string,string> $names Custom ResourceEnum Controller Behavior Routing Name
     * @param array<string,string> $parameters Custom ResourceEnum Controller Parameter Name
     * @param array<string,string> $scoped Custom limited range resource routing
     * @param bool $isShallow Enable shallow nesting and use nested resource-based routing effectively
     * @param string $missingRedirect When calling non-existent methods, the routing route name
     */
    public function __construct(
        public string $path,
        public array  $only = [],
        public array  $except = [],
        public array  $names = [],
        public array  $parameters = [],
        public array  $scoped = [],
        public bool   $isShallow = false,
        public string $missingRedirect = ''
    )
    {
    }
}
