<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Constraint;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class RouteWhereAlpha extends Router
{
    public Constraint $pattern = Constraint::ALPHA;

    /**
     * @param string $keys Letter type constraints
     * The convention specifies that the parameter set is of letter type
     * @example #[RouteWhereAlpha(url: "/demo/test/{nickname}/{username}", keys: "nickname,username"]
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
