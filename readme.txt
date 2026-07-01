=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.4
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
https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* **Documentation** - https://plogins.com/plogins-catalog/docs/
* **Plugin page** - https://plogins.com/plogins-catalog/
* **Source code** - https://github.com/wppoland/plogins-catalog
* **Bug reports and feature requests** - https://github.com/wppoland/plogins-catalog/issues


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


= Does this plugin work on WordPress Multisite? =

Yes. This plugin is compatible with WordPress Multisite. Network activate it or activate it on individual sites; each site keeps its own settings and data.

== Screenshots ==

1. The Catalog settings screen under WooCommerce.
2. A product page with the price and add-to-cart hidden.
3. Shop listing in catalog mode.

== External Services ==

Catalog does not connect to any external services. Price and add-to-cart visibility are decided on your own server from the current visitor's role, and your choices are kept in a single `catalog_settings` option in your WordPress database (plus a `catalog_db_version` marker for upgrades), both removed on uninstall. The plugin sends no data anywhere and loads only its own stylesheets bundled with the plugin.

== Changelog ==

= 0.1.4 =
* Renamed to Plogins Catalog for WooCommerce for a more distinctive plugin name.

= 0.1.3 =
* `catalog/rule_cta` filter for per-role CTA links shown before add-to-cart replacement filters.

= 0.1.2 =
* `catalog/add_to_cart_replacement` filter for quote forms or CTA buttons when add-to-cart is hidden.

= 0.1.1 =
* Extension filters `catalog/hide_price`, `catalog/hide_add_to_cart` and `catalog/price_notice` for PRO per-role visibility rules.

= 0.1.0 =
* Initial release: hide the price and/or add-to-cart store-wide or by visitor role, optional price notice, single and listing support, and non-purchasable enforcement.
