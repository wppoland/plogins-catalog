# Catalog - Catalog Mode for WooCommerce

Turn your WooCommerce store into a browsable catalog. Catalog hides the price, the add-to-cart button, or both — store-wide, for selected products or categories, or only for certain visitors (for example, show prices only to logged-in wholesale customers). You can optionally replace add-to-cart with a custom call-to-action button.

## Features

- Hide the price and/or the add-to-cart button.
- Scope it to all products, selected products, or selected categories.
- Per-product and per-category overrides (force on / force off).
- Role rules: everyone, only guests, only selected roles, or everyone except selected roles.
- Optional price notice (e.g. "Contact us for pricing").
- Optional replacement call-to-action button with custom text and link.
- Works on single product pages and shop, category and tag listings.
- Marks catalog products non-purchasable, blocking direct cart URLs and REST.

## Installation

1. Upload the plugin to `/wp-content/plugins/catalog`, or install it via **Plugins → Add New**.
2. Activate it. WooCommerce must be active.
3. Go to **WooCommerce → Catalog** to choose what to hide and for whom.

## Frequently Asked Questions

**Can I show prices only to logged-in customers?**
Yes. The role rules let you hide prices and add-to-cart for guests while keeping them visible for selected roles.

**Does it stop people adding hidden products to the cart directly?**
Yes. Products in catalog mode are marked non-purchasable, so direct cart URLs and REST requests are blocked too.

Built by WPPoland — https://plogins.com

License: GPL-2.0-or-later
