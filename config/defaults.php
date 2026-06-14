<?php
/**
 * Default settings, merged under the option key `catalog_settings`.
 *
 * The plugin ships enabled in "all" scope hiding both the price and the
 * add-to-cart button, so activating it immediately turns the store into a
 * browsable catalog. The merchant then narrows the scope (all / selected
 * products / selected categories), chooses which elements to hide, restricts
 * catalog mode to specific roles (or exempts roles such as wholesale), and
 * optionally replaces add-to-cart with a custom call-to-action button.
 *
 * @package Catalog
 *
 * @return array<string, mixed>
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

return [
    // Master switch.
    'enabled' => true,

    // What to hide on catalog products.
    'hide_price'        => true,
    'hide_add_to_cart'  => true,

    // Scope: 'all' (every product), 'products' (only flagged products) or
    // 'categories' (only products in flagged categories).
    'scope' => 'all',

    // Role handling.
    //   'everyone'     : catalog mode applies to all visitors.
    //   'guests'       : only logged-out visitors (e.g. show prices to members).
    //   'roles'        : only the roles listed in role_list.
    //   'except_roles' : everyone except the roles listed in role_list.
    'role_mode' => 'everyone',

    // Role slugs the role_mode applies to (used for 'roles' / 'except_roles').
    'role_list' => [],

    // Optional replacement call-to-action shown instead of add-to-cart.
    'cta_enabled' => false,
    'cta_text'    => '',
    'cta_url'     => '',
    // Open the CTA link in a new browser tab.
    'cta_new_tab' => false,

    // Where catalog behaviour renders.
    'apply_on_single' => true,
    'apply_on_loop'   => true,

    // Optional notice shown in place of the price (e.g. "Contact us for pricing").
    'price_notice' => '',
];
