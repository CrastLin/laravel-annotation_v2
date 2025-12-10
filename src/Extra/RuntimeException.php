<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Extra;

use Exception;
use Throwable;

class RuntimeException extends Exception implements Throwable
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string
    {
        $datetime = date('Y-m-d H:i:s');
        return "[{$datetime}]" . __CLASS__ . "[{$this->code}]{$this->message} {$this->file} --> {$this->line}\r\n" . parent::getTraceAsString();
    }
}
