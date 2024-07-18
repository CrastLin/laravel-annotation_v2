<?php
declare (strict_types=1);

namespace Crastlin\LaravelAnnotation\Utils;

use Crastlin\LaravelAnnotation\Utils\Traits\RedisClientTrait;

final class RedisClient
{
    use RedisClientTrait;

    private static ?array $singletonContainers = [];

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
        if (PHP_SAPI == 'cli') {
            return new self($options);
        } else {
            if (!isset(self::$singletonContainers[$select]) || !isset(self::$singletonContainers[$select]->redis) || !self::$singletonContainers[$select]->redis->isConnected()) {
                self::$singletonContainers[$select] = new self($options);
            }
            return self::$singletonContainers[$select];
        }
    }

    /**
     * get redis instance
     * @return \Redis
     */
    public function getInstance(): \Redis
    {
        return $this->redis;
    }


    public function __destruct()
    {
        if (isset(self::$singletonContainers)) {
            foreach (self::$singletonContainers as $instance):
                if (isset($instance->redis) && $instance->redis->isConnected())
                    $instance->redis->close();
            endforeach;
        }
        self::$singletonContainers = null;
    }

}
