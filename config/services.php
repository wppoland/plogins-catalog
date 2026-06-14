<?php
/**
 * Service wiring. Returns a closure that registers every service in the
 * container. Services are thin and self-contained — this plugin has no external
 * runtime dependencies.
 *
 * @package Catalog
 */

declare(strict_types=1);

use Catalog\Admin\Settings;
use Catalog\Container;
use Catalog\Migrator;
use Catalog\Service\CatalogMode;
use Catalog\Service\Settings as SettingsStore;

defined('ABSPATH') || exit;

return static function (Container $c): void {
    $c->singleton(Migrator::class, static fn (): Migrator => new Migrator());

    // Shared, read-only settings accessor.
    $c->singleton(SettingsStore::class, static fn (): SettingsStore => new SettingsStore());

    // Storefront: hide price / add-to-cart for catalog products.
    $c->singleton(CatalogMode::class, static fn (): CatalogMode => new CatalogMode(
        $c->get(SettingsStore::class),
    ));

    // Admin (only needed in wp-admin context).
    if (is_admin()) {
        $c->singleton(Settings::class, static fn (): Settings => new Settings());
    }
};
