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

Turn your store into a catalog: hide prices and/or add-to-cart, store-wide or by product, category or role.

== Description ==

Catalog turns your WooCommerce store into a browsable catalog. Hide the price,
the add-to-cart button, or both — store-wide, for selected products, for whole
categories, or only for certain visitors (for example, show prices only to
logged-in wholesale customers). When add-to-cart is hidden you can replace it
with your own call-to-action button linking to a contact or enquiry page.

It is ideal for wholesale and B2B stores, "request a quote" workflows, members-only
pricing, showroom/look-book sites, and any store that wants to display products
without selling them online directly.

= Features =

* Hide the **price**, the **add-to-cart** button, or both.
* Scope catalog mode to **all products**, **selected products**, or **selected categories**.
* Per-product override in the product editor (force catalog mode on or off).
* Per-category override on the product category screen (force on, or exempt a category).
* **Role rules**: apply to everyone, only logged-out visitors, only selected roles, or everyone except selected roles (e.g. show prices to wholesale customers only).
* Optional **price notice** shown where the price would be (e.g. "Contact us for pricing").
* Optional **call-to-action button** with custom text and link in place of add-to-cart.
* Works on **single product pages** and **shop/category/tag listings**.
* Blocks server-side purchasing of catalog products (direct cart URLs, REST).
* Accessible, dark-mode-aware settings screen with inline help on every option.
* Translation ready (POT included) and clean uninstall.
* HPOS and cart/checkout blocks compatible.

== Installation ==

1. Upload the plugin to `/wp-content/plugins/catalog`, or install via Plugins → Add New.
2. Activate it. WooCommerce must be installed and active.
3. Go to **WooCommerce → Catalog** and choose what to hide, the scope, and the visitor rule.
4. (Optional) In "Selected products" mode, edit a product and set its **Catalog mode** field. In "Selected categories" mode, set the **Catalog mode** field on a product category.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Yes. WooCommerce must be installed and active.

= Can I show prices only to logged-in or wholesale customers? =

Yes. Set the visitor rule to "Everyone except selected roles" and tick the roles
that should still see prices and buy (e.g. your wholesale role), or use "Only
logged-out visitors" to show prices to any logged-in customer.

= Can I apply catalog mode to only some products or categories? =

Yes. Choose "Only selected products" and set each product's Catalog mode field,
or "Only selected categories" and set the field on each category. Per-product and
per-category overrides always win over the store-wide setting.

= Can I replace add-to-cart with a contact button? =

Yes. Enable the call-to-action option, set the button text and link (e.g. your
contact page), and it will appear wherever add-to-cart is hidden.

= Does it stop people buying via a direct URL? =

Yes. Products under catalog mode with add-to-cart hidden are marked
non-purchasable, so direct cart URLs and the REST API are blocked too.

== Screenshots ==

1. The Catalog settings screen under WooCommerce.
2. A product page with the price hidden and a call-to-action button.
3. Shop listing in catalog mode.
4. The per-product Catalog mode override in the product editor.

== Changelog ==

= 0.1.0 =
* Initial release: hide price and/or add-to-cart by scope (all / products / categories) and by role, optional price notice and replacement call-to-action, single and listing support, and non-purchasable enforcement.
