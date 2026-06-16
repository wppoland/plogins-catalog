<?php
// Base store seed: products, categories, a sale, a customer.
if (!function_exists('wc_get_product')) { echo "no wc\n"; return; }

function seed_get_or_make_cat($name) {
    $t = get_term_by('name', $name, 'product_cat');
    if ($t) return $t->term_id;
    $r = wp_insert_term($name, 'product_cat');
    return is_wp_error($r) ? 0 : $r['term_id'];
}

function seed_make_simple($name, $price, $cat_ids, $sale = null, $desc = '') {
    $existing = get_page_by_title($name, OBJECT, 'product');
    if ($existing) return $existing->ID;
    $p = new WC_Product_Simple();
    $p->set_name($name);
    $p->set_regular_price((string)$price);
    if ($sale !== null) $p->set_sale_price((string)$sale);
    $p->set_description($desc ?: "A high quality $name, crafted with care and built to last. Free shipping on orders over \$50.");
    $p->set_short_description("Premium $name — customer favourite.");
    $p->set_catalog_visibility('visible');
    $p->set_status('publish');
    $p->set_stock_status('instock');
    if ($cat_ids) $p->set_category_ids($cat_ids);
    return $p->save();
}

$apparel = seed_get_or_make_cat('Apparel');
$home    = seed_get_or_make_cat('Home & Living');
$access  = seed_get_or_make_cat('Accessories');

seed_make_simple('Classic Cotton Tee', 29, [$apparel]);
seed_make_simple('Merino Wool Beanie', 24, [$apparel, $access], 18);
seed_make_simple('Ceramic Pour-Over Mug', 19, [$home]);
seed_make_simple('Linen Throw Blanket', 79, [$home], 59);
seed_make_simple('Leather Card Wallet', 45, [$access]);
seed_make_simple('Stainless Water Bottle', 32, [$home, $access]);

// Variable product (size variations) for catalog/screenshots needing variants.
if (!get_page_by_title('Everyday Hooded Sweatshirt', OBJECT, 'product')) {
    $vp = new WC_Product_Variable();
    $vp->set_name('Everyday Hooded Sweatshirt');
    $vp->set_description('Soft brushed-fleece hoodie. Pre-shrunk, true to size.');
    $vp->set_short_description('Cosy unisex hoodie in three sizes.');
    $vp->set_category_ids([$apparel]);
    $vp->set_status('publish');
    $attr = new WC_Product_Attribute();
    $attr->set_name('Size');
    $attr->set_options(['Small','Medium','Large']);
    $attr->set_visible(true);
    $attr->set_variation(true);
    $vp->set_attributes([$attr]);
    $vpid = $vp->save();
    foreach (['Small'=>49,'Medium'=>49,'Large'=>54] as $size=>$pr) {
        $v = new WC_Product_Variation();
        $v->set_parent_id($vpid);
        $v->set_attributes(['size'=>$size]);
        $v->set_regular_price((string)$pr);
        $v->set_stock_status('instock');
        $v->save();
    }
    WC_Product_Variable::sync($vpid);
}

echo "seed-base-done\n";
