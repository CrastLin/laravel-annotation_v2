<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation;

use Exception;
use Throwable;

class AnnotationException extends Exception implements Throwable
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        return __CLASS__ . "[{$this->code}]{$this->message} {$this->file} --> {$this->line}";
    }
}
