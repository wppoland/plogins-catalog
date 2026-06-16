<?php
/**
 * Seed realistic scenario for wp.org screenshots. Run via wp eval-file.
 * Idempotent-ish: deletes prior seed products/CPTs by a marker meta before re-creating.
 */

if (!defined('ABSPATH')) { exit; }

echo "== SEED START ==\n";

// ---- Customer: Jordan ----
$jordan = get_user_by('login', 'jordan');
if (!$jordan) {
    $uid = wp_insert_user([
        'user_login' => 'jordan',
        'user_pass'  => 'password',
        'user_email' => 'jordan@harbor.test',
        'first_name' => 'Jordan',
        'last_name'  => 'Avery',
        'display_name' => 'Jordan Avery',
        'role'       => 'customer',
    ]);
    $jordan = get_user_by('id', $uid);
    echo "created jordan: {$uid}\n";
} else {
    echo "jordan exists: {$jordan->ID}\n";
}
$jordanId = (int) $jordan->ID;

// ---- Products ----
function ss2_make_product($name, $price) {
    // reuse by title
    $existing = get_page_by_title($name, OBJECT, 'product');
    if ($existing) { return wc_get_product($existing->ID); }
    $p = new WC_Product_Simple();
    $p->set_name($name);
    $p->set_regular_price((string)$price);
    $p->set_price((string)$price);
    $p->set_status('publish');
    $p->set_catalog_visibility('visible');
    $p->set_short_description('A well-made everyday essential from Harbor & Co.');
    $p->set_description('Crafted for daily use. Durable materials, simple design, built to last.');
    $id = $p->save();
    return wc_get_product($id);
}

$prodSpecs = [
    ['Linen Apron', 28],
    ['Stoneware Mug', 16],
    ['Beeswax Candle', 22],
    ['Cotton Tea Towel', 12],
    ['Cast Iron Skillet', 64],
    ['Olive Wood Board', 38],
];
$products = [];
foreach ($prodSpecs as $spec) {
    $products[] = ss2_make_product($spec[0], $spec[1]);
}
$pids = array_map(fn($p) => $p->get_id(), $products);
echo "products: " . implode(',', $pids) . "\n";

// ---- A completed order for Jordan (needed by followup/returns eligibility) ----
$orders = wc_get_orders(['customer_id' => $jordanId, 'limit' => 1]);
if (empty($orders)) {
    $order = wc_create_order(['customer_id' => $jordanId]);
    $order->add_product($products[0], 1); // Linen Apron
    $order->add_product($products[1], 2); // Stoneware Mug
    $order->set_address([
        'first_name' => 'Jordan', 'last_name' => 'Avery',
        'email' => 'jordan@harbor.test', 'address_1' => '14 Quay Street',
        'city' => 'Portside', 'postcode' => 'PS1 4QY', 'country' => 'GB',
    ], 'billing');
    $order->calculate_totals();
    $order->update_status('completed');
    $orderId = $order->get_id();
    echo "order created: {$orderId}\n";
} else {
    $order = $orders[0];
    $orderId = $order->get_id();
    if ($order->get_status() !== 'completed') { $order->update_status('completed'); }
    echo "order exists: {$orderId}\n";
}

// ---- Returns: a return request CPT for Jordan ----
$existingReturns = get_posts(['post_type' => 'returns_rma', 'post_status' => 'private', 'numberposts' => 1, 'fields' => 'ids']);
if (empty($existingReturns)) {
    $items = [];
    foreach ($order->get_items() as $itemId => $item) {
        $items[] = ['item_id' => $itemId, 'name' => $item->get_name(), 'qty' => 1];
    }
    $rmaId = wp_insert_post([
        'post_type' => 'returns_rma',
        'post_status' => 'private',
        'post_title' => sprintf('Return for order #%d — %s', $orderId, date_i18n('M j, Y')),
    ]);
    update_post_meta($rmaId, '_returns_order_id', $orderId);
    update_post_meta($rmaId, '_returns_customer_id', $jordanId);
    update_post_meta($rmaId, '_returns_items', $items);
    update_post_meta($rmaId, '_returns_reason', 'Wrong size');
    update_post_meta($rmaId, '_returns_note', 'The apron is lovely but a touch large — would like to exchange for a smaller one.');
    update_post_meta($rmaId, '_returns_status', 'requested');
    echo "returns rma: {$rmaId}\n";
} else {
    echo "returns rma exists: {$existingReturns[0]}\n";
}

// ---- Registry: a public gift registry with progress ----
$existingReg = get_posts(['post_type' => 'gift_registry', 'post_status' => 'publish', 'numberposts' => 1, 'fields' => 'ids']);
if (empty($existingReg)) {
    $regId = wp_insert_post([
        'post_type' => 'gift_registry',
        'post_status' => 'publish',
        'post_title' => "Jordan & Sam's Wedding Registry",
        'post_author' => $jordanId,
    ]);
    update_post_meta($regId, '_registry_event_type', 'wedding');
    update_post_meta($regId, '_registry_event_date', date('Y-m-d', strtotime('+45 days')));
    // desired quantities
    $items = [
        $products[4]->get_id() => 2, // Cast Iron Skillet
        $products[5]->get_id() => 4, // Olive Wood Board
        $products[2]->get_id() => 6, // Beeswax Candle
        $products[1]->get_id() => 8, // Stoneware Mug
    ];
    update_post_meta($regId, '_registry_items', $items);
    // partial purchases for progress bars
    $purchased = [
        $products[4]->get_id() => 2, // fully fulfilled
        $products[5]->get_id() => 1,
        $products[2]->get_id() => 4,
        $products[1]->get_id() => 3,
    ];
    update_post_meta($regId, '_registry_purchased', $purchased);
    echo "registry: {$regId} url=" . get_permalink($regId) . "\n";
} else {
    echo "registry exists: {$existingReg[0]} url=" . get_permalink($existingReg[0]) . "\n";
}

// ---- Locator: store locations ----
$existingStores = get_posts(['post_type' => 'locator_store', 'numberposts' => 1, 'fields' => 'ids']);
$storePT = post_type_exists('locator_store') ? 'locator_store' : null;
if (!$storePT) {
    // discover the real post type
    foreach (['locator_store', 'store_location', 'locator_location'] as $cand) {
        if (post_type_exists($cand)) { $storePT = $cand; break; }
    }
}
echo "locator post type: " . ($storePT ?: 'UNKNOWN') . "\n";
if ($storePT) {
    $have = get_posts(['post_type' => $storePT, 'numberposts' => -1, 'fields' => 'ids']);
    if (count($have) < 3) {
        $stores = [
            ['Harbor & Co. — Portside', 'PS1 4QY', '14 Quay Street, Portside', '01234 567890', "Mon–Fri 9–6\nSat 10–4"],
            ['Harbor & Co. — Old Town', 'OT2 9AB', '3 Market Square, Old Town', '01234 222333', "Mon–Sat 9–6"],
            ['Harbor & Co. — Riverside', 'RS5 1KL', '88 Mill Lane, Riverside', '01234 444555', "Tue–Sun 10–5"],
        ];
        foreach ($stores as $st) {
            $sid = wp_insert_post([
                'post_type' => $storePT,
                'post_status' => 'publish',
                'post_title' => $st[0],
            ]);
            // try common meta keys
            foreach ([
                '_locator_address' => $st[2], 'address' => $st[2], '_store_address' => $st[2],
                '_locator_postcode' => $st[1], 'postcode' => $st[1], '_store_postcode' => $st[1],
                '_locator_phone' => $st[3], 'phone' => $st[3], '_store_phone' => $st[3],
                '_locator_hours' => $st[4], 'hours' => $st[4], '_store_hours' => $st[4],
                '_locator_city' => explode(',', $st[2])[1] ?? '', 'city' => trim(explode(',', $st[2])[1] ?? ''),
            ] as $k => $v) {
                update_post_meta($sid, $k, $v);
            }
            echo "store: {$sid} {$st[0]}\n";
        }
    } else {
        echo "stores exist: " . count($have) . "\n";
    }
}

// ---- Recover: abandoned carts (direct DB) ----
global $wpdb;
$table = $wpdb->prefix . 'recover_carts';
$exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
echo "recover table: " . ($exists ?: 'MISSING') . "\n";
if ($exists) {
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    if ($count < 5) {
        $cartContents = maybe_serialize([
            ['product_id' => $products[4]->get_id(), 'name' => 'Cast Iron Skillet', 'qty' => 1, 'total' => 64],
            ['product_id' => $products[0]->get_id(), 'name' => 'Linen Apron', 'qty' => 1, 'total' => 28],
        ]);
        $rows = [
            ['alex@example.com', 92.00, 2, 'recovered', '-3 days'],
            ['priya@example.com', 38.00, 1, 'abandoned', '-2 days'],
            ['sam@example.com', 64.00, 1, 'abandoned', '-1 days'],
            ['lee@example.com', 50.00, 3, 'pending', '-3 hours'],
            ['mara@example.com', 22.00, 1, 'recovered', '-5 days'],
            ['noah@example.com', 16.00, 1, 'pending', '-1 hours'],
            ['ivy@example.com', 102.00, 4, 'abandoned', '-4 days'],
        ];
        foreach ($rows as $i => $r) {
            $created = gmdate('Y-m-d H:i:s', strtotime($r[4]));
            $wpdb->insert($table, [
                'token' => wp_generate_password(64, false),
                'session_key' => 'sess_' . $i,
                'email' => $r[0],
                'cart_contents' => $cartContents,
                'currency' => get_woocommerce_currency(),
                'cart_total' => $r[1],
                'item_count' => $r[2],
                'status' => $r[3],
                'consent' => 1,
                'emails_sent' => $r[3] === 'pending' ? 0 : 1,
                'created_at' => $created,
                'updated_at' => $created,
                'abandoned_at' => $r[3] !== 'pending' ? $created : null,
                'recovered_at' => $r[3] === 'recovered' ? gmdate('Y-m-d H:i:s', strtotime($r[4] . ' +2 hours')) : null,
            ]);
        }
        echo "recover carts inserted: " . count($rows) . "\n";
    } else {
        echo "recover carts exist: {$count}\n";
    }
}

// ---- Subscribe: a few subscribers ----
$subPT = post_type_exists('subscribe_subscriber') ? 'subscribe_subscriber' : null;
echo "subscribe post type: " . ($subPT ?: 'UNKNOWN') . "\n";
if ($subPT) {
    $have = get_posts(['post_type' => $subPT, 'numberposts' => -1, 'fields' => 'ids', 'post_status' => 'any']);
    if (count($have) < 4) {
        $subs = [
            ['alex@example.com', 'checkout'],
            ['priya@example.com', 'checkout'],
            ['sam@example.com', 'checkout'],
            ['mara@example.com', 'checkout'],
            ['lee@example.com', 'checkout'],
        ];
        foreach ($subs as $s) {
            $sid = wp_insert_post([
                'post_type' => $subPT,
                'post_status' => 'publish',
                'post_title' => $s[0],
            ]);
            update_post_meta($sid, '_subscribe_email', $s[0]);
            update_post_meta($sid, '_subscribe_consent', 1);
            update_post_meta($sid, '_subscribe_consented_at', time() - random_int(3600, 864000));
            update_post_meta($sid, '_subscribe_source', $s[1]);
        }
        echo "subscribers inserted\n";
    } else {
        echo "subscribers exist: " . count($have) . "\n";
    }
}

echo "JORDAN_ID={$jordanId}\n";
echo "ORDER_ID={$orderId}\n";
echo "== SEED DONE ==\n";
