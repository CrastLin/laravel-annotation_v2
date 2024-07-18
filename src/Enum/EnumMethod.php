<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Enum;

trait EnumMethod
{

    static function names(): array
    {
        return array_column(static::cases(), 'name');
    }

    static function values(): array
    {
        return array_column(static::cases(), 'value');
    }

    static function getValuesByEnums(array $enums): array
    {
        if (empty($enums))
            return [];
        $values = [];
        foreach ($enums as $enum) {
            if ($enum instanceof static)
                $values[] = $enum->value;
        }
        return $values;
    }

    static function isMatched(mixed $enum): bool
    {
        $values = static::values();
        $value = $enum instanceof static ? $enum->value : null;
        return !is_null($value) && in_array($value, $values);
    }

    static function isMatchedAll(array $methods): bool
    {
        $methods = self::getValuesByEnums($methods);
        if (empty($methods))
            return false;
        $values = static::values();
        $different = array_diff($methods, $values);
        return empty($different);
    }

}
