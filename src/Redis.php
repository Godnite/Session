<?php

declare(strict_types=1);

namespace Rancoud\Session;

use Predis\Client as Predis;
use SessionHandlerInterface;

/**
 * Class Redis.
 */
class Redis implements SessionHandlerInterface
{
    /**
     * @var Predis
     */
    protected $redis;
    protected $lifetime = 1440;

    public function setNewRedis($configuration)
    {
        $this->redis = new Predis($configuration);
    }

    public function setCurrentRedis($redis)
    {
        $this->redis = $redis;
    }

    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * @param $savePath
     * @param $sessionName
     *
     * @return bool
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * @param $sessionId
     *
     * @return string
     */
    public function read($sessionId): string
    {
        return (string) $this->redis->get($sessionId);
    }

    /**
     * @param $sessionId
     * @param $data
     *
     * @return bool
     */
    public function write($sessionId, $data): bool
    {
        $this->redis->set($sessionId, $data);
        $this->redis->expireat($sessionId, time() + $this->lifetime);

        return true;
    }

    /**
     * @param $sessionId
     *
     * @return bool
     */
    public function destroy($sessionId): bool
    {
        $this->redis->del([$sessionId]);

        return true;
    }

    /**
     * @param $lifetime
     *
     * @return bool
     */
    public function gc($lifetime): bool
    {
        return true;
    }
}
