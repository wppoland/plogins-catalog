<?php

/**
 * Catalog mode must hold on every path a price or a cart form leaves by, and the
 * role list a shop manager saves must not be narrowed to the roles they may edit.
 *
 * 1.1.2 only unhooked the classic add-to-cart action (block themes never fire
 * it), only filtered the price HTML (JSON-LD, variation data and the Store API
 * kept the raw price) and validated roles against get_editable_roles(), which
 * WooCommerce limits to Customer for shop managers.
 *
 * Run: php tests/catalog-mode-check.php
 */

declare(strict_types=1);

namespace {
    define('ABSPATH', __DIR__);
    define('CATALOG_DIR', __DIR__ . '/../');

    $GLOBALS['options'] = ['catalog_settings' => ['enabled' => true, 'hide_price' => true, 'hide_add_to_cart' => true, 'role_mode' => 'everyone']];

    function get_option(string $k, mixed $d = false): mixed { return $GLOBALS['options'][$k] ?? $d; }
    // A per-product rule (as a category rule would be) hides add to cart on product 7
    // only, and a partner plugin fills the slot.
    function apply_filters(string $h, mixed $v, mixed ...$a): mixed
    {
        if ('catalog/hide_add_to_cart' === $h && ! empty($GLOBALS['product_rule'])) {
            return 7 === $a[0]->id;
        }
        if ('catalog/add_to_cart_replacement' === $h && ! empty($GLOBALS['product_rule'])) {
            return '<a class="button">Add to quote</a>';
        }
        return $v;
    }
    function add_filter(mixed ...$a): void {}
    function add_action(mixed ...$a): void { $GLOBALS['actions'][] = $a[0]; }
    function remove_action(mixed ...$a): bool { return true; }
    function has_action(string $h, mixed $cb): bool { return ! empty($GLOBALS['classic_form']); }
    function sanitize_key(string $k): string { return strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $k)); }
    function sanitize_text_field(string $s): string { return trim($s); }
    function esc_url(string $s): string { return $s; }
    function esc_html(string $s): string { return $s; }
    function wp_kses_post(string $s): string { return $s; }
    function wp_kses(string $s, array $a): string { return $s; }
    function wp_kses_allowed_html(string $c): array { return ["p" => []]; }
    function is_user_logged_in(): bool { return false; }
    function get_the_ID(): int { return 7; }
    function translate_user_role(string $n): string { return $n; }
    // What WooCommerce hands a shop manager: Customer only.
    function get_editable_roles(): array { return ['customer' => ['name' => 'Customer']]; }
    function wp_roles(): object
    {
        return new class {
            public function get_names(): array { return ['administrator' => 'Administrator', 'editor' => 'Editor', 'customer' => 'Customer']; }
        };
    }

    class WC_Product
    {
        public function __construct(public int $id = 7, public int $parent = 0) {}
        public function is_type(string $t): bool { return 'variation' === $t && $this->parent > 0; }
        public function get_parent_id(): int { return $this->parent; }
    }
    function wc_get_product(mixed $id): WC_Product|false { return $id ? new WC_Product((int) $id) : false; }

    class WP_Block { public array $context = ['postId' => 7]; }
    class WP_REST_Request { public function __construct(private string $r) {} public function get_route(): string { return $this->r; } }
    class WP_REST_Response
    {
        public function __construct(private mixed $d) {}
        public function get_data(): mixed { return $this->d; }
        public function set_data(mixed $d): void { $this->d = $d; }
    }
}

namespace Catalog\Contract {
    interface HasHooks { public function registerHooks(): void; }
}

namespace {
    require __DIR__ . '/../src/Service/Settings.php';
    require __DIR__ . '/../src/Service/CatalogMode.php';

    $fail = 0;
    $check = static function (bool $ok, string $what) use (&$fail): void {
        echo ($ok ? 'ok   ' : 'FAIL ') . $what . "\n";
        $fail += $ok ? 0 : 1;
    };

    $mode = new Catalog\Service\CatalogMode(new Catalog\Service\Settings());

    $check(
        method_exists($mode, 'filterAddToCartBlock')
            && '' === $mode->filterAddToCartBlock('<form class="variations_form"></form>', [], new WP_Block()),
        'block add-to-cart form is replaced on block themes',
    );

    $ld = ['@type' => 'Product', 'name' => 'X', 'offers' => [['price' => '49.99']]];
    $check(
        method_exists($mode, 'filterStructuredData') && ! isset($mode->filterStructuredData($ld, new WC_Product())['offers']),
        'JSON-LD loses the offer price',
    );

    $var = ['display_price' => 19, 'display_regular_price' => 19, 'price_html' => ''];
    $out = method_exists($mode, 'filterVariationData') ? $mode->filterVariationData($var, new WC_Product(), new WC_Product(8)) : $var;
    $check(! isset($out['display_price']) && ! isset($out['display_regular_price']), 'variation data loses display prices');

    $api = new WP_REST_Response([['id' => 7, 'prices' => ['price' => '4999', 'regular_price' => '4999', 'sale_price' => '4999', 'price_range' => null]]]);
    if (method_exists($mode, 'filterStoreApiResponse')) {
        $mode->filterStoreApiResponse($api, null, new WP_REST_Request('/wc/store/v1/products'));
    }
    $check('' === $api->get_data()[0]['prices']['price'], 'Store API collection loses the price');

    // The real Store API hands prices over as an object, not an array.
    $one = new WP_REST_Response(['id' => 7, 'prices' => (object) ['price' => '4999']]);
    if (method_exists($mode, 'filterStoreApiResponse')) {
        $mode->filterStoreApiResponse($one, null, new WP_REST_Request('/wc/store/v1/products/7'));
    }
    $check('' === $one->get_data()['prices']->price, 'Store API single product loses the price');

    $other = new WP_REST_Response(['id' => 7, 'prices' => ['price' => '4999']]);
    if (method_exists($mode, 'filterStoreApiResponse')) {
        $mode->filterStoreApiResponse($other, null, new WP_REST_Request('/wc/store/v1/cart'));
    }
    $check('4999' === $other->get_data()['prices']['price'], 'non-product routes are untouched');

    $state = ['state' => ['woocommerce/products' => ['products' => [7 => ['id' => 7, 'prices' => (object) ['price' => '4999']]]]]];
    $state = method_exists($mode, 'filterInteractivityData') ? $mode->filterInteractivityData($state) : $state;
    $check('' === $state['state']['woocommerce/products']['products'][7]['prices']->price, 'block page interactivity state loses the price');

    $GLOBALS['options']['catalog_settings']['hide_price'] = false;
    $shown = new Catalog\Service\CatalogMode(new Catalog\Service\Settings());
    $check(method_exists($shown, 'filterStructuredData') && isset($shown->filterStructuredData($ld, new WC_Product())['offers']), 'JSON-LD keeps the offer when the price is shown');

    // Classic templates get the replacement once, at the summary hook. Block
    // templates have unhooked the classic form and get it from the block filter
    // only, so the summary hook must not render it a second time.
    $GLOBALS['product_rule'] = true;
    $GLOBALS['product']      = new WC_Product(7);
    foreach ([true => 1, false => 0] as $classic => $want) {
        $GLOBALS['actions']      = [];
        $GLOBALS['classic_form'] = $classic;
        $shown->maybeReplaceSingle();
        $check($want === count($GLOBALS['actions']), ($classic ? 'classic' : 'block') . " template: summary replacement hooked $want time(s)");
    }

    // A variation is judged as its parent: a rule on product 7 also stops its variation 8.
    $GLOBALS['product_rule'] = true;
    $check(false === $shown->filterPurchasable(true, new WC_Product(8, 7)), 'variation of a catalog product is not purchasable');
    $check(true === $shown->filterPurchasable(true, new WC_Product(9, 3)), 'variation of another product stays purchasable');
    $GLOBALS['product_rule'] = false;

    // Admin settings: a shop manager's save keeps every selected role.
    $src = file_get_contents(__DIR__ . '/../src/Admin/Settings.php');
    $src = preg_replace('/^<\?php\s+declare\(strict_types=1\);/', '', $src);
    $src = str_replace(['namespace Catalog\Admin;', 'use Catalog\Contract\HasHooks;', 'use Catalog\Service\Settings as SettingsStore;', 'implements HasHooks'], ['', '', '', ''], $src);
    $src = str_replace(['final class Settings', 'SettingsStore::OPTION'], ['final class AdminSettings', "'catalog_settings'"], $src);
    eval($src);
    $saved = (new AdminSettings())->sanitize(['role_mode' => 'except_roles', 'role_list' => ['administrator', 'customer', 'editor', 'bogus']]);
    sort($saved['role_list']);
    $check(['administrator', 'customer', 'editor'] === $saved['role_list'], 'shop manager save keeps administrator, customer, editor and drops unknown roles');

    exit($fail > 0 ? 1 : 0);
}
