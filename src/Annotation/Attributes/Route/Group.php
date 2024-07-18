<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Group
{

    public function __construct(
        public string       $prefix,
        public string|array $middleware = '',
        public string       $domain = '',
    )
    {
    }


}
