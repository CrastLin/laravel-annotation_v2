<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum ResourceEnum: string
{
    use EnumMethod;

    case INDEX = 'index';
    case CREATE = 'create';
    case STORE = 'store';
    case SHOW = 'show';
    case EDIT = 'edit';
    case UPDATE = 'update';
    case DESTROY = 'destroy';
}
