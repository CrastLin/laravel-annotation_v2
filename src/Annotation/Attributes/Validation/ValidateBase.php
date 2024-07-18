<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Annotation\Attributes\Validation;

use Crastlin\LaravelAnnotation\Enum\Rule;

/**
 * Verifier Annotation BaseService Class
 * @Author crastlin@163.cm
 * @Date 2024-3-2
 */
abstract class ValidateBase
{
    public Rule $rule;

    public function __construct(
        public string $field = '',
        public string $attribute = '',
        public string $message = '',
        public ?string $ruleValue = null,
    )
    {
    }
}
