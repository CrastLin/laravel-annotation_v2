<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhere extends Router
{
    public Constraint $pattern = Constraint::MATCH;

    /**
     * @param array<string, string> $where Regular constraint conditions
     * Routing with parameter regularization constraints
     * Defined as an associative array type, with the key name being the routing parameter name and the value being a regular expression
     * @example #[RouteWhere(url: "/demo/test/{id}/{username?}", where: ["id" => "\d+", "username" => "\w+"])]
     */
    public function __construct(
        public string $path,
        public array  $where,
        public Method $method = Method::POST,
        public string $name = '',
    )
    {
    }
}
