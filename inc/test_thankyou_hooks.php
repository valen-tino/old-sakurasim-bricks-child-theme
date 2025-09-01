<?php
// Display the Total Monthly Fee At the Order Details inside the Thank You Page
add_action('woocommerce_order_items_table', 'add_total_monthly_fee_inside_order_details');

function add_total_monthly_fee_inside_order_details($order) {
    $total_monthly_fee = 0;

    foreach ($order->get_items() as $item_id => $item) {
        $monthly_fee = $item->get_meta('monthly_fee');
        if ($monthly_fee) {
            $total_monthly_fee += $monthly_fee;
        }
    }

    if ($total_monthly_fee > 0) {
        echo '<tr class="total-monthly-fee">';
        echo '<th>' . __('Total Monthly Fee', 'woocommerce') . '</th>';
        echo '<td>' . wc_price($total_monthly_fee) . '</td>';
        echo '</tr>';
    }
}