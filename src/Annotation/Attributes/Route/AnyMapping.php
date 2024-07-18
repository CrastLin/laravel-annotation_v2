<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class AnyMapping extends Router
{
    public Method $method = Method::ANY;

    public function __construct(
        public string $path = '',
        public string $name = ''
    )
    {
    }

}
