<?php
/**
 * Uninstall cleanup for Catalog.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the plugin's options.
 * Per-product and per-category overrides are stored as term/post meta and are
 * intentionally left in place: they are merchant content that should survive a
 * reinstall and can be removed manually.
 *
 * @package Catalog
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('catalog_settings');
delete_option('catalog_db_version');
