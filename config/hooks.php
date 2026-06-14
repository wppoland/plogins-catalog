<?php
/**
 * Boot order: services listed here are resolved from the container and have
 * their registerHooks() called during Plugin::boot(). Each must implement
 * Catalog\Contract\HasHooks.
 *
 * @package Catalog
 *
 * @return array<class-string>
 */

declare(strict_types=1);

use Catalog\Admin\Settings;
use Catalog\Service\CatalogMode;

defined('ABSPATH') || exit;

return is_admin()
    ? [
        CatalogMode::class,
        Settings::class,
    ]
    : [
        CatalogMode::class,
    ];
