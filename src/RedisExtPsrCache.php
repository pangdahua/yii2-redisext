<?php

namespace FRFT;

use Yii;
use yii\helpers\StringHelper;
use Exception;

class RedisExtPsrCache extends \yii\base\Component implements \Psr\SimpleCache\CacheInterface
{
    public string $redis;

    public string $keyPrefix = '';

    public function getRedis()
    {
        return Yii::$app->get($this->redis);
    }

    public function buildKey($key): string
    {
        if (is_string($key)) {
            $key = strlen($key) <= 32 ? $key : md5($key);
        } else {
            $serializedKey = serialize($key);
            $key = md5($serializedKey);
        }

        return $this->keyPrefix . $key;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->getRedis()->get($this->buildKey($key));
    }

    public function set(string $key, mixed $value, $ttl = null): bool
    {
        $cacheKey = $this->buildKey($key);
        $rst = $this->getRedis()->set($cacheKey, $value);
        if (null !== $ttl) {
            $this->getRedis()->expire($cacheKey, $ttl);
        }

        return $rst;
    }

    public function delete(string $key): bool
    {
        $this->getRedis()->delete($this->buildKey($key));
        return true;
    }

    public function clear(): bool
    {
        throw new Exception('Not implemented');
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        throw new Exception('Not implemented');
    }

    public function setMultiple(iterable $values, $ttl = null): bool
    {
        throw new Exception('Not implemented');
    }

    public function deleteMultiple(iterable $keys): bool
    {
        throw new Exception('Not implemented');
    }

    public function has(string $key): bool
    {
        return $this->getRedis()->exists($this->buildKey($key)) == 1;
    }
}