<?php
/**
 * Uninstall cleanup for Catalog.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the plugin's options.
 *
 * @package Catalog
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

delete_option('catalog_settings');
delete_option('catalog_db_version');
