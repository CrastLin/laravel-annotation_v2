<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

enum Constraint: string
{
    use EnumMethod;

    /**
     * Constraint request parameters are regular expressions
     * set expression value:['id' =>'[0-9]+','name' => '[a-zA-Z]+']
     */
    case MATCH = 'match';

    /**
     * Constrain request parameter is of type to letter type
     * Including a-zA-Z
     */
    case ALPHA = 'alpha';

    /**
     * Constraint request parameter is of type is a combination of letters or numbers
     * Including a-zA-Z or 0-9
     */
    case ALPHA_NUMERIC = 'alphaNumeric';

    /**
     * Constraint type is numeric
     * Including 0-9
     */
    case NUMBER = 'number';

    /**
     * Constraint request parameter is of the specified numeric type
     */
    case IN = 'in';

    /**
     * Constraint request parameter is of type uuid
     */
    case UUID = 'uuid';

}
