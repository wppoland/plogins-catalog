=== Catalog - Catalog Mode for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn your store into a catalog: hide the price, the add-to-cart button, or both, store-wide or only for selected visitor roles.

== Description ==

Catalog turns your WooCommerce store into a browsable catalog. You choose
whether to hide the price, the add-to-cart button, or both, and whether that
applies to every visitor or only some of them. A common setup is to show prices
to logged-in wholesale customers while hiding them from everyone else.

Hiding add-to-cart does more than remove the button. Catalog products are also
marked non-purchasable, so a guessed `?add-to-cart=` URL or a Store API request
won't push a hidden product through to checkout.

Typical uses are wholesale and B2B stores, "request a quote" workflows,
members-only pricing, and showroom sites that display products without taking
orders online.

The plugin is open source. Source code and bug reports live on GitHub at
https://github.com/wppoland/catalog.

= Features =

* Hide the price, the add-to-cart button, or both.
* Four visitor rules: everyone, logged-out visitors only, selected roles only, or everyone except selected roles (so wholesale customers keep prices and checkout).
* Optional price notice shown in place of the price, such as "Contact us for pricing".
* Applies on single product pages and on shop, category, and tag listings.
* Marks hidden products non-purchasable, so they can't be bought through direct cart URLs or the Store API.
* Settings screen built with standard WordPress admin styles, including dark mode.
* Ships with a POT file for translation and removes its option on uninstall.
* Declares HPOS and cart/checkout blocks compatibility.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/catalog`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Go to **WooCommerce → Catalog** and choose what to hide and the visitor rule.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Can I show prices only to logged-in or wholesale customers? =

Yes. Set the visitor rule to "Everyone except selected roles" and tick the roles
that should still see prices and buy (e.g. your wholesale role), or use "Only
logged-out visitors" to show prices to any logged-in customer.

= Does it stop people buying via a direct URL? =

Yes. Products under catalog mode with add-to-cart hidden are marked
non-purchasable, so direct cart URLs and the REST API are blocked too.

= Does catalog mode hide products from shop archives? =

No. Products remain visible in listings unless your theme hides them; Catalog controls price visibility and purchasability.

= Can wholesale customers still see prices? =

Yes. Use the visitor rule to hide prices from guests while allowing selected roles to see prices and buy.

== Screenshots ==

1. The Catalog settings screen under WooCommerce.
2. A product page with the price and add-to-cart hidden.
3. Shop listing in catalog mode.

== Changelog ==

= 0.1.2 =
* `catalog/add_to_cart_replacement` filter for quote forms or CTA buttons when add-to-cart is hidden.

= 0.1.1 =
* Extension filters `catalog/hide_price`, `catalog/hide_add_to_cart` and `catalog/price_notice` for PRO per-role visibility rules.

= 0.1.0 =
* Initial release: hide the price and/or add-to-cart store-wide or by visitor role, optional price notice, single and listing support, and non-purchasable enforcement.
