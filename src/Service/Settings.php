<?php

declare(strict_types=1);

namespace Catalog\Service;

defined('ABSPATH') || exit;

/**
 * Read-only settings accessor. Loads the stored `catalog_settings` option,
 * merges it over the shipped defaults and exposes typed getters so every other
 * service shares one source of truth. Stateless apart from a per-request cache.
 */
final class Settings
{
    public const OPTION = 'catalog_settings';

    /** @var array<string, mixed>|null */
    private ?array $cache = null;

    /**
     * The full, defaults-merged settings array.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (null !== $this->cache) {
            return $this->cache;
        }

        $stored = get_option(self::OPTION, []);

        if (! is_array($stored)) {
            $stored = [];
        }

        /** @var array<string, mixed> $defaults */
        $defaults = require CATALOG_DIR . 'config/defaults.php';

        return $this->cache = array_merge($defaults, $stored);
    }

    public function get(string $key, mixed $fallback = null): mixed
    {
        $all = $this->all();

        return $all[$key] ?? $fallback;
    }

    public function bool(string $key): bool
    {
        return (bool) $this->get($key, false);
    }

    public function string(string $key): string
    {
        return trim((string) $this->get($key, ''));
    }

    /**
     * @return list<string>
     */
    public function roleList(): array
    {
        $list = $this->get('role_list', []);

        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter(array_map('strval', $list)));
    }

    /**
     * Clear the per-request cache (used after a save in the same request).
     */
    public function flush(): void
    {
        $this->cache = null;
    }
}
