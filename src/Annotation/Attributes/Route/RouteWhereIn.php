<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhereIn extends Router
{

    public Constraint $pattern = Constraint::IN;

    /**
     * @param string $keys
     * @param string[] $values Agree on specified values
     * Agree that the specified parameter set is the specified values
     * @example #[RouteWhereIn(url: "/demo/test/{id}/{type}", "id", ["12", "2"]]
     * @example #[RouteWhereIn(url: "/demo/test/{id}/{type}", "id,type", values: "12,2"]
     */
    public function __construct(
        public string       $path,
        public string       $keys,
        public array|string $values,
        public Method       $method = Method::POST,
        public string       $name = '',
    )
    {
    }
}
