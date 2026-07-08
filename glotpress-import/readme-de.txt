=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Erfordert Plugins: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Verwandeln Sie dein Geschäft in einen Katalog: Blende den Preis, die Schaltfläche „In den Warenkorb“ oder beides aus, im gesamten Geschäft oder nur für ausgewählte Besucherrollen.

== Description ==

Der Katalog verwandelt deinen WooCommerce-Shop in einen durchsuchbaren Katalog. Sie wählen
ob der Preis, die Schaltfläche „In den Warenkorb“ oder beides ausgeblendet werden sollen und ob das so ist
gilt für jeden Besucher oder nur für einige von ihnen. Eine gängige Einrichtung ist die Anzeige von Preisen
an angemeldete Großhandelskunden, während sie vor allen anderen verborgen bleiben.

Das Ausblenden der Funktion „Zum Warenkorb hinzufügen“ bewirkt mehr als nur das Entfernen der Schaltfläche. Katalogprodukte sind ebenfalls vorhanden
als nicht käuflich markiert, also eine vermutete „?add-to-cart=“-URL oder eine Store-API-Anfrage
wird ein verstecktes Produkt nicht zur Kasse weiterleiten.

Typische Anwendungen sind Großhandels- und B2B-Shops, „Angebot anfordern“-Workflows,
Preise gelten nur für Mitglieder und Showroom-Websites, auf denen Produkte angezeigt werden, ohne sie zu kaufen
Bestellungen online.

Das Plugin ist Open Source. Quellcode und Fehlerberichte live auf GitHub unter
https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-catalog/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-catalog/
* <strong>Quellcode</strong> – https://github.com/wppoland/plogins-catalog
* <strong>Fehlerberichte und Funktionsanfragen</strong> – https://github.com/wppoland/plogins-catalog/issues


= Features =

* Blende den Preis, die Schaltfläche „In den Warenkorb“ oder beides aus.
* Vier Besucherregeln: alle, nur abgemeldete Besucher, nur ausgewählte Rollen oder alle außer ausgewählten Rollen (damit Großhandelskunden Preise und Kasse behalten).
* Optionaler Preishinweis, der anstelle des Preises angezeigt wird, z. B. „Kontaktiere uns für Preise“.
* Gilt für einzelne Produktseiten sowie für Shop-, Kategorie- und Tag-Einträge.
* Markiert versteckte Produkte als nicht käuflich, sodass sie nicht über direkte Warenkorb-URLs oder die Store-API gekauft werden können.
* Einstellungsbildschirm mit Standard-WordPress-Administratorstilen, einschließlich Dunkelmodus.
* Wird mit einer POT-Datei zur Übersetzung geliefert und entfernt deren Option bei der Deinstallation.
* Erklärt die Kompatibilität von HPOS und Warenkorb-/Checkout-Blöcken.

== Installation ==

1. Lade das Plugin nach „/wp-content/plugins/catalog“ hoch oder installiere es über Plugins → Neu hinzufügen.
2. Aktiviere es. WooCommerce muss installiert und aktiv sein.
3. Gehe zu <strong>WooCommerce → Katalog</strong> und wähle aus, was ausgeblendet werden soll, sowie die Besucherregel.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein.

= Can I show prices only to logged-in or wholesale customers? =

Ja. Setze die Besucherregel auf „Jeder außer ausgewählten Rollen“ und kreuze die Rollen an
Das sollte weiterhin Preise sehen und kaufen (z. B. deine Großhandelsrolle), oder verwende „Nur“.
„Abgemeldete Besucher“, um jedem eingeloggten Kunden Preise anzuzeigen.

= Does it stop people buying via a direct URL? =

Ja. Produkte im Katalogmodus mit ausgeblendeter Add-to-Cart-Funktion werden markiert
nicht käuflich, daher sind auch direkte Warenkorb-URLs und die REST-API blockiert.

= Does catalog mode hide products from shop archives? =

Nein. Produkte bleiben in den Einträgen sichtbar, es sei denn, dein Theme verbirgt sie; Der Katalog kontrolliert die Preistransparenz und die Kaufbarkeit.

= Can wholesale customers still see prices? =

Ja. Verwende die Besucherregel, um Preise vor Gästen zu verbergen und gleichzeitig ausgewählten Rollen die Möglichkeit zu geben, Preise zu sehen und zu kaufen.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es im Netzwerk oder auf einzelnen Websites. Jede Site behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Der Bildschirm „Katalogeinstellungen“ unter WooCommerce.
2. Eine Produktseite mit ausgeblendetem Preis und Add-to-Cart.
3. Shop-Eintrag im Katalogmodus.

== External Services ==

Der Katalog stellt keine Verbindung zu externen Diensten her. Preis und Sichtbarkeit in den Warenkorb werden auf deinem eigenen Server anhand der Rolle des aktuellen Besuchers festgelegt, und deine Auswahl wird in einer einzigen „catalog_settings“-Option in deiner WordPress-Datenbank gespeichert (plus einer „catalog_db_version“-Markierung für Upgrades), beide werden bei der Deinstallation entfernt. Das Plugin sendet nirgendwo Daten und lädt nur seine eigenen Stylesheets, die mit dem Plugin gebündelt sind.

== Changelog ==

= 1.0.1 =
* Erste stabile Version.

= 0.1.4 =
* Für einen eindeutigeren Plugin-Namen in „Plogins-Katalog für WooCommerce“ umbenannt.

= 0.1.3 =
* „catalog/rule_cta“-Filter für CTA-Links pro Rolle, die vor den Add-to-Cart-Ersatzfiltern angezeigt werden.

= 0.1.2 =
* „catalog/add_to_cart_replacement“-Filter für Angebotsformulare oder CTA-Schaltflächen, wenn „In den Warenkorb“ ausgeblendet ist.

= 0.1.1 =
* Erweiterungsfilter „catalog/hide_price“, „catalog/hide_add_to_cart“ und „catalog/price_notice“ für PRO-Sichtbarkeitsregeln pro Rolle.

= 0.1.0 =
* Erstveröffentlichung: Ausblenden des Preises und/oder der Add-to-Cart-Funktion im gesamten Geschäft oder nach Besucherrolle, optionale Preisbenachrichtigung, Einzel- und Auflistungsunterstützung sowie Durchsetzung nicht käuflicher Inhalte.
