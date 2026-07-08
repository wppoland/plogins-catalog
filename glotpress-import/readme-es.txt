=== Plogins Catalog - Catalog Mode for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, catalog mode, hide price, hide add to cart, request a quote
Requires at least: 6.5
Tested up to: 7.0
Requires PHP: 8.1
Requiere complementos: woocommerce
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Convierta su tienda en un catálogo: oculte el precio, el botón de añadir al carrito, o ambos, en toda la tienda o solo para roles de visitantes seleccionados.

== Description ==

El catálogo convierte su tienda WooCommerce en un catálogo navegable. tu eliges
si se debe ocultar el precio, el botón de añadir al carrito o ambos, y si eso
se aplica a todos los visitantes o sólo a algunos de ellos. Una configuración común es mostrar precios.
a clientes mayoristas conectados mientras los oculta de todos los demás.

Ocultar Añadir al carrito hace más que eliminar el botón. Los productos del catálogo también son
marcado como no adquirible, por lo que una URL `?add-to-cart=` adivinada o una solicitud de API de la tienda
no enviará un producto oculto al proceso de pago.

Los usos típicos son tiendas mayoristas y B2B, flujos de trabajo de "solicitar cotización",
precios exclusivos para miembros y sitios de exposición que muestran productos sin tomar
pedidos en línea.

El complemento es de código abierto. El código fuente y los informes de errores están disponibles en GitHub en
https://github.com/wppoland/plogins-catalog.

= Documentation and links =

* <strong>Documentación</strong> - https://plogins.com/es/plogins-catalog/docs/
* <strong>Página de complementos</strong> - https://plogins.com/es/plogins-catalog/
* <strong>Código fuente</strong> - https://github.com/wppoland/plogins-catalog
* <strong>Informes de errores y solicitudes de funciones</strong> - https://github.com/wppoland/plogins-catalog/issues


= Features =

* Ocultar el precio, el botón de añadir al carrito o ambos.
* Cuatro reglas para visitantes: todos, solo visitantes desconectados, solo roles seleccionados o todos excepto roles seleccionados (para que los clientes mayoristas conserven los precios y realicen el pago).
* Aviso de precio opcional que se muestra en lugar del precio, como "Contáctenos para conocer el precio".
* Se aplica en páginas de un solo producto y en listados de tiendas, categorías y etiquetas.
* Marca los productos ocultos como no adquiribles, por lo que no se pueden comprar a través de las URL directas del carrito o la API de la tienda.
* Pantalla de configuración creada con estilos de administración estándar de WordPress, incluido el modo oscuro.
* Se envía con un archivo POT para traducir y elimina su opción de desinstalación.
* Declara compatibilidad con HPOS y bloques de carrito/pago.

== Installation ==

1. Cargue el complemento en `/wp-content/plugins/catalog`, o instálelo a través de Complementos → Añadir nuevo.
2. Actívalo. WooCommerce debe estar instalado y activo.
3. Vaya a <strong>WooCommerce → Catálogo</strong> y elija qué ocultar y la regla de visitante.

== Frequently Asked Questions ==

= Does it require WooCommerce? =

Sí. WooCommerce debe estar instalado y activo.

= Can I show prices only to logged-in or wholesale customers? =

Sí. Establezca la regla de visitante en "Todos excepto los roles seleccionados" y marque los roles
que aún debería ver los precios y comprar (por ejemplo, su rol mayorista), o usar "Solo
visitantes desconectados" para mostrar los precios a cualquier cliente conectado.

= Does it stop people buying via a direct URL? =

Sí. Los productos en modo catálogo con añadir al carrito oculto están marcados
no se pueden comprar, por lo que las URL directas del carrito y la API REST también están bloqueadas.

= Does catalog mode hide products from shop archives? =

No. Los productos permanecen visibles en los listados a menos que su tema los oculte; El catálogo controla la visibilidad de los precios y la capacidad de compra.

= Can wholesale customers still see prices? =

Sí. Utilice la regla de visitante para ocultar los precios a los invitados y al mismo tiempo permitir que los roles seleccionados vean los precios y compren.


= Does this plugin work on WordPress Multisite? =

Sí. Este complemento es compatible con WordPress Multisite. Activarlo en red o activarlo en sitios individuales; Cada sitio mantiene su propia configuración y datos.

== Screenshots ==

1. La pantalla de configuración del catálogo en WooCommerce.
2. Una página de producto con el precio y el complemento al carrito ocultos.
3. Listado de tiendas en modo catálogo.

== External Services ==

El catálogo no se conecta a ningún servicio externo. El precio y la visibilidad de los complementos se deciden en su propio servidor a partir del rol del visitante actual, y sus opciones se mantienen en una única opción `catalog_settings` en tu base de datos de WordPress (más un marcador `catalog_db_version` para actualizaciones), ambos eliminados al desinstalar. El complemento no envía datos a ninguna parte y solo carga sus propias hojas de estilo incluidas con el complemento.

== Changelog ==

= 1.0.1 =
* Primera versión estable.

= 0.1.4 =
* Renombrado a Plogins Catalog para WooCommerce para obtener un nombre de complemento más distintivo.

= 0.1.3 =
* Filtro `catalog/rule_cta` para enlaces de CTA por función que se muestran antes de los filtros de reemplazo para añadir al carrito.

= 0.1.2 =
* Filtro `catalog/add_to_cart_replacement` para formularios de cotización o botones de CTA cuando añadir al carrito está oculto.

= 0.1.1 =
* La extensión filtra `catalog/hide_price`, `catalog/hide_add_to_cart` y `catalog/price_notice` para reglas de visibilidad PRO por rol.

= 0.1.0 =
* Lanzamiento inicial: ocultar el precio y/o añadir al carrito en toda la tienda o por rol de visitante, aviso de precio opcional, soporte único y de listado, y aplicación de no comprable.
