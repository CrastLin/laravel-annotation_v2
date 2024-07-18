<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Sync;


abstract class SyncBase implements Locker
{
    protected string $prefix = 'sync_lock_state', $director = '/', $lockName;
    protected array $options;

    /**
     * SyncBase constructor.
     * @param string $name
     * @param string $prefix
     * @param array $options
     */
    function __construct(string $name, string $prefix = '', array $options = [])
    {
        $this->prefix = $prefix ?: $this->prefix;
        $this->lockName = "{$this->prefix}{$this->director}{$name}";
        $this->options = $options;
    }


    /**
     * get a sync lock
     * @param int $expire
     * @return bool
     */
    abstract function lock(int $expire = 60): bool;

    /**
     * remove a sync lock
     * @return bool
     */
    abstract function unlock(): bool;

}
