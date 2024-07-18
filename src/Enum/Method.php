<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum Method: string
{
    use EnumMethod;

    case POST = 'POST';
    case GET = 'GET';
    case OPTIONS = 'OPTIONS';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
    case ANY = 'ANY';
    case PATCH = 'PATCH';

}
