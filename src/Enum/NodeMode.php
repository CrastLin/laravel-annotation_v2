<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum NodeMode: int
{
    use EnumMethod;

    /**
     * Loose mode
     * ignore if there are public methods that have not been annotated with nodes
     */
    case LOOSE_MODE = 1;

    /**
     * Strict mode
     * detects all public methods, throws an exception if the node is not annotated
     */
    case STRICT_MODE = 2;

}
