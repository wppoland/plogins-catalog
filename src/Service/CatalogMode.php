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
    private string $singleReplacement = '';

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

        if (! $this->shouldHidePrice($product)) {
            return $price;
        }

        $notice = $this->priceNotice($product);

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

        if (! $this->shouldHideAddToCart($product)) {
            return;
        }

        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);

        $this->singleReplacement = $this->addToCartReplacement($product, 'single');

        if ('' !== $this->singleReplacement) {
            add_action('woocommerce_single_product_summary', [$this, 'renderSingleReplacement'], 30);
        }
    }

    public function renderSingleReplacement(): void
    {
        if ('' === $this->singleReplacement) {
            return;
        }

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted HTML from catalog/add_to_cart_replacement filters.
        echo $this->singleReplacement;
    }

    /**
     * In loops, remove the add-to-cart link for catalog products.
     */
    public function filterLoopButton(mixed $html, mixed $product): mixed
    {
        if (! $product instanceof \WC_Product || ! $this->applies()) {
            return $html;
        }

        if (! $this->shouldHideAddToCart($product)) {
            return $html;
        }

        $replacement = $this->addToCartReplacement($product, 'loop');

        return '' !== $replacement ? $replacement : '';
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

        return $this->shouldHideAddToCart($product) ? false : $purchasable;
    }

    private function shouldHidePrice(\WC_Product $product): bool
    {
        $hide = $this->settings->bool('hide_price');

        /**
         * Filters whether the price should be hidden for the current visitor.
         *
         * @param bool        $hide    Whether the FREE settings hide the price.
         * @param \WC_Product $product Product being rendered.
         */
        return (bool) apply_filters('catalog/hide_price', $hide, $product);
    }

    private function shouldHideAddToCart(\WC_Product $product): bool
    {
        $hide = $this->settings->bool('hide_add_to_cart');

        /**
         * Filters whether add-to-cart should be hidden for the current visitor.
         *
         * @param bool        $hide    Whether the FREE settings hide add-to-cart.
         * @param \WC_Product $product Product being rendered.
         */
        return (bool) apply_filters('catalog/hide_add_to_cart', $hide, $product);
    }

    private function priceNotice(\WC_Product $product): string
    {
        $notice = $this->settings->string('price_notice');

        /**
         * Filters the replacement text shown when the price is hidden.
         *
         * @param string      $notice  Notice from FREE settings.
         * @param \WC_Product $product Product being rendered.
         */
        return (string) apply_filters('catalog/price_notice', $notice, $product);
    }

    private function addToCartReplacement(\WC_Product $product, string $context): string
    {
        /**
         * Filters HTML shown in place of add-to-cart when catalog mode hides the cart.
         *
         * @param string      $html    Replacement HTML. Empty leaves the slot blank.
         * @param \WC_Product $product Product being rendered.
         * @param string      $context `single` or `loop`.
         */
        $html = apply_filters('catalog/add_to_cart_replacement', '', $product, $context);

        return is_string($html) ? $html : '';
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
