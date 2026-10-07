<?php

declare(strict_types=1);

namespace Catalog\Service;

use Catalog\Contract\HasHooks;

defined('ABSPATH') || exit;

/**
 * Storefront catalog behaviour.
 *
 * Decides, per current user, whether catalog mode applies, then hides the
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

        // The raw price also leaves through JSON-LD, the variation form data and
        // the Store API, none of which goes through the price HTML.
        add_filter('woocommerce_structured_data_product', [$this, 'filterStructuredData'], 100, 2);
        add_filter('woocommerce_available_variation', [$this, 'filterVariationData'], 100, 3);
        add_filter('rest_request_after_callbacks', [$this, 'filterStoreApiResponse'], 100, 3);
        // Block pages hydrate the same Store API data straight from the
        // controller, skipping REST filters, into the interactivity state.
        add_filter('script_module_data_@wordpress/interactivity', [$this, 'filterInteractivityData'], 100);

        // Single product: remove the add-to-cart form.
        add_action('woocommerce_single_product_summary', [$this, 'maybeReplaceSingle'], 1);

        // Block themes never fire woocommerce_single_product_summary; the form is a block.
        add_filter('render_block_woocommerce/add-to-cart-form', [$this, 'filterAddToCartBlock'], 100, 3);
        add_filter('render_block_woocommerce/add-to-cart-with-options', [$this, 'filterAddToCartBlock'], 100, 3);

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
         * Add-ons (e.g. Shelfora Pro's scheduled windows) can force catalog
         * mode off, or leave the FREE decision untouched, by returning a
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

    /**
     * Block themes: replace the add-to-cart form block (simple, variable,
     * grouped and external alike) for catalog products.
     */
    public function filterAddToCartBlock(mixed $content, mixed $block = null, mixed $instance = null): mixed
    {
        $postId  = $instance instanceof \WP_Block ? ($instance->context['postId'] ?? 0) : 0;
        $product = wc_get_product($postId ?: get_the_ID());

        if (! $product instanceof \WC_Product || ! $this->applies() || ! $this->shouldHideAddToCart($product)) {
            return $content;
        }

        return self::ksesReplacement($this->addToCartReplacement($product, 'single'));
    }

    /**
     * Drop the offer (price, price specification, price range) from the product
     * JSON-LD when the price is hidden, so search engines cannot show it.
     */
    public function filterStructuredData(mixed $markup, mixed $product): mixed
    {
        if (is_array($markup) && $this->hidesPriceFor($product)) {
            unset($markup['offers']);
        }

        return $markup;
    }

    /**
     * Remove the raw prices a variable product hands to the variation form.
     */
    public function filterVariationData(mixed $data, mixed $product = null, mixed $variation = null): mixed
    {
        if (is_array($data) && $this->hidesPriceFor($variation)) {
            unset($data['display_price'], $data['display_regular_price']);
        }

        return $data;
    }

    /**
     * Blank the prices in Store API product responses (also used to hydrate
     * block shop and product pages) when the price is hidden.
     */
    public function filterStoreApiResponse(mixed $response, mixed $handler = null, mixed $request = null): mixed
    {
        if (
            ! $response instanceof \WP_REST_Response
            || ! $request instanceof \WP_REST_Request
            || ! preg_match('#^/wc/store(/v\d+)?/products#', $request->get_route())
        ) {
            return $response;
        }

        $data = $response->get_data();

        if (! is_array($data)) {
            return $response;
        }

        if (isset($data['prices'])) {
            $data = $this->blankStoreApiPrices($data);
        } else {
            $data = array_map(fn (mixed $item): mixed => is_array($item) ? $this->blankStoreApiPrices($item) : $item, $data);
        }

        $response->set_data($data);

        return $response;
    }

    /**
     * Blank the prices in the woocommerce/products interactivity state.
     */
    public function filterInteractivityData(mixed $data): mixed
    {
        if (! is_array($data) || ! isset($data['state']['woocommerce/products']) || ! is_array($data['state']['woocommerce/products'])) {
            return $data;
        }

        foreach (['products', 'productVariations'] as $key) {
            $items = $data['state']['woocommerce/products'][$key] ?? null;

            if (is_array($items)) {
                $data['state']['woocommerce/products'][$key] = array_map(
                    fn (mixed $item): mixed => is_array($item) ? $this->blankStoreApiPrices($item) : $item,
                    $items,
                );
            }
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function blankStoreApiPrices(array $item): array
    {
        if (! isset($item['id'], $item['prices']) || ! $this->hidesPriceFor(wc_get_product((int) $item['id']))) {
            return $item;
        }

        // The Store API builds prices as an object; keep whatever shape it used.
        $prices = (array) $item['prices'];
        foreach (['price', 'regular_price', 'sale_price'] as $key) {
            $prices[$key] = '';
        }
        $prices['price_range'] = null;

        $item['prices'] = is_object($item['prices']) ? (object) $prices : $prices;

        return $item;
    }

    private function hidesPriceFor(mixed $product): bool
    {
        return $product instanceof \WC_Product && $this->applies() && $this->shouldHidePrice($product);
    }

    public function renderSingleReplacement(): void
    {
        if ('' === $this->singleReplacement) {
            return;
        }

        // The old note called this trusted HTML, which nothing enforced: the value
        // comes from a public filter, so it is filtered, and scripts are dropped.
        echo self::ksesReplacement($this->singleReplacement); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered by wp_kses in ksesReplacement().
    }

    /**
     * Post HTML plus form fields. A replacement is often a form (the PRO quote
     * request), and wp_kses_post strips form, input, select, textarea and button,
     * which left that form as bare labels.
     */
    private static function ksesReplacement(string $html): string
    {
        $allowed = wp_kses_allowed_html('post');
        $common  = ['id' => true, 'class' => true, 'name' => true, 'value' => true, 'required' => true, 'disabled' => true, 'aria-label' => true, 'aria-describedby' => true];
        $allowed['form']     = ['id' => true, 'class' => true, 'action' => true, 'method' => true, 'novalidate' => true, 'data-*' => true];
        $allowed['input']    = $common + ['type' => true, 'min' => true, 'max' => true, 'step' => true, 'placeholder' => true, 'checked' => true, 'autocomplete' => true, 'data-*' => true];
        $allowed['textarea'] = $common + ['rows' => true, 'cols' => true, 'placeholder' => true];
        $allowed['select']   = $common + ['multiple' => true];
        $allowed['option']   = ['value' => true, 'selected' => true];
        $allowed['button']   = $common + ['type' => true, 'data-*' => true];
        $allowed['label']    = ['for' => true, 'class' => true, 'id' => true];
        $allowed['p']        = ($allowed['p'] ?? []) + ['role' => true, 'aria-live' => true, 'hidden' => true];

        return wp_kses($html, $allowed);
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
         * Filters a per-role CTA link shown when add-to-cart is hidden.
         *
         * Return `label` and `url` keys. Shelfora Pro uses this for per-role CTA
         * buttons on role pricing rows.
         *
         * @param array{label?: string, url?: string} $cta     CTA data.
         * @param \WC_Product                         $product Product being rendered.
         * @param string                              $context `single` or `loop`.
         */
        $cta = apply_filters('catalog/rule_cta', [], $product, $context);

        $html = $this->renderRuleCta(is_array($cta) ? $cta : []);

        /**
         * Filters HTML shown in place of add-to-cart when catalog mode hides the cart.
         *
         * @param string      $html    Replacement HTML. Empty leaves the slot blank.
         * @param \WC_Product $product Product being rendered.
         * @param string      $context `single` or `loop`.
         */
        $html = apply_filters('catalog/add_to_cart_replacement', $html, $product, $context);

        return is_string($html) ? $html : '';
    }

    /**
     * @param array{label?: string, url?: string} $cta
     */
    private function renderRuleCta(array $cta): string
    {
        $label = sanitize_text_field((string) ($cta['label'] ?? ''));
        $url   = esc_url((string) ($cta['url'] ?? ''));

        if ('' === $label || '' === $url) {
            return '';
        }

        return sprintf(
            '<p class="catalog-rule-cta"><a class="button catalog-rule-cta__link" href="%s">%s</a></p>',
            esc_url($url),
            esc_html($label),
        );
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
