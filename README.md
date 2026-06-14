# Catalog - Catalog Mode for WooCommerce

Turn your WooCommerce store into a browsable catalog. Hide the price, the
add-to-cart button, or both — store-wide, for selected products or categories, or
only for certain visitors (e.g. show prices only to logged-in wholesale
customers). Optionally replace add-to-cart with a custom call-to-action button.

Self-contained: no runtime Composer dependencies.

## Features

- Hide the price and/or the add-to-cart button.
- Scope: all products, selected products, or selected categories.
- Per-product and per-category overrides (force on / force off).
- Role rules: everyone, only guests, only selected roles, or everyone except selected roles.
- Optional price notice (e.g. "Contact us for pricing").
- Optional replacement call-to-action button (custom text + link).
- Works on single product pages and shop/category/tag listings.
- Marks catalog products non-purchasable (blocks direct cart URLs / REST).

## Architecture

- **Bootstrap** (`catalog.php`): PHP/WC guards, HPOS + cart-blocks compat, boots on
  `init` priority 0 and fires `do_action('catalog/booted')` from `Plugin::boot()`
  (the hook the PRO companion extends).
- **Autoload** (`autoload.php`): Composer vendor autoloader + PSR-4 fallback.
- **DI**: `src/Plugin.php` singleton + `src/Container.php`; services in
  `config/services.php`, boot order in `config/hooks.php`, defaults in
  `config/defaults.php`; `src/Migrator.php`.
- **Storefront**: `src/Service/CatalogMode.php` decides per-product/per-user
  whether catalog mode applies and hides price / add-to-cart / renders the CTA.
- **Admin**: `src/Admin/Settings.php` (WooCommerce → Catalog),
  `src/Admin/ProductData.php` (per-product), `src/Admin/CategoryData.php` (per-category).

## Development

```bash
composer install
composer cs        # WordPress Coding Standards
composer analyse   # PHPStan level 6
```

## PRO companion

`catalog-pro` (private, Freemius) hooks `add_action('catalog/booted', …)` and adds
scheduled catalog windows.
