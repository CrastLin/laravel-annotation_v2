<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route extends Router
{

    /**
     * @param string $path
     * @param Method $method
     * @param Method[] $methods
     * @param string $name
     * @param array $where
     */
    public function __construct(
        public string $path = '',
        public Method $method = Method::ANY,
        public array  $methods = [],
        public string $name = '',
        public array  $where = []
    )
    {
    }
}
