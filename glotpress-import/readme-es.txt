=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requiere plugins: woocommerce
Stable tag: 1.0.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Convierte tu tienda en un catálogo: oculta el precio, el botón de añadir al carrito o ambos, en toda la tienda o solo para roles de visitante seleccionados.

== Description ==

Catalog convierte tu tienda WooCommerce en un catálogo navegable. Tú decides si ocultar el precio, el botón de añadir al carrito o ambos, y si eso se aplica a todos los visitantes o solo a algunos. Una configuración habitual es mostrar precios a clientes mayoristas conectados y ocultarlos al resto.

Ocultar «añadir al carrito» hace más que quitar el botón. Los productos en modo catálogo también se marcan como no comprables, así que una URL adivinada `?add-to-cart=` o una petición a la Store API no llevará un producto oculto hasta el pago.

Los usos habituales son tiendas mayoristas y B2B, flujos de «solicitar presupuesto», precios solo para miembros y sitios showroom que muestran productos sin aceptar pedidos online.

El plugin es de código abierto. El código fuente y los informes de errores están en GitHub: https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-catalog/docs/
* <strong>Página del plugin</strong> - https://plogins.com/es/plogins-catalog/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-catalog
* <strong>Informes de errores y peticiones de funciones</strong> - https://github.com/wppoland/plogins-catalog/issues


= Features =

* Oculta el precio, el botón de añadir al carrito o ambos.
* Cuatro reglas de visitante: todos, solo visitantes desconectados, solo roles seleccionados o todos excepto roles seleccionados (para que los clientes mayoristas conserven precios y pago).
* Aviso de precio opcional mostrado en lugar del precio, como «Contáctanos para conocer el precio».
* Se aplica en páginas de producto individual y en listados de tienda, categoría y etiqueta.
* Marca los productos ocultos como no comprables, así que no se pueden comprar mediante URL directas del carrito ni la Store API.
* Pantalla de ajustes con estilos estándar de administración de WordPress, incluido el modo oscuro.
* Incluye un archivo POT para traducir y elimina su opción al desinstalar.
* Declara compatibilidad con HPOS y con los bloques de carrito/pago.

== Installation ==

1. Sube el plugin a `/wp-content/plugins/catalog` o instálalo desde Plugins → Añadir nuevo.
2. Actívalo. WooCommerce debe estar instalado y activo.
3. Ve a <strong>WooCommerce → Catalog</strong> y elige qué ocultar y la regla de visitante.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo.

= Can I show prices only to logged-in or wholesale customers? =

Sí. Define la regla de visitante en «Todos excepto roles seleccionados» y marca los roles que aún deben ver precios y comprar (por ejemplo, tu rol mayorista), o usa «Solo visitantes desconectados» para mostrar precios a cualquier cliente conectado.

= Does it stop people buying via a direct URL? =

Sí. Los productos en modo catálogo con «añadir al carrito» oculto se marcan como no comprables, así que las URL directas del carrito y la REST API también quedan bloqueadas.

= Does catalog mode hide products from shop archives? =

No. Los productos siguen visibles en los listados, salvo que tu tema los oculte; Catalog controla la visibilidad del precio y la posibilidad de compra.

= Can wholesale customers still see prices? =

Sí. Usa la regla de visitante para ocultar precios a los invitados y, al mismo tiempo, permitir que los roles seleccionados vean precios y compren.


= Does this plugin work on WordPress Multisite? =

Sí. Este plugin es compatible con WordPress Multisite. Actívalo en red o en sitios concretos; cada sitio conserva sus propios ajustes y datos.

== Screenshots ==

1. La pantalla de ajustes de Catalog en WooCommerce.
2. Una página de producto con el precio y el botón de añadir al carrito ocultos.
3. Listado de tienda en modo catálogo.

== External Services ==

Catalog no se conecta a ningún servicio externo. La visibilidad del precio y de «añadir al carrito» se decide en tu propio servidor según el rol del visitante actual, y tus elecciones se guardan en una sola opción `catalog_settings` en tu base de datos de WordPress (más un marcador `catalog_db_version` para actualizaciones), ambos eliminados al desinstalar. El plugin no envía datos a ningún sitio y solo carga sus propias hojas de estilo incluidas con el plugin.

== Translations ==

Plogins Catalog incluye traducciones al polaco, al alemán y al español para la interfaz del plugin. El dominio de texto es `plogins-catalog`, así que los paquetes de idioma de WordPress.org también pueden sustituir o ampliar estas traducciones incluidas.

== Changelog ==

= 1.0.2 =
* Añadidas traducciones incluidas al polaco, al alemán y al español para la interfaz del plugin.

= 1.0.1 =
* Primera versión estable.

= 0.1.4 =
* Renombrado a Plogins Catalog para WooCommerce, para un nombre de plugin más distintivo.

= 0.1.3 =
* Filtro `catalog/rule_cta` para enlaces CTA por rol mostrados antes de los filtros de sustitución de añadir al carrito.

= 0.1.2 =
* Filtro `catalog/add_to_cart_replacement` para formularios de presupuesto o botones CTA cuando «añadir al carrito» está oculto.

= 0.1.1 =
* Filtros de extensión `catalog/hide_price`, `catalog/hide_add_to_cart` y `catalog/price_notice` para reglas de visibilidad PRO por rol.

= 0.1.0 =
* Lanzamiento inicial: ocultar el precio y/o «añadir al carrito» en toda la tienda o por rol de visitante, aviso de precio opcional, soporte en páginas individuales y listados, y aplicación de no comprable.
