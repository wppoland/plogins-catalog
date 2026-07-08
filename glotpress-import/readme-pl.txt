=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Wymaga wtyczek: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zamień swój sklep w katalog: ukryj cenę, przycisk „dodaj do koszyka” lub jedno i drugie, w całym sklepie lub tylko dla wybranych ról odwiedzających.

== Description ==

Katalog zmienia Twój sklep WooCommerce w katalog, który można przeglądać. Ty wybierasz
czy ukryć cenę, przycisk „dodaj do koszyka”, czy jedno i drugie, i czy to
dotyczy każdego odwiedzającego lub tylko części z nich. Typową konfiguracją jest pokazywanie cen
do zalogowanych klientów hurtowych, ukrywając ich przed wszystkimi innymi.

Ukrycie dodatku do koszyka powoduje więcej niż tylko usunięcie przycisku. Produkty katalogowe też są
oznaczony jako niemożliwy do zakupu, więc zgadnięty adres URL „?add-to-cart=” lub żądanie API Sklepu
nie przepuści ukrytego produktu do kasy.

Typowe zastosowania to sklepy hurtowe i B2B, przepływy pracy typu „zapytanie o wycenę”,
ceny tylko dla członków i witryny salonów, które wyświetlają produkty bez pobierania
zamówienia online.

Wtyczka jest oprogramowaniem typu open source. Kod źródłowy i raporty o błędach są dostępne na GitHubie pod adresem
https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Dokumentacja</strong> - https://plogins.com/pl/plogins-catalog/docs/
* <strong>Strona wtyczki</strong> - https://plogins.com/pl/plogins-catalog/
* <strong>Kod źródłowy</strong> - https://github.com/wppoland/plogins-catalog
* <strong>Raporty o błędach i prośby o nowe funkcje</strong> - https://github.com/wppoland/plogins-catalog/issues


= Features =

* Ukryj cenę, przycisk „dodaj do koszyka” lub jedno i drugie.
* Cztery zasady odwiedzających: wszyscy, tylko niezalogowani odwiedzający, tylko wybrane role lub wszyscy oprócz wybranych ról (aby klienci hurtowi zachowywali ceny i kasę).
* Opcjonalna informacja o cenie wyświetlana w miejscu ceny, np. „Skontaktuj się z nami w sprawie ceny”.
* Dotyczy stron pojedynczych produktów oraz list sklepów, kategorii i tagów.
* Oznacza ukryte produkty, których nie można kupić, więc nie można ich kupić za pośrednictwem bezpośrednich adresów URL koszyka lub interfejsu API sklepu.
* Ekran ustawień zbudowany ze standardowych stylów administracyjnych WordPress, w tym trybu ciemnego.
* Dostarczany z plikiem POT do tłumaczenia i usuwa tę opcję podczas dezinstalacji.
* Deklaruje kompatybilność HPOS i bloków koszyka/kasy.

== Installation ==

1. Prześlij wtyczkę do `/wp-content/plugins/catalog` lub zainstaluj poprzez Wtyczki → Dodaj nową.
2. Aktywuj. WooCommerce musi być zainstalowany i aktywny.
3. Przejdź do <strong>WooCommerce → Katalog</strong> i wybierz, co chcesz ukryć oraz regułę odwiedzania.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Tak. WooCommerce musi być zainstalowany i aktywny.

= Can I show prices only to logged-in or wholesale customers? =

Tak. Ustaw regułę gościa na „Wszyscy oprócz wybranych ról” i zaznacz role
który nadal powinien widzieć ceny i kupować (np. Twoja rola jako hurtownik) lub użyć opcji „Tylko
niezalogowani goście”, aby pokazać ceny każdemu zalogowanemu klientowi.

= Does it stop people buying via a direct URL? =

Tak. Produkty znajdujące się w trybie katalogowym z ukrytym dodatkiem do koszyka są zaznaczone
nie można kupić, więc bezpośrednie adresy URL koszyka i interfejs API REST również są blokowane.

= Does catalog mode hide products from shop archives? =

Nie. Produkty pozostają widoczne na listach, chyba że Twój motyw je ukrywa; Katalog kontroluje widoczność cen i możliwość zakupu.

= Can wholesale customers still see prices? =

Tak. Użyj reguły gościa, aby ukryć ceny przed gośćmi, jednocześnie pozwalając wybranym rolom zobaczyć ceny i kupować.


= Does this plugin work on WordPress Multisite? =

Tak. Ta wtyczka jest kompatybilna z WordPress Multisite. Aktywuj go w sieci lub aktywuj na poszczególnych stronach; każda witryna przechowuje własne ustawienia i dane.

== Screenshots ==

1. Ekran ustawień katalogu w WooCommerce.
2. Strona produktu z ukrytą ceną i dodatkiem do koszyka.
3. Lista sklepów w trybie katalogu.

== External Services ==

Katalog nie łączy się z żadnymi usługami zewnętrznymi. Cena i widoczność dodatków do koszyka są ustalane na Twoim własnym serwerze na podstawie roli bieżącego gościa, a Twoje wybory są przechowywane w pojedynczej opcji „catalog_settings” w Twojej bazie danych WordPress (plus znacznik „catalog_db_version” dla aktualizacji), oba usuwane podczas dezinstalacji. Wtyczka nie wysyła nigdzie danych i ładuje tylko własne arkusze stylów dołączone do wtyczki.

== Changelog ==

= 1.0.1 =
* Pierwsza stabilna wersja.

= 0.1.4 =
* Zmieniono nazwę na Katalog Plogin dla WooCommerce, aby uzyskać bardziej charakterystyczną nazwę wtyczki.

= 0.1.3 =
* Filtr „katalog/rule_cta” dla linków CTA dla poszczególnych ról wyświetlanych przed filtrami zastępczymi dodawanymi do koszyka.

= 0.1.2 =
* Filtr `katalog/add_to_cart_replacement` dla formularzy wyceny lub przycisków CTA, gdy dodatek do koszyka jest ukryty.

= 0.1.1 =
* Filtry rozszerzeń `katalog/ukryj_cena`, `katalog/ukryj_add_do_koszyka` i `katalog/powiadomienie o cenie` dla reguł widoczności PRO dla poszczególnych ról.

= 0.1.0 =
* Wersja pierwsza: ukryj cenę i/lub opcję dodawania do koszyka w całym sklepie lub według roli gościa, opcjonalne powiadomienie o cenie, obsługa pojedynczych produktów i aukcji oraz egzekwowanie zasad niezwiązanych z zakupem.
