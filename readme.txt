=== Catalog - Catalog Mode for WooCommerce ===
Contributors: wppoland
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requires Plugins: woocommerce
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Turn your store into a catalog: hide the price, the add-to-cart button, or both — store-wide or only for selected visitor roles.

== Description ==

Catalog turns your WooCommerce store into a browsable catalog. Hide the price,
the add-to-cart button, or both — across the whole store, or only for certain
visitors (for example, show prices only to logged-in wholesale customers). When
add-to-cart is hidden, products are also blocked from being purchased
server-side, so direct cart URLs and the REST API can't slip a sale through.

It is ideal for wholesale and B2B stores, "request a quote" workflows,
members-only pricing, showroom/look-book sites, and any store that wants to
display products without selling them online directly.

= Features =

* Hide the **price**, the **add-to-cart** button, or both.
* **Role rules**: apply to everyone, only logged-out visitors, only selected roles, or everyone except selected roles (e.g. show prices to wholesale customers only).
* Optional **price notice** shown where the price would be (e.g. "Contact us for pricing").
* Works on **single product pages** and **shop/category/tag listings**.
* Blocks server-side purchasing of catalog products (direct cart URLs, REST).
* Accessible, dark-mode-aware settings screen.
* Translation ready (POT included) and clean uninstall.
* HPOS and cart/checkout blocks compatible.

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

== Screenshots ==

1. The Catalog settings screen under WooCommerce.
2. A product page with the price and add-to-cart hidden.
3. Shop listing in catalog mode.

== Changelog ==

= 0.1.0 =
* Initial release: hide the price and/or add-to-cart store-wide or by visitor role, optional price notice, single and listing support, and non-purchasable enforcement.
