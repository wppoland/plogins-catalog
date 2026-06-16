<?php
// Group seed #2: classic checkout/cart, shipping (local pickup), customer,
// orders, subscribers, store locations, registries, returns, abandoned carts.
if (!class_exists('WooCommerce')) { echo "no wc\n"; return; }

// --- Classic cart & checkout (shortcodes) so plugin checkout hooks fire ---
$cart_id = wc_get_page_id('cart');
$checkout_id = wc_get_page_id('checkout');
if ($cart_id > 0) wp_update_post(['ID'=>$cart_id, 'post_content'=>'[woocommerce_cart]']);
if ($checkout_id > 0) wp_update_post(['ID'=>$checkout_id, 'post_content'=>'[woocommerce_checkout]']);

// --- Store base + currency so prices render cleanly ---
update_option('woocommerce_currency', 'USD');
update_option('woocommerce_store_address', '12 Market Street');
update_option('woocommerce_store_city', 'Portland');
update_option('woocommerce_default_country', 'US:OR');
update_option('woocommerce_store_postcode', '97204');
update_option('woocommerce_calc_taxes', 'no');

// --- Shipping zone "Everywhere" with Flat rate + Local pickup ---
if (class_exists('WC_Shipping_Zones')) {
    $zones = WC_Shipping_Zones::get_zones();
    $have_pickup = false;
    foreach ($zones as $z) { foreach (($z['shipping_methods'] ?? []) as $m) { if ($m->id === 'local_pickup') $have_pickup = true; } }
    if (!$have_pickup) {
        $zone = new WC_Shipping_Zone(0); // "Rest of the world" default zone
        $zone->add_shipping_method('flat_rate');
        $zone->add_shipping_method('local_pickup');
        $zone->save();
    }
}

// --- Customer with completed + processing orders ---
$cust = get_user_by('email', 'jordan@example.com');
if (!$cust) {
    $uid = wp_insert_user([
        'user_login' => 'jordan',
        'user_pass'  => 'password',
        'user_email' => 'jordan@example.com',
        'role'       => 'customer',
        'first_name' => 'Jordan',
        'last_name'  => 'Avery',
    ]);
} else {
    $uid = $cust->ID;
}
update_user_meta($uid, 'billing_first_name', 'Jordan');
update_user_meta($uid, 'billing_last_name', 'Avery');
update_user_meta($uid, 'billing_email', 'jordan@example.com');
update_user_meta($uid, 'billing_address_1', '88 Riverside Ave');
update_user_meta($uid, 'billing_city', 'Portland');
update_user_meta($uid, 'billing_postcode', '97204');
update_user_meta($uid, 'billing_country', 'US');
update_user_meta($uid, 'billing_state', 'OR');
update_user_meta($uid, 'billing_phone', '503-555-0142');

function seed_order($uid, $product_ids, $status, $days_ago) {
    $order = wc_create_order(['customer_id' => $uid]);
    foreach ($product_ids as $pid) {
        $p = wc_get_product($pid);
        if ($p) $order->add_product($p, 1);
    }
    $order->set_address([
        'first_name'=>'Jordan','last_name'=>'Avery','email'=>'jordan@example.com',
        'address_1'=>'88 Riverside Ave','city'=>'Portland','postcode'=>'97204','country'=>'US','state'=>'OR','phone'=>'503-555-0142',
    ], 'billing');
    $order->calculate_totals();
    $order->set_status($status);
    $date = gmdate('Y-m-d H:i:s', time() - $days_ago * DAY_IN_SECONDS);
    $order->set_date_created($date);
    $order->save();
    return $order->get_id();
}

if (!get_user_meta($uid, '_seed_orders_done', true)) {
    seed_order($uid, [10, 12], 'completed', 5);   // Tee + Mug
    seed_order($uid, [13], 'completed', 12);       // Throw blanket
    seed_order($uid, [14, 15], 'processing', 2);   // Wallet + Bottle
    update_user_meta($uid, '_seed_orders_done', '1');
}

echo "seed2-done uid=$uid\n";
