<?php

declare(strict_types=1);

namespace App\Core;

class Container
{
    private array $services = [];
    private array $instances = [];

    public function set(string $name, callable $resolver): void
    {
        $this->services[$name] = $resolver;
        unset($this->instances[$name]);
    }

    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \InvalidArgumentException("Service '$name' not found");
        }

        return $this->instances[$name] ??= $this->services[$name]($this);
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    public function make(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \InvalidArgumentException("Service '$name' not found");
        }

        return $this->services[$name]($this);
    }
}
