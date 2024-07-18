<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhereNumeric extends Router
{

    public Constraint $pattern = Constraint::NUMBER;

    /**
     * @param string $keys Numerical constraints
     * Constraint specifies that the parameter set is of numeric types
     * @example #[RouteWhereNumeric(url: "/demo/test/{id}/{mobile}", keys: "mobile,id"]
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
