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

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'catalog_pro_banner_dismissed', '', true);
