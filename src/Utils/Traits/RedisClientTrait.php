<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Traits;

use Crastlin\LaravelAnnotation\Utils\Sync;

trait RedisClientTrait
{
    /**
     * make redis key with prefix
     * @param string $name
     * @param string|null $prefix
     * @return string
     */
    static function mk(string $name, ?string $prefix = ''): string
    {
        return strtolower((!empty($prefix) ? rtrim($prefix, '_') . '_' : 'service_') . $name);
    }

    /**
     * sync once task
     * @param int $expireTime
     * @param string $name
     * @param string $prefix
     * @param callable|null $callback
     * @return bool
     */
    function syncOnceByTime(int $expireTime, string $name = '', string $prefix = '', callable $callback = null): bool
    {
        if (empty($name)) {
            $names = explode('\\', __CLASS__);
            $name = array_pop($names);
        }
        $sync = Sync::create($name, $prefix ?: 'command_crontab_task');
        if (!is_null($callback)) {
            if (!$sync->lock($expireTime))
                return false;
            $callback($sync);
            return true;
        }
        return $sync->lock($expireTime);
    }

    /**
     * sync daily
     * @param string $name
     * @param string $prefix
     * @param callable|null $callback
     * @return bool
     */
    function syncDaily(string $name = '', string $prefix = '', callable $callback = null): bool
    {
        return $this->syncOnceByTime(86400, $name, $prefix, $callback);
    }

}
