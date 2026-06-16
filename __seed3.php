<?php
// Group seed #3: subscribers, store locations, gift registries, return
// requests, abandoned carts. Idempotent-ish (checks existing by title/email).
if (!class_exists('WooCommerce')) { echo "no wc\n"; return; }
global $wpdb;

$cust = get_user_by('email', 'jordan@example.com');
$uid  = $cust ? $cust->ID : 1;

// ---------- Subscribers (subscribe_subscriber CPT) ----------
$subs = [
    ['Jordan Avery','jordan@example.com','checkout','-3 days'],
    ['Priya Natarajan','priya@example.com','checkout','-6 days'],
    ['Marcus Webb','marcus@example.com','checkout','-9 days'],
    ['Lena Fischer','lena@example.com','checkout','-14 days'],
    ['Tomas Klein','tomas@example.com','checkout','-21 days'],
];
foreach ($subs as [$name,$email,$source,$when]) {
    $exists = get_posts(['post_type'=>'subscribe_subscriber','meta_key'=>'_subscribe_email','meta_value'=>$email,'fields'=>'ids','posts_per_page'=>1]);
    if ($exists) continue;
    $ts = strtotime($when);
    $pid = wp_insert_post(['post_type'=>'subscribe_subscriber','post_status'=>'publish','post_title'=>$email,'post_date'=>gmdate('Y-m-d H:i:s',$ts)]);
    update_post_meta($pid,'_subscribe_email',$email);
    update_post_meta($pid,'_subscribe_consent','1');
    update_post_meta($pid,'_subscribe_consented_at',gmdate('Y-m-d H:i:s',$ts));
    update_post_meta($pid,'_subscribe_source',$source);
}

// ---------- Store locations (locator_store CPT) ----------
$stores = [
    ['Downtown Flagship','12 Market Street','Portland','97204','US','503-555-0110','flagship@store.test','Mon–Fri 9–7, Sat 10–6, Sun 11–5','45.5202','-122.6742'],
    ['Riverside Outlet','88 Riverside Ave','Portland','97214','US','503-555-0188','riverside@store.test','Mon–Sat 10–8, Sun closed','45.5051','-122.6500'],
    ['Eastside Pickup Point','340 Burnside Rd','Gresham','97030','US','503-555-0143','eastside@store.test','Mon–Fri 8–6','45.5001','-122.4302'],
    ['Hillsboro Boutique','7 Orenco Station','Hillsboro','97124','US','503-555-0177','hillsboro@store.test','Tue–Sun 10–6','45.5300','-122.9210'],
];
foreach ($stores as [$name,$addr,$city,$zip,$country,$phone,$email,$hours,$lat,$lng]) {
    $exists = get_page_by_title($name, OBJECT, 'locator_store');
    if ($exists) continue;
    $pid = wp_insert_post(['post_type'=>'locator_store','post_status'=>'publish','post_title'=>$name]);
    update_post_meta($pid,'_locator_address',$addr);
    update_post_meta($pid,'_locator_city',$city);
    update_post_meta($pid,'_locator_postcode',$zip);
    update_post_meta($pid,'_locator_country',$country);
    update_post_meta($pid,'_locator_phone',$phone);
    update_post_meta($pid,'_locator_email',$email);
    update_post_meta($pid,'_locator_hours',$hours);
    update_post_meta($pid,'_locator_lat',$lat);
    update_post_meta($pid,'_locator_lng',$lng);
}

// ---------- Gift registries (gift_registry CPT) ----------
if (!get_page_by_title('Avery–Klein Wedding', OBJECT, 'gift_registry')) {
    $rid = wp_insert_post(['post_type'=>'gift_registry','post_status'=>'publish','post_title'=>'Avery–Klein Wedding','post_author'=>$uid]);
    update_post_meta($rid,'_registry_event_type','wedding');
    update_post_meta($rid,'_registry_event_date',gmdate('Y-m-d', strtotime('+45 days')));
    // items: productId => desired qty
    update_post_meta($rid,'_registry_items',[13=>2, 12=>4, 11=>3, 15=>2]);
}
if (!get_page_by_title('Baby Avery Shower', OBJECT, 'gift_registry')) {
    $rid2 = wp_insert_post(['post_type'=>'gift_registry','post_status'=>'publish','post_title'=>'Baby Avery Shower','post_author'=>$uid]);
    update_post_meta($rid2,'_registry_event_type','baby_shower');
    update_post_meta($rid2,'_registry_event_date',gmdate('Y-m-d', strtotime('+20 days')));
    update_post_meta($rid2,'_registry_items',[10=>5, 12=>6]);
}

// ---------- Return requests (returns_rma CPT) ----------
// Find a completed order for the customer to attach.
$orders = wc_get_orders(['customer_id'=>$uid,'status'=>'completed','limit'=>2]);
if ($orders && !get_posts(['post_type'=>'returns_rma','fields'=>'ids','posts_per_page'=>1])) {
    $o = $orders[0];
    $items = [];
    foreach ($o->get_items() as $it) {
        $items[] = ['product_id'=>$it->get_product_id(),'name'=>$it->get_name(),'qty'=>1];
        break;
    }
    $rma = wp_insert_post(['post_type'=>'returns_rma','post_status'=>'publish','post_title'=>'Return for order #'.$o->get_id()]);
    update_post_meta($rma,'_returns_order_id',$o->get_id());
    update_post_meta($rma,'_returns_customer_id',$uid);
    update_post_meta($rma,'_returns_items',$items);
    update_post_meta($rma,'_returns_reason','Wrong size');
    update_post_meta($rma,'_returns_note','The blanket was larger than expected — would like to exchange.');
    update_post_meta($rma,'_returns_status','requested');

    if (isset($orders[1])) {
        $o2 = $orders[1];
        $items2=[]; foreach ($o2->get_items() as $it){ $items2[]=['product_id'=>$it->get_product_id(),'name'=>$it->get_name(),'qty'=>1]; break; }
        $rma2 = wp_insert_post(['post_type'=>'returns_rma','post_status'=>'publish','post_title'=>'Return for order #'.$o2->get_id()]);
        update_post_meta($rma2,'_returns_order_id',$o2->get_id());
        update_post_meta($rma2,'_returns_customer_id',$uid);
        update_post_meta($rma2,'_returns_items',$items2);
        update_post_meta($rma2,'_returns_reason','Defective item');
        update_post_meta($rma2,'_returns_note','Stitching came loose after first use.');
        update_post_meta($rma2,'_returns_status','approved');
    }
}

// ---------- Abandoned carts (recover_carts table) ----------
$table = $wpdb->prefix . 'recover_carts';
$count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
if ($count === 0) {
    $rows = [
        ['priya@example.com','abandoned',2, 53.00, '-90 minutes', 1, 1],
        ['marcus@example.com','abandoned',1, 29.00, '-3 hours', 1, 1],
        ['lena@example.com','recovered',3, 122.00, '-1 day', 1, 2],
        ['tomas@example.com','pending',1, 19.00, '-20 minutes', 0, 0],
        ['casey@example.com','abandoned',2, 77.00, '-5 hours', 1, 1],
        ['robin@example.com','recovered',1, 45.00, '-2 days', 1, 1],
    ];
    foreach ($rows as $i => [$email,$status,$items,$total,$when,$consent,$sent]) {
        $ts = gmdate('Y-m-d H:i:s', strtotime($when));
        $contents = maybe_serialize([['product_id'=>10+($i%6),'qty'=>$items,'name'=>'Sample item']]);
        $wpdb->insert($table, [
            'token'        => hash('sha256', $email.$i.microtime()),
            'session_key'  => 'sess_'.$i,
            'user_id'      => null,
            'email'        => $email,
            'cart_contents'=> $contents,
            'currency'     => 'USD',
            'cart_total'   => $total,
            'item_count'   => $items,
            'status'       => $status,
            'consent'      => $consent,
            'emails_sent'  => $sent,
            'created_at'   => $ts,
            'updated_at'   => $ts,
            'abandoned_at' => $status==='pending' ? null : $ts,
            'recovered_at' => $status==='recovered' ? $ts : null,
            'last_email_at'=> $sent>0 ? $ts : null,
        ]);
    }
}

echo "seed3-done\n";
