<?php
declare (strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils;

use Crastlin\LaravelAnnotation\Facades\Context;
use Crastlin\LaravelAnnotation\Utils\Traits\RedisClientTrait;

final class RedisClient
{
    use RedisClientTrait;

    protected array $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'db' => 0,
        'coroutineUnique' => '',
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
    ];
    /**
     * @var \Redis $redis
     */
    private \Redis $redis;


    /**
     * hash的key
     *
     * @var string
     */
    private string $singleKey;

    /**
     * Redis constructor.
     * @param array $options
     */
    function __construct(array $options)
    {
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->redis = new \Redis();
        $persistentId = 'persistent_id_' . md5(serialize($options));
        if ($this->options['persistent']) {
            $this->redis->pconnect((string)$this->options['host'], (int)$this->options['port'], (float)$this->options['timeout'], $persistentId);
        } else {
            $this->redis->connect((string)$this->options['host'], (int)$this->options['port'], (float)$this->options['timeout']);
        }

        if ('' != $this->options['password']) {

            $this->redis->auth((string)$this->options['password']);
        }

        if (0 != $this->options['db']) {
            $this->redis->select((int)$this->options['db']);
        }

    }


    /**
     * get redis singleton
     * @param ?int $select
     * @param ?array $options
     * @return self
     */
    static function singleton(?int $select = null, ?array $options = []): self
    {
        $options = !empty($options) ? $options : config('annotation.redis.master');
        $options['db'] = !is_null($select) ? $select : (!empty($options['db']) ? (int)$options['db'] : 0);
        $rk = "redis_client_" . md5("{$options['host']}:{$options['port']}:{$options['db']}");
        $instance = Context::exists($rk) ? Context::get($rk) : null;
        if (!$instance) {
            $instance = new self($options);
            Context::set($rk, $instance);
        }
        return $instance;
    }

    /**
     * get redis instance
     * @return \Redis
     */
    public function getInstance(): \Redis
    {
        return $this->redis;
    }
}
