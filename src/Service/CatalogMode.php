<?php

declare(strict_types=1);

namespace Catalog\Service;

use Catalog\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Storefront catalog behaviour.
 *
 * Decides — per current user — whether catalog mode applies, then hides the
 * price and/or the add-to-cart button on single product pages and in
 * shop/category/related loops. The decision combines the master switch with the
 * role rule (everyone / guests / specific roles / except roles), letting a store
 * show prices to, say, logged-in wholesale customers while hiding them from
 * everyone else.
 */
final class CatalogMode implements HasHooks
{
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

        // Single product: remove the add-to-cart form.
        add_action('woocommerce_single_product_summary', [$this, 'maybeReplaceSingle'], 1);

        // Loops: remove the add-to-cart link.
        add_filter('woocommerce_loop_add_to_cart_link', [$this, 'filterLoopButton'], 100, 2);

        // Belt-and-braces: block server-side add-to-cart for catalog products.
        add_filter('woocommerce_is_purchasable', [$this, 'filterPurchasable'], 100, 2);

        add_action('wp_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Whether catalog mode applies for the current visitor.
     */
    public function applies(): bool
    {
        $applies = $this->roleRuleMatches();

        /**
         * Filters whether catalog mode applies for the current visitor.
         *
         * Add-ons (e.g. Catalog Pro's scheduled windows) can force catalog
         * mode off — or leave the FREE decision untouched — by returning a
         * boolean here. Runs on every price/add-to-cart decision.
         *
         * @param bool $applies Whether the FREE plugin decided catalog mode applies.
         */
        return (bool) apply_filters('catalog/applies', $applies);
    }

    /**
     * Hide (or replace) the price HTML on catalog products.
     */
    public function filterPriceHtml(mixed $price, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies()) {
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
     * On single product pages, remove the add-to-cart form for catalog products.
     */
    public function maybeReplaceSingle(): void
    {
        global $product;

        if (! $product instanceof \WC_Product || ! $this->applies()) {
            return;
        }

        if (! $this->settings->bool('hide_add_to_cart')) {
            return;
        }

        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    }

    /**
     * In loops, remove the add-to-cart link for catalog products.
     */
    public function filterLoopButton(mixed $html, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies()) {
            return $html;
        }

        return $this->settings->bool('hide_add_to_cart') ? '' : $html;
    }

    /**
     * Prevent catalog products from being purchased server-side (direct cart
     * URLs, REST, etc.) when add-to-cart is hidden.
     */
    public function filterPurchasable(mixed $purchasable, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies()) {
            return $purchasable;
        }

        return $this->settings->bool('hide_add_to_cart') ? false : $purchasable;
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
