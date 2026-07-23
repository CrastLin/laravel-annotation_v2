<?php

namespace Crastlin\LaravelAnnotation\Enum;

enum InjectionEnum: string
{
    use EnumMethod;

    case ALL = 'all';

    case CONSTRUCTOR = 'constructor';

    case PROPERTY = 'property';

    case METHOD = 'method';

}
