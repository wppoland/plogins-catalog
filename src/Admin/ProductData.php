<?php

declare(strict_types=1);

namespace Catalog\Admin;

use Catalog\Contract\HasHooks;
use Catalog\Service\CatalogMode;

defined('ABSPATH') || exit;

/**
 * Adds a "Catalog mode" override select to the product editor (General tab in
 * the Product data box). The merchant can force catalog mode on or off for an
 * individual product, overriding the store-wide scope and category rules.
 */
final class ProductData implements HasHooks
{
    private const NONCE = 'catalog_product_data';

    public function registerHooks(): void
    {
        add_action('woocommerce_product_options_general_product_data', [$this, 'renderField']);
        add_action('woocommerce_admin_process_product_object', [$this, 'save']);
    }

    public function renderField(): void
    {
        echo '<div class="options_group">';

        wp_nonce_field(self::NONCE, 'catalog_product_data_nonce');

        woocommerce_wp_select([
            'id'          => CatalogMode::META_PRODUCT,
            'value'       => $this->fieldValue(),
            'label'       => __('Catalog mode', 'catalog'),
            'options'     => [
                'inherit' => __('Use store / category rules', 'catalog'),
                'on'      => __('Force on (hide price / cart)', 'catalog'),
                'off'     => __('Force off (always purchasable)', 'catalog'),
            ],
            'desc_tip'    => true,
            'description' => __('Override catalog mode for this product. "Use store / category rules" follows the global Catalog settings.', 'catalog'),
        ]);

        echo '</div>';
    }

    public function save(\WC_Product $product): void
    {
        $nonce = isset($_POST['catalog_product_data_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['catalog_product_data_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }

        $raw   = isset($_POST[CatalogMode::META_PRODUCT])
            ? sanitize_text_field(wp_unslash($_POST[CatalogMode::META_PRODUCT]))
            : 'inherit';
        $value = in_array($raw, ['on', 'off'], true) ? $raw : 'inherit';

        if ('inherit' === $value) {
            $product->delete_meta_data(CatalogMode::META_PRODUCT);
            return;
        }

        $product->update_meta_data(CatalogMode::META_PRODUCT, $value);
    }

    private function fieldValue(): string
    {
        global $post;

        if (! $post instanceof \WP_Post) {
            return 'inherit';
        }

        $product = wc_get_product($post->ID);

        if (! $product instanceof \WC_Product) {
            return 'inherit';
        }

        $value = (string) $product->get_meta(CatalogMode::META_PRODUCT);

        return in_array($value, ['on', 'off'], true) ? $value : 'inherit';
    }
}
