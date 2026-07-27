<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package plogins-catalog-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Catalog Pro',
    'url'        => 'https://plogins.com/plogins-catalog-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'price_pln'  => 129,
    'lead'       => [
        'en' => 'Catalog Pro is feature-complete: scheduled windows, per-role visibility, quote forms and per-rule CTA buttons.',
        'pl' => 'Catalog Pro jest feature-complete: harmonogram, widoczność per rola, formularz wyceny i CTA per reguła.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Per-role visibility', 'desc' => 'Override hide price, hide add-to-cart and the price notice per customer role, e.g. guest vs wholesale.'],
            'pl' => ['title' => 'Widoczność per rola', 'desc' => 'Nadpisz ukrywanie ceny, koszyka i komunikat zastępczy per rola klienta, np. gość vs hurtownik.'],
        ],
        [
            'en' => ['title' => 'Request a quote', 'desc' => 'A built-in quote form in place of add-to-cart, collecting products and quantities and emailing the request to your team.'],
            'pl' => ['title' => 'Zapytanie o wycenę', 'desc' => 'Wbudowany formularz wyceny zamiast koszyka, zbierający produkty i ilości i wysyłający zapytanie do zespołu.'],
        ],
        [
            'en' => ['title' => 'Scheduled catalog windows', 'desc' => 'Turn catalog mode on or off automatically on a schedule.'],
            'pl' => ['title' => 'Harmonogramy trybu katalogu', 'desc' => 'Automatyczne włączanie i wyłączanie trybu katalogu według harmonogramu.'],
        ],
        [
            'en' => ['title' => 'Per-rule CTA', 'desc' => 'Different call-to-action buttons per role, such as contact sales, apply for wholesale or request access.'],
            'pl' => ['title' => 'CTA per reguła', 'desc' => 'Różne przyciski wezwania do działania per rola, np. kontakt ze sprzedażą, formularz hurtowy albo prośba o dostęp.'],
        ],
    ],
];
