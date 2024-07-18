<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhereAlphaNum extends Router
{

    public Constraint $pattern = Constraint::ALPHA_NUMERIC;

    /**
     * @param string $keys Letter type and number constraints
     * The convention specifies that the parameter set is of letter and number types
     * @example #[RouteWhereAlphaNum(url: "/demo/test/{username}", keys: "username"]
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
