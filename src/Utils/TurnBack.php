<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils;

use Crastlin\LaravelAnnotation\Extra\ResponseCode;

class TurnBack
{
    public ResponseCode $code;
    public string $message;
    public array $data;

    public function __construct(ResponseCode $code, string $message, ?array $data = [])
    {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    static function intoResult(ResponseCode $code, string $message, ?array $data = []): TurnBack
    {
        return new self($code, $message, $data);
    }

    function toArray(array $fieldSet = []): array
    {
        return [
                $fieldSet['code'] ?? 'code' => $this->code->value,
                $fieldSet['message'] ?? 'msg' => $this->message,
                $fieldSet['data'] ?? 'data' => $this->data
        ];
    }

    function toJson(array $fieldSet = []): string
    {
        return json_encode($this->toArray($fieldSet), 256);
    }
}
