<?php

namespace FRFT;

use http\Exception\RuntimeException;
use yii\helpers\StringHelper;

class RedisExtPsrCache extends \yii\base\Compponent implements \Psr\SimpleCache\CacheInterface
{
    public RedisExt $redis;

    public string $keyPrefix = '';

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
        return $this->redis->get($this->buildKey($key));
    }

    public function set(string $key, mixed $value, $ttl = null): bool
    {
        $cacheKey = $this->buildKey($key);
        $this->redis->set($cacheKey, $value);

        if (null !== $ttl) {
            $this->redis->expire($cacheKey, $ttl);
        }
    }

    public function delete(string $key): bool
    {
        $this->redis->delete($this->buildKey($key));
    }

    public function clear(): bool
    {
        throw new RuntimeException('Not implemented');
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        throw new RuntimeException('Not implemented');
    }

    public function setMultiple(iterable $values, $ttl = null): bool
    {
        throw new RuntimeException('Not implemented');
    }

    public function deleteMultiple(iterable $keys): bool
    {
        throw new RuntimeException('Not implemented');
    }

    public function has(string $key): bool
    {
        return $this->redis->exists($this->buildKey($key)) == 1;
    }
}