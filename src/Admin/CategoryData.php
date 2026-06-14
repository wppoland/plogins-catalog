<?php

declare(strict_types=1);

namespace Catalog\Admin;

use Catalog\Contract\HasHooks;
use Catalog\Service\CatalogMode;

defined('ABSPATH') || exit;

/**
 * Adds a "Catalog mode" override field to the product category (product_cat)
 * add/edit screens. A category can force catalog mode on (used by the
 * "selected categories" scope) or off (to exempt a category from store-wide
 * catalog mode).
 */
final class CategoryData implements HasHooks
{
    private const TAXONOMY = 'product_cat';
    private const NONCE    = 'catalog_category_data';

    public function registerHooks(): void
    {
        add_action(self::TAXONOMY . '_add_form_fields', [$this, 'renderAddField']);
        add_action(self::TAXONOMY . '_edit_form_fields', [$this, 'renderEditField']);
        add_action('created_' . self::TAXONOMY, [$this, 'save']);
        add_action('edited_' . self::TAXONOMY, [$this, 'save']);
    }

    public function renderAddField(): void
    {
        wp_nonce_field(self::NONCE, 'catalog_category_nonce');
        ?>
        <div class="form-field term-catalog-mode-wrap">
            <label for="catalog_mode"><?php esc_html_e('Catalog mode', 'catalog'); ?></label>
            <select name="catalog_mode" id="catalog_mode">
                <?php $this->options('inherit'); ?>
            </select>
            <p class="description">
                <?php esc_html_e('Override catalog mode for products in this category.', 'catalog'); ?>
            </p>
        </div>
        <?php
    }

    public function renderEditField(\WP_Term $term): void
    {
        $value = (string) get_term_meta($term->term_id, CatalogMode::TERM_META, true);
        $value = in_array($value, ['on', 'off'], true) ? $value : 'inherit';

        wp_nonce_field(self::NONCE, 'catalog_category_nonce');
        ?>
        <tr class="form-field term-catalog-mode-wrap">
            <th scope="row">
                <label for="catalog_mode"><?php esc_html_e('Catalog mode', 'catalog'); ?></label>
            </th>
            <td>
                <select name="catalog_mode" id="catalog_mode">
                    <?php $this->options($value); ?>
                </select>
                <p class="description">
                    <?php esc_html_e('Override catalog mode for products in this category.', 'catalog'); ?>
                </p>
            </td>
        </tr>
        <?php
    }

    public function save(int $termId): void
    {
        $nonce = isset($_POST['catalog_category_nonce'])
            ? sanitize_text_field(wp_unslash($_POST['catalog_category_nonce']))
            : '';

        if (! wp_verify_nonce($nonce, self::NONCE)) {
            return;
        }

        if (! current_user_can('manage_product_terms')) {
            return;
        }

        $raw   = isset($_POST['catalog_mode'])
            ? sanitize_text_field(wp_unslash($_POST['catalog_mode']))
            : 'inherit';
        $value = in_array($raw, ['on', 'off'], true) ? $raw : 'inherit';

        if ('inherit' === $value) {
            delete_term_meta($termId, CatalogMode::TERM_META);
            return;
        }

        update_term_meta($termId, CatalogMode::TERM_META, $value);
    }

    private function options(string $selected): void
    {
        $options = [
            'inherit' => __('Use store rules', 'catalog'),
            'on'      => __('Force on for this category', 'catalog'),
            'off'     => __('Force off for this category', 'catalog'),
        ];

        foreach ($options as $value => $label) {
            printf(
                '<option value="%1$s"%2$s>%3$s</option>',
                esc_attr($value),
                selected($selected, $value, false),
                esc_html($label),
            );
        }
    }
}
