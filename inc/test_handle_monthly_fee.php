<?php

// Hook to calculate, display & save the current subtotal of monthly fees
add_action('woocommerce_cart_totals_before_order_total', 'display_monthly_fees_subtotal');
add_action('woocommerce_review_order_before_order_total', 'display_monthly_fees_subtotal');
add_action('woocommerce_checkout_create_order_line_item', 'save_monthly_fee_to_order', 10, 4);

function display_monthly_fees_subtotal() {
    $subtotal_monthly_fee = 0;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (has_term('data-plan', 'product_cat', $cart_item['product_id'])) {
            $monthly_fee = get_field('monthly_fee', $cart_item['product_id']);
            if ($monthly_fee) {
                $subtotal_monthly_fee += $monthly_fee;
            }
        }
    }

    if ($subtotal_monthly_fee > 0) {
        echo '<tr class="monthly-fee-subtotal">';
        echo '<th>' . __('Monthly Fee Subtotal', 'woocommerce') . '</th>';
        echo '<td>' . wc_price($subtotal_monthly_fee) . '</td>';
        echo '</tr>';
        echo '<tr class="monthly-fee-payment-subtotal">';
        echo '<th>' . __('Monthly Fee Payment Method', 'woocommerce') . '</th>';
        echo '<td><div id="thisismonthlyfeepayment">None</div></td>';
        echo '</tr>';
    }
}

// Don't Remove This
function save_monthly_fee_to_order($item, $cart_item_key, $values, $order) {
    $product_id = $values['product_id'];
    $monthly_fee = get_field('monthly_fee', $product_id);

    if ($monthly_fee) {
        $item->update_meta_data('monthly_fee', $monthly_fee);
    }

    $order_id = $order->get_id();
    $subtotal_monthly_fee = get_post_meta($order_id, 'subtotal_monthly_fee', true);
    $subtotal_monthly_fee += $monthly_fee;

    update_post_meta($order_id, 'total_monthly_fee', wc_price($subtotal_monthly_fee));
}

// Add filter to change monthly_fee meta label on frontend
add_filter('woocommerce_order_item_display_meta_key', 'change_monthly_fee_label', 10, 3);
function change_monthly_fee_label($display_key, $meta, $item) {
    if ($display_key === 'monthly_fee') {
        $display_key = 'Monthly Fee';
    }
    return $display_key;
}

// Add filter to change monthly_fee value format on frontend
add_filter('woocommerce_order_item_display_meta_value', 'format_monthly_fee_thousands', 10, 3);
function format_monthly_fee_thousands($display_value, $meta, $item) {
    if ($meta->key === 'monthly_fee') {
        $display_value = '¥'.number_format($display_value, 0, '.', ',');
    }
    return $display_value;
}