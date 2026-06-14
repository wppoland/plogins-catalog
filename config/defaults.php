<?php
/**
 * Default settings, merged under the option key `catalog_settings`.
 *
 * The plugin ships enabled and hiding both the price and the add-to-cart button
 * store-wide, so activating it immediately turns the store into a browsable
 * catalog. The merchant then chooses which elements to hide and (optionally)
 * restricts catalog mode to specific roles — for example, exempting a wholesale
 * role so those customers still see prices and can buy.
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

    // Role handling.
    //   'everyone'     : catalog mode applies to all visitors.
    //   'guests'       : only logged-out visitors (e.g. show prices to members).
    //   'roles'        : only the roles listed in role_list.
    //   'except_roles' : everyone except the roles listed in role_list.
    'role_mode' => 'everyone',

    // Role slugs the role_mode applies to (used for 'roles' / 'except_roles').
    'role_list' => [],

    // Optional notice shown in place of the price (e.g. "Contact us for pricing").
    'price_notice' => '',
];
