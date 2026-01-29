<?php

declare(strict_types=1);

namespace App\Core;

class Storage
{
    private static ?Storage $instance = null;
    private string $dataPath;
    private array $cache = [];

    private function __construct(string $dataPath)
    {
        $this->dataPath = rtrim($dataPath, '/\\');
        $this->ensureDirectory();
        $this->initializeFiles();
    }

    public static function getInstance(string $dataPath): Storage
    {
        if (self::$instance === null) {
            self::$instance = new self($dataPath);
        }
        return self::$instance;
    }

    private function ensureDirectory(): void
    {
        if (!is_dir($this->dataPath)) {
            mkdir($this->dataPath, 0755, true);
        }
    }

    private function initializeFiles(): void
    {
        foreach (['payments', 'pse_transactions'] as $collection) {
            $file = $this->getFilePath($collection);
            if (!file_exists($file)) {
                file_put_contents($file, json_encode([], JSON_PRETTY_PRINT));
            }
        }
    }

    private function getFilePath(string $collection): string
    {
        return $this->dataPath . '/' . $collection . '.json';
    }

    public function save(string $collection, array $data): int
    {
        $items = $this->getAll($collection);
        $id = $items ? max(array_column($items, 'id')) + 1 : 1;
        $data['id'] = $id;
        $items[] = $data;
        $this->write($collection, $items);
        return $id;
    }

    public function findById(string $collection, int $id): ?array
    {
        return $this->findOneBy($collection, ['id' => $id]);
    }

    public function findBy(string $collection, array $criteria): array
    {
        return array_values(array_filter($this->getAll($collection), function ($item) use ($criteria) {
            foreach ($criteria as $key => $value) {
                if (!isset($item[$key]) || $item[$key] !== $value) {
                    return false;
                }
            }
            return true;
        }));
    }

    public function findOneBy(string $collection, array $criteria): ?array
    {
        $results = $this->findBy($collection, $criteria);
        return $results[0] ?? null;
    }

    public function update(string $collection, int $id, array $data): bool
    {
        $items = $this->getAll($collection);
        $updated = false;

        foreach ($items as $key => $item) {
            if ($item['id'] === $id) {
                $items[$key] = array_merge($item, $data);
                $items[$key]['id'] = $id;
                $updated = true;
                break;
            }
        }

        if ($updated) {
            $this->write($collection, $items);
        }

        return $updated;
    }

    public function delete(string $collection, int $id): bool
    {
        $items = $this->getAll($collection);
        $filtered = array_values(array_filter($items, fn($item) => $item['id'] !== $id));

        if (count($filtered) < count($items)) {
            $this->write($collection, $filtered);
            return true;
        }

        return false;
    }

    public function getAll(string $collection): array
    {
        if (isset($this->cache[$collection])) {
            return $this->cache[$collection];
        }

        $file = $this->getFilePath($collection);
        if (!file_exists($file)) {
            return [];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true) ?? [];
        $this->cache[$collection] = $data;

        return $data;
    }

    private function write(string $collection, array $data): void
    {
        file_put_contents($this->getFilePath($collection), json_encode($data, JSON_PRETTY_PRINT));
        $this->cache[$collection] = $data;
    }

    public function clear(string $collection): void
    {
        $this->write($collection, []);
    }

    public function count(string $collection, array $criteria = []): int
    {
        return count($criteria ? $this->findBy($collection, $criteria) : $this->getAll($collection));
    }
}
