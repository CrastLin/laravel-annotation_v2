<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

interface Annotator
{
    static function build(array $analysisResult, string $savePath): void;

}
