<?php
// Hook to display the gift options on the product page
add_action('woocommerce_before_add_to_cart_button', 'display_gifts_options_and_script');
function display_gifts_options_and_script() {
    global $product;

    // Check if the product is in the 'data-plan' category
    if (has_term('data-plan', 'product_cat', $product->get_id())) {

        // Display the monthly fee if it exists
        $monthly_fee = get_field('monthly_fee', $product->get_id());
        if ($monthly_fee) {
            $formatted_monthly_fee = '¥ ' . number_format($monthly_fee, 0, '.', ',');
            echo '<p>Monthly Fee: ' . esc_html($formatted_monthly_fee) . '</p>';
        }

        $gifts = get_posts(array(
            'post_type' => 'product',
            'numberposts' => -1,
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => 'gifts',
                    'operator' => 'IN',
                ),
            ),
        ));

        if ($gifts) {
            echo '<h5>Choose your free gift!</h5>';
            echo '<p>Note: 1 Free Gift for each Data Plan</p>';
            echo '<div class="gifts-selection">';

            foreach ($gifts as $gift) {
                $gift_product = wc_get_product($gift->ID);
                $is_in_stock = $gift_product->is_in_stock();
                $gift_thumbnail_url = wp_get_attachment_image_src(get_post_thumbnail_id($gift->ID), 'full')[0];

                $style = $is_in_stock ? "" : "style='filter: grayscale(100%); pointer-events: none;'";
                $disabled = $is_in_stock ? "" : "disabled";

                echo "<label for='gift_".esc_attr($gift->ID)."' class='gift-option' $style>";
                echo "<input type='radio' id='gift_".esc_attr($gift->ID)."' name='selected_gift' value='".esc_attr($gift->ID)."' $disabled>";
                echo "<img src='".esc_url($gift_thumbnail_url)."' alt='".esc_attr($gift->post_title)."'>";
                echo "</label>";
            }

            echo '</div>'; // Close .gifts-selection

            // Add JavaScript to control the gift section visibility and Buy Now button enable / disable state
                echo '<script>
                        function initialize() {
                            const discountCheckbox = document.getElementById("discount_checkbox");
                            const giftsSelection = document.querySelector(".gifts-selection");
                            const buyNowButton = document.querySelector("button.single_add_to_cart_button");
                            const giftRadios = giftsSelection.querySelectorAll("input[type=radio]");
                        
                            // Set the button to disabled initially
                            buyNowButton.disabled = true;
                            updateBuyNowButtonStyle(buyNowButton, discountCheckbox.checked, document.querySelector("input[name=selected_gift]:checked"));
                        
                            discountCheckbox.addEventListener("change", () => {
                                giftsSelection.style.display = discountCheckbox.checked ? "none" : "block";
                                giftRadios.forEach(radio => {
                                    radio.disabled = discountCheckbox.checked;
                                    if (discountCheckbox.checked) {
                                        radio.checked = false;
                                    }
                                });
                                updateBuyNowButtonStyle(buyNowButton, discountCheckbox.checked, document.querySelector("input[name=selected_gift]:checked"));
                            });
                        
                            giftRadios.forEach(radio => {
                                radio.addEventListener("change", () => {
                                    updateBuyNowButtonStyle(buyNowButton, discountCheckbox.checked, radio);
                                });
                            });
                        }
                        
                        function updateBuyNowButtonStyle(button, isDiscountApplied, selectedGift) {
                            button.disabled = !isDiscountApplied && !selectedGift;
                            if (button.disabled) {
                                button.style.backgroundColor = "#ddd";
                                button.style.color = "#aaa";
                                button.style.cursor = "not-allowed";
                            } else {
                                button.style.backgroundColor = "#de3594";
                                button.style.color = "white";
                                button.style.cursor = "pointer";
                            }
                        }
                        
                        if (document.readyState === "loading") {
                            document.addEventListener("DOMContentLoaded", initialize);
                        } else {
                            initialize(); // DOMContentLoaded has already fired
                        }                  
                    </script>';
        }

        // Add the discount checkbox
        echo '<label for="discount_checkbox" class="discount-option">
                <input type="checkbox" id="discount_checkbox" name="apply_discount">
                I want the ¥600 Discount Instead
            </label>';
    }
}

// Hook to apply the discount if the checkbox is checked
add_action( 'woocommerce_before_calculate_totals', 'apply_discount_if_checked', 10 );
function apply_discount_if_checked( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        if ( isset( $cart_item['apply_discount'] ) && $cart_item['apply_discount'] ) {
            $original_price = $cart_item['data']->get_price();
            $adjusted_price = max( 0, $original_price - 600 ); // Ensure price doesn't go negative
            $cart_item['data']->set_price( $adjusted_price );
        }
    }
}

// Hook to save the discount flag to cart item data
add_filter( 'woocommerce_add_cart_item_data', 'add_discount_flag_to_cart', 10, 2 );
function add_discount_flag_to_cart( $cart_item_data, $product_id ) {
    if ( isset( $_POST['apply_discount'] ) && $_POST['apply_discount'] ) {
        $cart_item_data['apply_discount'] = true;
    }
    return $cart_item_data;
}

// Hook to add the selected gift to the cart item data
add_filter('woocommerce_add_cart_item_data', 'add_gift_to_cart', 10, 2);
function add_gift_to_cart($cart_item_data, $product_id) {
    if (isset($_POST['selected_gift']) && !empty($_POST['selected_gift'])) {
        $gift_product_id = sanitize_text_field($_POST['selected_gift']);
        $gift_product = wc_get_product($gift_product_id);

        // Check if the gift product is in stock
        if (!$gift_product || !$gift_product->is_in_stock()) {
            wc_add_notice('The selected gift is out of stock.', 'error');
            return $cart_item_data;
        }

        // Store the gift product ID in the cart item data
        $cart_item_data['selected_gift'] = $gift_product_id;
    }
    return $cart_item_data;
}

// Hook to handle the addition of products to the cart
add_action('woocommerce_add_to_cart', 'handle_gift_product_addition', 10, 6);
function handle_gift_product_addition($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    if (isset($cart_item_data['selected_gift']) && !empty($cart_item_data['selected_gift'])) {
        // Link the gift with the data plan instead of adding it to the cart
        WC()->cart->cart_contents[$cart_item_key]['selected_gift'] = $cart_item_data['selected_gift'];
    }
}

// Hook to display combined product & monthly fees in the cart and checkout
add_filter('woocommerce_get_item_data', 'display_grouped_product_in_cart', 10, 2);
function display_grouped_product_in_cart($item_data, $cart_item) {
    // Display the selected gift
    if (isset($cart_item['selected_gift'])) {
        $gift_product = wc_get_product($cart_item['selected_gift']);
        if ($gift_product) {
            $item_data[] = array(
                'key'     => __('Free Gift', 'woocommerce'),
                'value'   => '<span class="hidden-gift-slug">'.$gift_product->get_slug().'</span>'.$gift_product->get_name(),
                'display' => '',
            );
        }
    }

    // Display monthly fee & data_plan_code for data plan items
    if (has_term('data-plan', 'product_cat', $cart_item['product_id'])) {
        $monthly_fee = get_field('monthly_fee', $cart_item['product_id']);
        $dpc = get_field('data_plan_code', $cart_item['product_id']);

        if ($monthly_fee) {
            $formatted_monthly_fee = '¥ ' . number_format($monthly_fee, 0, '.', ',');
            $item_data[] = array(
                'key'   => __('Monthly Fee', 'woocommerce'),
                'value' => esc_html($formatted_monthly_fee),
            );

            $item_data[] = array(
                'key'   => __('Data Plan Code', 'woocommerce'),
                'value' => $dpc,
                'class' => 'dpc_value',
            );

            
        }

    }

    // Display a discount notice if the discount is applied
    if (isset($cart_item['apply_discount']) && $cart_item['apply_discount']) {
        $item_data[] = array(
            'key'     => __('Discount', 'woocommerce'),
            'value'   => __('¥600 Discount Applied', 'woocommerce'),
            'display' => '',
        );
    }

    return $item_data;
}

// Hook to customize the product price display for data plans
add_filter('woocommerce_get_price_html', 'custom_price_display_for_data_plans', 10, 2);
function custom_price_display_for_data_plans($price_html, $product) {
    if (has_term('data-plan', 'product_cat', $product->get_id())) {
        $price_html = 'First Month: ' . $price_html;
    }

    return $price_html;
}


// Hook to save the selected free gift into the order
add_action('woocommerce_checkout_create_order_line_item', 'save_selected_gift_to_order', 10, 4);
function save_selected_gift_to_order($item, $cart_item_key, $values, $order) {
    if (isset($values['selected_gift'])) {
        $selected_gift_id = $values['selected_gift'];

        // Save the selected gift ID to the order item meta
        $item->update_meta_data('_selected_gift', $selected_gift_id);

        // You can also save the selected gift name
        $selected_gift = wc_get_product($selected_gift_id);
        if ($selected_gift) {
            $item->update_meta_data('selected_gift_name', $selected_gift->get_name());
        }

        $item->update_meta_data('apply_discount',false);
    }

    if (isset($values['apply_discount'])){
        $item->update_meta_data('_selected_gift', 0);
        $item->update_meta_data('selected_gift_name',"none");
        $item->update_meta_data('apply_discount',true);
    }

}

// Add filter to change selected_gift_name meta label on frontend
add_filter('woocommerce_order_item_display_meta_key', 'change_selected_gift_name_label', 10, 3);
function change_selected_gift_name_label($display_key, $meta, $item) {
    if ($display_key === 'selected_gift_name') {
        $display_key = 'Free Gift';
    }
    if ($display_key === '_selected_gift'){
        $display_key = 'Gift ID';
    }
    if ($display_key === 'apply_discount'){
        $display_key = 'Apply ¥600 Discount?';
    }
    return $display_key;
}