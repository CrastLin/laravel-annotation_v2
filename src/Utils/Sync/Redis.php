<?php
declare(strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils\Sync;

use Crastlin\LaravelAnnotation\Utils\RedisClient;
use Crastlin\LaravelAnnotation\Utils\Traits\RedisClientTrait;
use \Throwable;

class Redis extends SyncBase
{
    use RedisClientTrait;

    protected string $director = ':';

    protected \Redis $redis;

    public function __construct(string $name, string $prefix = '', array $options = [])
    {
        parent::__construct($name, $prefix, $options);
        $select = $this->options['select'] ?? null;
        $this->redis = RedisClient::singleton($select, $this->options)->getInstance();
    }

    /**
     * get a lock
     * @param int $expire
     * @return bool
     * @throws Throwable
     */
    function lock(int $expire = 60): bool
    {

        $state = $this->redis->set($this->mk($this->lockName), 1, ['nx', 'ex' => $expire]);
        return !empty($state);
    }

    /**
     * unlock
     * @return bool
     * @throws Throwable
     */
    function unlock(): bool
    {
        $result = $this->redis->del($this->mk($this->lockName));
        return !empty($result);
    }


}
