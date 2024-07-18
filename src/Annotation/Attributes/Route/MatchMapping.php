<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Route;

use Crastlin\LaravelAnnotation\Annotation\AnnotationException;
use Crastlin\LaravelAnnotation\Annotation\Attributes\Router;
use Crastlin\LaravelAnnotation\Enum\Method;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class MatchMapping extends Router
{

    /**
     * @var Method[] $methods
     */
    public array $methods;

    /**
     * @param Method[] $methods
     * @param string $path
     * @param string $name
     */
    public function __construct(
        array         $methods = [Method::GET, Method::POST],
        public string $path = '',
        public string $name = '',
    )
    {
        if (!Method::isMatchedAll($methods))
            throw new AnnotationException("MatchMapping Annotation Errors: The request method you defined does not match");
        $this->methods = $methods;
    }
}
