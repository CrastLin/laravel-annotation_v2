<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhereUuid extends Router
{

    public Constraint $pattern = Constraint::UUID;

    /**
     * @param string $keys UUID type constraints
     * The convention specifies that the parameter set is of UUID type
     * @example #[RouteWhereUuid(url: "/demo/test/{token}", keys: "token"]
     */
    public function __construct(
        public string $path,
        public string $keys = '*',
        public Method $method = Method::POST,
        public string $name = '',
    )
    {
    }
}
