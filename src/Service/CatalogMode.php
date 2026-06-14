<?php

declare(strict_types=1);

namespace Catalog\Service;

use Catalog\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Storefront catalog behaviour.
 *
 * Decides — per product and per current user — whether catalog mode applies,
 * then hides the price and/or the add-to-cart button and (optionally) renders a
 * custom call-to-action button in their place. Works on single product pages and
 * in shop/category/related loops. All decisions run through {@see self::applies()}
 * which combines the scope rule (all / selected products / selected categories)
 * with the role rule (everyone / guests / specific roles / except roles).
 */
final class CatalogMode implements HasHooks
{
    /** Per-product override meta. Values: 'inherit' | 'on' | 'off'. */
    public const META_PRODUCT = '_catalog_mode';

    /** Per-category override term meta. Values: 'inherit' | 'on' | 'off'. */
    public const TERM_META = 'catalog_mode';

    public function __construct(private readonly Settings $settings)
    {
    }

    public function registerHooks(): void
    {
        if (! $this->settings->bool('enabled')) {
            return;
        }

        // Price hiding everywhere the price HTML is generated.
        add_filter('woocommerce_get_price_html', [$this, 'filterPriceHtml'], 100, 2);

        // Single product: swap the add-to-cart form.
        if ($this->settings->bool('apply_on_single')) {
            add_action('woocommerce_single_product_summary', [$this, 'maybeReplaceSingle'], 1);
        }

        // Loops: swap the add-to-cart link.
        if ($this->settings->bool('apply_on_loop')) {
            add_filter('woocommerce_loop_add_to_cart_link', [$this, 'filterLoopButton'], 100, 2);
        }

        // Belt-and-braces: block server-side add-to-cart for catalog products.
        add_filter('woocommerce_is_purchasable', [$this, 'filterPurchasable'], 100, 2);

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Whether catalog mode applies to a given product for the current visitor.
     */
    public function applies(\WC_Product $product): bool
    {
        $result = $this->roleRuleMatches() && $this->scopeMatches($product);

        /**
         * Filter whether catalog mode applies to a product for the current user.
         *
         * @param bool        $result  Whether catalog mode applies.
         * @param \WC_Product $product The product being evaluated.
         */
        return (bool) apply_filters('catalog/applies', $result, $product);
    }

    /**
     * Hide (or replace) the price HTML on catalog products.
     */
    public function filterPriceHtml(mixed $price, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies($product)) {
            return $price;
        }

        if (! $this->settings->bool('hide_price')) {
            return $price;
        }

        $notice = $this->settings->string('price_notice');

        return '' !== $notice
            ? '<span class="catalog-price-notice">' . esc_html($notice) . '</span>'
            : '';
    }

    /**
     * On single product pages, remove the add-to-cart form for catalog products
     * and (optionally) render the CTA button instead.
     */
    public function maybeReplaceSingle(): void
    {
        global $product;

        if (! $product instanceof \WC_Product || ! $this->applies($product)) {
            return;
        }

        if (! $this->settings->bool('hide_add_to_cart')) {
            return;
        }

        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

        if ($this->settings->bool('cta_enabled')) {
            add_action('woocommerce_single_product_summary', [$this, 'renderSingleCta'], 30);
        }
    }

    public function renderSingleCta(): void
    {
        echo $this->ctaHtml(true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- ctaHtml escapes internally.
    }

    /**
     * In loops, replace the add-to-cart link with a CTA (or nothing).
     */
    public function filterLoopButton(mixed $html, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies($product)) {
            return $html;
        }

        if (! $this->settings->bool('hide_add_to_cart')) {
            return $html;
        }

        return $this->settings->bool('cta_enabled') ? $this->ctaHtml(false) : '';
    }

    /**
     * Prevent catalog products from being purchased server-side (direct cart
     * URLs, REST, etc.) when add-to-cart is hidden.
     */
    public function filterPurchasable(mixed $purchasable, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies($product)) {
            return $purchasable;
        }

        return $this->settings->bool('hide_add_to_cart') ? false : $purchasable;
    }

    /**
     * Build the call-to-action button markup. All dynamic values are escaped.
     */
    private function ctaHtml(bool $single): string
    {
        $label = $this->ctaLabel();
        $url   = $this->settings->string('cta_url');

        $classes = 'button catalog-cta';
        if (! $single) {
            $classes .= ' add_to_cart_button';
        }

        if ('' === $url) {
            // No URL configured: render a non-link button so the affordance is
            // still visible but inert (graceful misconfiguration handling).
            return sprintf(
                '<span class="%1$s catalog-cta--inert" aria-disabled="true">%2$s</span>',
                esc_attr($classes),
                esc_html($label),
            );
        }

        $rel    = '';
        $target = '';
        if ($this->settings->bool('cta_new_tab')) {
            $target = ' target="_blank"';
            $rel    = ' rel="noopener noreferrer"';
        }

        return sprintf(
            '<a href="%1$s" class="%2$s"%3$s%4$s>%5$s</a>',
            esc_url($url),
            esc_attr($classes),
            $target,
            $rel,
            esc_html($label),
        );
    }

    private function ctaLabel(): string
    {
        $custom = $this->settings->string('cta_text');

        return '' !== $custom ? $custom : __('Read more', 'catalog');
    }

    /**
     * Whether the current user falls under catalog mode per the role rule.
     */
    private function roleRuleMatches(): bool
    {
        $mode = (string) $this->settings->get('role_mode', 'everyone');

        switch ($mode) {
            case 'guests':
                return ! is_user_logged_in();

            case 'roles':
                return $this->currentUserHasListedRole();

            case 'except_roles':
                return ! $this->currentUserHasListedRole();

            case 'everyone':
            default:
                return true;
        }
    }

    private function currentUserHasListedRole(): bool
    {
        $roles = $this->settings->roleList();

        if ([] === $roles) {
            // No roles selected: a "roles" rule matches nobody; an
            // "except_roles" rule (negated by the caller) matches everybody.
            return false;
        }

        if (! is_user_logged_in()) {
            return false;
        }

        $user = wp_get_current_user();

        return (bool) array_intersect($roles, (array) $user->roles);
    }

    /**
     * Whether the product is in scope (all / selected products / categories),
     * honouring per-product and per-category on/off overrides.
     */
    private function scopeMatches(\WC_Product $product): bool
    {
        // Per-product override always wins.
        $productOverride = (string) $product->get_meta(self::META_PRODUCT);
        if ('on' === $productOverride) {
            return true;
        }
        if ('off' === $productOverride) {
            return false;
        }

        $scope = (string) $this->settings->get('scope', 'all');

        switch ($scope) {
            case 'products':
                // Only products explicitly toggled on (handled above).
                return false;

            case 'categories':
                return $this->productHasCatalogCategory($product);

            case 'all':
            default:
                // Store-wide, but allow a category to opt out via term meta.
                return ! $this->productHasCategoryOverride($product, 'off');
        }
    }

    private function productHasCatalogCategory(\WC_Product $product): bool
    {
        return $this->productHasCategoryOverride($product, 'on');
    }

    /**
     * Whether any of the product's categories carries the given override value.
     */
    private function productHasCategoryOverride(\WC_Product $product, string $value): bool
    {
        $termIds = $product->get_category_ids();

        if (empty($termIds)) {
            return false;
        }

        foreach ($termIds as $termId) {
            if ($value === (string) get_term_meta((int) $termId, self::TERM_META, true)) {
                return true;
            }
        }

        return false;
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_style(
            'catalog',
            CATALOG_URL . 'assets/css/catalog.css',
            [],
            \Catalog\VERSION,
        );
    }
}
