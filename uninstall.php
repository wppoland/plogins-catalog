<?php
/**
 * Uninstall cleanup for Catalog.
 *
 * Runs when the plugin is deleted from wp-admin. Removes the plugin's options
 * on every site of a network, since each site keeps its own.
 *
 * @package Catalog
 */

declare(strict_types=1);

defined('WP_UNINSTALL_PLUGIN') || exit;

function catalog_uninstall_cleanup(): void
{
    delete_option('catalog_settings');
    delete_option('catalog_db_version');
}

if (is_multisite()) {
    $catalog_site_ids = get_sites(['fields' => 'ids', 'number' => 0]);

    foreach ($catalog_site_ids as $catalog_site_id) {
        switch_to_blog((int) $catalog_site_id);
        catalog_uninstall_cleanup();
        restore_current_blog();
    }

    unset($catalog_site_ids, $catalog_site_id);
} else {
    catalog_uninstall_cleanup();
}

// The PRO banner's dismissal is stored per user, so it belongs to the
// plugin rather than to the site content. User meta is global, not
// per-site, which is why this uses delete_metadata's \$delete_all rather
// than a loop over the users of one blog.
delete_metadata('user', 0, 'catalog_pro_banner_dismissed', '', true);
