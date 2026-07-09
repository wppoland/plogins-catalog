=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Wymaga wtyczek: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zamień swój sklep w katalog: ukryj cenę, przycisk „Dodaj do koszyka” lub oba — w całym sklepie albo tylko dla wybranych ról odwiedzających.

== Description ==

Catalog zamienia Twój sklep WooCommerce w katalog do przeglądania. Ty decydujesz, czy ukryć cenę, przycisk „Dodaj do koszyka”, czy oba, oraz czy dotyczy to wszystkich odwiedzających, czy tylko części z nich. Typowa konfiguracja to pokazywanie cen zalogowanym klientom hurtowym i ukrywanie ich przed pozostałymi.

Ukrycie „Dodaj do koszyka” to coś więcej niż usunięcie przycisku. Produkty w trybie katalogowym są też oznaczone jako niedostępne do zakupu, więc zgadnięty adres URL `?add-to-cart=` ani żądanie Store API nie przepchnie ukrytego produktu do kasy.

Typowe zastosowania to sklepy hurtowe i B2B, przepływy „zapytaj o wycenę”, ceny tylko dla członków oraz witryny showroomowe, które prezentują produkty bez przyjmowania zamówień online.

Wtyczka jest open source. Kod źródłowy i zgłoszenia błędów znajdziesz na GitHubie: https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-catalog/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-catalog/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-catalog
* <strong>Zgłoszenia błędów i propozycje funkcji</strong> - https://github.com/wppoland/plogins-catalog/issues


= Features =

* Ukryj cenę, przycisk „Dodaj do koszyka” lub oba.
* Cztery reguły dla odwiedzających: wszyscy, tylko niezalogowani, tylko wybrane role albo wszyscy oprócz wybranych ról (aby klienci hurtowi zachowali ceny i kasę).
* Opcjonalna informacja o cenie wyświetlana zamiast ceny, np. „Skontaktuj się z nami w sprawie ceny”.
* Działa na stronach pojedynczych produktów oraz na listach sklepu, kategorii i tagów.
* Oznacza ukryte produkty jako niedostępne do zakupu, więc nie da się ich kupić przez bezpośrednie adresy URL koszyka ani Store API.
* Ekran ustawień zbudowany w standardowych stylach panelu WordPress, z obsługą trybu ciemnego.
* Dostarczany z plikiem POT do tłumaczenia; usuwa swoją opcję przy odinstalowaniu.
* Deklaruje zgodność z HPOS oraz blokami koszyka/kasy.

== Installation ==

1. Wgraj wtyczkę do `/wp-content/plugins/catalog` lub zainstaluj przez Wtyczki → Dodaj nową.
2. Włącz ją. WooCommerce musi być zainstalowane i aktywne.
3. Przejdź do <strong>WooCommerce → Catalog</strong> i wybierz, co ukryć, oraz regułę dla odwiedzających.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowane i aktywne.

= Can I show prices only to logged-in or wholesale customers? =

Tak. Ustaw regułę odwiedzających na „Wszyscy oprócz wybranych ról” i zaznacz role, które nadal mają widzieć ceny i kupować (np. Twoją rolę hurtową), albo użyj „Tylko niezalogowani”, aby pokazywać ceny każdemu zalogowanemu klientowi.

= Does it stop people buying via a direct URL? =

Tak. Produkty w trybie katalogowym z ukrytym „Dodaj do koszyka” są oznaczone jako niedostępne do zakupu, więc bezpośrednie adresy URL koszyka i REST API też są blokowane.

= Does catalog mode hide products from shop archives? =

Nie. Produkty pozostają widoczne na listach, chyba że Twój motyw je ukrywa; Catalog kontroluje widoczność cen i możliwość zakupu.

= Can wholesale customers still see prices? =

Tak. Użyj reguły odwiedzających, aby ukryć ceny przed gośćmi, a jednocześnie pozwolić wybranym rolom widzieć ceny i kupować.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest zgodna z WordPress Multisite. Włącz ją dla całej sieci lub w pojedynczych witrynach; każda witryna zachowuje własne ustawienia i dane.

== Screenshots ==

1. Ekran ustawień Catalog w WooCommerce.
2. Strona produktu z ukrytą ceną i przyciskiem „Dodaj do koszyka”.
3. Lista sklepu w trybie katalogowym.

== External Services ==

Catalog nie łączy się z żadną usługą zewnętrzną. Widoczność ceny i przycisku „Dodaj do koszyka” jest ustalana na Twoim serwerze na podstawie roli bieżącego odwiedzającego, a Twoje wybory są przechowywane w jednej opcji `catalog_settings` w bazie danych WordPress (plus znacznik `catalog_db_version` na potrzeby aktualizacji) — obie są usuwane przy odinstalowaniu. Wtyczka nie wysyła danych nigdzie poza witrynę i ładuje wyłącznie własne arkusze stylów dołączone do wtyczki.

== Translations ==

Plogins Catalog zawiera polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki. Domena tekstowa to `plogins-catalog`, więc pakiety językowe z WordPress.org mogą również nadpisać lub rozszerzyć te dołączone tłumaczenia.

== Changelog ==

= 1.0.2 =
* Dodano dołączone polskie, niemieckie i hiszpańskie tłumaczenia interfejsu wtyczki.

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.4 =
* Zmieniono nazwę na Plogins Catalog dla WooCommerce, aby nazwa wtyczki była bardziej charakterystyczna.

= 0.1.3 =
* Filtr `catalog/rule_cta` dla linków CTA per rola wyświetlanych przed filtrami zastępującymi „Dodaj do koszyka”.

= 0.1.2 =
* Filtr `catalog/add_to_cart_replacement` dla formularzy wyceny lub przycisków CTA, gdy „Dodaj do koszyka” jest ukryte.

= 0.1.1 =
* Filtry rozszerzeń `catalog/hide_price`, `catalog/hide_add_to_cart` i `catalog/price_notice` dla reguł widoczności PRO per rola.

= 0.1.0 =
* Pierwsze wydanie: ukrywanie ceny i/lub „Dodaj do koszyka” w całym sklepie lub według roli odwiedzającego, opcjonalna informacja o cenie, obsługa stron pojedynczych i list oraz wymuszanie niedostępności do zakupu.
