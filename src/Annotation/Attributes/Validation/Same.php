<?php

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Validation;

use Crastlin\LaravelAnnotation\Enum\Rule;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PARAMETER | \Attribute::IS_REPEATABLE)]
class Same extends ValidateBase
{
    public Rule $rule = Rule::SAME;
}
