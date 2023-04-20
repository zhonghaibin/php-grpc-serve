<?php

namespace App\support;

use ArrayAccess;

class ConfigManager implements ArrayAccess
{
    protected array $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value)
    {
        $this->config[$name] = $value;
    }

    public function get($key, $default = null)
    {
        $config = $this->config;

        if (isset($config[$key])) {
            return $config[$key];
        }

        if (!str_contains($key, '.')) {
            return $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($config) || !array_key_exists($segment, $config)) {
                return $default;
            }
            $config = $config[$segment];
        }

        return $config;
    }

    public function getAll(): array
    {
        return $this->config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->config);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (isset($this->config[$offset])) {
            $this->config[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        if (isset($this->config[$offset])) {
            unset($this->config[$offset]);
        }
    }

    public function setAll($config)
    {
        $this->config = $config;
    }
}
