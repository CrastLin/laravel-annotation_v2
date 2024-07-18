<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils;

use \Exception;
use Crastlin\LaravelAnnotation\Utils\Sync\Locker;

final class Sync
{

    /**
     * create sync
     * @param string $name
     * @param string $prefix
     * @param array $options
     * @param string $driver
     * @return Locker
     * @throws Exception
     */
    static function create(string $name, string $prefix = '', array $options = [], string $driver = 'redis'): Locker
    {
        $class = '\\App\\Annotation\\Utils\\Sync\\' . ucfirst($driver);
        if (!class_exists($class))
            throw new Exception("await ext class: {$class} is not exists", 202);
        return new $class($name, $prefix, $options);
    }

}
