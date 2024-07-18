<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes;

use Crastlin\LaravelAnnotation\Enum\Rule;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class Validation
{

    /**
     * @param string $field
     * Verify field name, if it is a row parameter annotation, then it is the parameter name
     * @param Rule|string $rule
     * Validation rule class name, can specify validation class annotation
     * @param string $ruleValue
     * Define the rule parameter values for validation rules
     * @param string $attribute
     * Set the prompt name corresponding to the field
     * @param string $message
     * Set verification failure prompt message
     * @param string $class
     * Specify a custom validator that must inherit the Validate class and define validation rules
     * @param array $rules
     * Set batch validation rules, dictionary array type, key name as validation name, value as validation rule
     * @param array $messages
     * Set batch validation failure message, dictionary array type, key name as validation name, value as validation rule
     * @param array $attributes
     * Set batch validation field names, key name as field name, value as tips name
     *
     */
    public function __construct(
        public string      $field = '',
        public Rule|string $rule = '',
        public string      $ruleValue = '',
        public string      $attribute = '',
        public string      $message = '',
        public string      $class = '',
        public array       $rules = [],
        public array       $messages = [],
        public array       $attributes = [],
    )
    {
    }
}
