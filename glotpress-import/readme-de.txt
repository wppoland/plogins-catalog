=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Erfordert Plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Verwandle deinen Shop in einen Katalog: Blende den Preis, den Button „In den Warenkorb“ oder beides aus — shopweit oder nur für ausgewählte Besucherrollen.

== Description ==

Catalog verwandelt deinen WooCommerce-Shop in einen durchsuchbaren Katalog. Du entscheidest, ob der Preis, der Button „In den Warenkorb“ oder beides ausgeblendet wird und ob das für alle Besucher gilt oder nur für einige. Eine gängige Einrichtung ist, Preise eingeloggten Großhandelskunden zu zeigen und sie vor allen anderen zu verbergen.

Das Ausblenden von „In den Warenkorb“ ist mehr als das Entfernen des Buttons. Katalogprodukte werden auch als nicht käuflich markiert, sodass eine erratene `?add-to-cart=`-URL oder eine Store-API-Anfrage ein verstecktes Produkt nicht bis zur Kasse durchlässt.

Typische Einsätze sind Großhandels- und B2B-Shops, „Angebot anfordern“-Workflows, Mitgliederpreise und Showroom-Websites, die Produkte zeigen, ohne Bestellungen online anzunehmen.

Das Plugin ist Open Source. Quellcode und Fehlerberichte findest du auf GitHub unter https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Dokumentation</strong> - https://plogins.com/de/plogins-catalog/docs/
* <strong>Plugin-Seite</strong> - https://plogins.com/de/plogins-catalog/
* <strong>Quellcode</strong> - https://github.com/wppoland/plogins-catalog
* <strong>Fehlerberichte und Funktionswünsche</strong> - https://github.com/wppoland/plogins-catalog/issues


= Features =

* Blende den Preis, den Button „In den Warenkorb“ oder beides aus.
* Vier Besucherregeln: alle, nur abgemeldete Besucher, nur ausgewählte Rollen oder alle außer ausgewählten Rollen (damit Großhandelskunden Preise und Kasse behalten).
* Optionaler Preishinweis anstelle des Preises, z. B. „Kontaktiere uns für Preise“.
* Gilt auf einzelnen Produktseiten sowie in Shop-, Kategorie- und Tag-Listen.
* Markiert versteckte Produkte als nicht käuflich, sodass sie nicht über direkte Warenkorb-URLs oder die Store API gekauft werden können.
* Einstellungsbildschirm in Standard-WordPress-Admin-Stilen, inklusive Dark Mode.
* Wird mit einer POT-Datei zur Übersetzung geliefert und entfernt seine Option bei der Deinstallation.
* Deklariert Kompatibilität mit HPOS und den Warenkorb-/Kassen-Blöcken.

== Installation ==

1. Lade das Plugin nach `/wp-content/plugins/catalog` hoch oder installiere es über Plugins → Installieren.
2. Aktiviere es. WooCommerce muss installiert und aktiv sein.
3. Gehe zu <strong>WooCommerce → Catalog</strong> und wähle, was ausgeblendet werden soll, sowie die Besucherregel.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Ja. WooCommerce muss installiert und aktiv sein.

= Can I show prices only to logged-in or wholesale customers? =

Ja. Setze die Besucherregel auf „Alle außer ausgewählten Rollen“ und aktiviere die Rollen, die weiterhin Preise sehen und kaufen sollen (z. B. deine Großhandelsrolle), oder nutze „Nur abgemeldete Besucher“, um Preise jedem eingeloggten Kunden zu zeigen.

= Does it stop people buying via a direct URL? =

Ja. Produkte im Katalogmodus mit ausgeblendetem „In den Warenkorb“ werden als nicht käuflich markiert, sodass auch direkte Warenkorb-URLs und die REST API blockiert sind.

= Does catalog mode hide products from shop archives? =

Nein. Produkte bleiben in Listen sichtbar, es sei denn, dein Theme verbirgt sie; Catalog steuert Preissichtbarkeit und Kaufbarkeit.

= Can wholesale customers still see prices? =

Ja. Nutze die Besucherregel, um Preise vor Gästen zu verbergen und gleichzeitig ausgewählten Rollen Preise und Kauf zu erlauben.


= Does this plugin work on WordPress Multisite? =

Ja. Dieses Plugin ist mit WordPress Multisite kompatibel. Aktiviere es netzwerkweit oder auf einzelnen Websites; jede Website behält ihre eigenen Einstellungen und Daten.

== Screenshots ==

1. Der Catalog-Einstellungsbildschirm unter WooCommerce.
2. Eine Produktseite mit ausgeblendetem Preis und „In den Warenkorb“.
3. Shop-Liste im Katalogmodus.

== External Services ==

Catalog stellt keine Verbindung zu externen Diensten her. Preis- und „In den Warenkorb“-Sichtbarkeit werden auf deinem eigenen Server anhand der Rolle des aktuellen Besuchers entschieden, und deine Auswahl wird in einer einzigen Option `catalog_settings` in deiner WordPress-Datenbank gespeichert (plus ein `catalog_db_version`-Marker für Upgrades), beides wird bei der Deinstallation entfernt. Das Plugin sendet keine Daten irgendwohin und lädt nur seine eigenen, mit dem Plugin gebündelten Stylesheets.

== Translations ==

Plogins Catalog enthält polnische, deutsche und spanische Übersetzungen für die Plugin-Oberfläche. Die Textdomain ist `plogins-catalog`, sodass Sprachpakete von WordPress.org diese mitgelieferten Übersetzungen ebenfalls überschreiben oder erweitern können.

== Changelog ==

= 1.0.2 =
* Mitgelieferte polnische, deutsche und spanische Übersetzungen für die Plugin-Oberfläche hinzugefügt.

= 1.0.1 =
* Erste stabile Version.

= 0.1.4 =
* In Plogins Catalog für WooCommerce umbenannt, für einen eindeutigeren Plugin-Namen.

= 0.1.3 =
* Filter `catalog/rule_cta` für rollenspezifische CTA-Links, die vor den Add-to-Cart-Ersatzfiltern angezeigt werden.

= 0.1.2 =
* Filter `catalog/add_to_cart_replacement` für Angebotsformulare oder CTA-Buttons, wenn „In den Warenkorb“ ausgeblendet ist.

= 0.1.1 =
* Erweiterungsfilter `catalog/hide_price`, `catalog/hide_add_to_cart` und `catalog/price_notice` für PRO-Sichtbarkeitsregeln pro Rolle.

= 0.1.0 =
* Erstveröffentlichung: Preis und/oder „In den Warenkorb“ shopweit oder nach Besucherrolle ausblenden, optionaler Preishinweis, Unterstützung für Einzelseiten und Listen sowie Durchsetzung der Nicht-Käuflichkeit.
