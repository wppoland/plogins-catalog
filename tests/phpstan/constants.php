<?php
/**
 * Constants needed by PHPStan to analyse the plugin without bootstrapping
 * WordPress or running the main plugin file.
 *
 * @package Catalog
 */

declare(strict_types=1);

namespace {
    if (! defined('ABSPATH')) {
        define('ABSPATH', '/tmp/wordpress/');
    }
    if (! defined('CATALOG_DIR')) {
        define('CATALOG_DIR', '/tmp/catalog/');
    }
    if (! defined('CATALOG_URL')) {
        define('CATALOG_URL', 'https://example.test/wp-content/plugins/catalog/');
    }
}

namespace Catalog {
    if (! defined('Catalog\\VERSION')) {
        define('Catalog\\VERSION', '0.1.0');
    }
    if (! defined('Catalog\\PLUGIN_FILE')) {
        define('Catalog\\PLUGIN_FILE', '/tmp/catalog/catalog.php');
    }
}
