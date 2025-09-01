<?php
function checkout_at_admin_button() {
    // Check if 'seller_code' exists in the URL
    if (isset($_GET['seller_code']) && !empty($_GET['seller_code'])) {
        $seller_code = sanitize_text_field($_GET['seller_code']);
        ?>
        <br/><br/>
        <a href="#" class="checkout_button alt" id="checkout_at_admin">Checkout At Admin</a>
        <?php
    }
}
add_action('woocommerce_proceed_to_checkout', 'checkout_at_admin_button', 20);

add_action('wp_footer', 'send_cart_data_via_webhook');
function send_cart_data_via_webhook() {
    if (is_cart()) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#checkout_at_admin').click(function(e) {
                    e.preventDefault();

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'get_cart_contents'
                        },
                        success: function(response) {
                            var modifiedResponse = {};
                            Object.keys(response).forEach(function(key) {
                                var item = response[key];

                                // Get & Process Product Name
                                var productNameElement = $('[data-product_id="' + item.product_id + '"]').closest('tr').find('.product-name');
                                var productName = productNameElement.contents().filter(function() {
                                    return this.nodeType === 3;
                                }).text().trim();

                                // Get & Process Monthly Fee
                                var monthlyFee = productNameElement.find('.variation-MonthlyFee').text().trim();
                                var monthlyFeeNumber = parseFloat(monthlyFee.replace(/[^\d.]/g, ''));

                                // Get & Process the data_plan_code
                                var dataPlanCodeElement = productNameElement.find('.variation-DataPlanCode');
                                var dpc = dataPlanCodeElement.text().replace('Data Plan Code:', '').trim();

                                // Get the selected_gift_name from the hidden-gift-slug span
                                var selectedGiftName = productNameElement.find('.variation-FreeGift').find('.hidden-gift-slug').text().trim();

                                var modifiedItem = {
                                    key: item.key,
                                    data_hash: item.data_hash,
                                    apply_discount: item.apply_discount ? item.apply_discount : false,
                                    product_id: item.product_id,
                                    product_name: productName,
                                    data_plan_code: dpc ? dpc : null,
                                    monthly_fee: monthlyFeeNumber,
                                    quantity: item.quantity,
                                    selected_gift: item.selected_gift ? item.selected_gift : null,
                                    selected_gift_name: selectedGiftName ? selectedGiftName : null,
                                    line_total: item.line_total,
                                    data: item.data
                                };
                                modifiedResponse[key] = modifiedItem;
                            });

                            $.ajax({
                                url: 'https://webhook.site/2c64fe6d-2a6b-49ab-bafc-d5a014f23f43',
                                type: 'POST',
                                data: JSON.stringify(modifiedResponse),
                                contentType: 'application/json; charset=utf-8',
                                dataType: 'json',
                                async: false,
                                success: function(msg) {
                                    alert('Your Cart is submitted, redirecting to staging.vdmjp.com...');
                                    setTimeout(function() {
                                        window.location.href = 'https://staging.vdmjp.com/?seller_code=' + <?php echo $seller_code ?>;
                                    }, 2000)
                                }
                            });
                        }
                    });
                });
            });
        </script>
        <?php
    }
}

add_action('wp_ajax_get_cart_contents', 'get_cart_contents');
add_action('wp_ajax_nopriv_get_cart_contents', 'get_cart_contents');
function get_cart_contents() {
    $cart = WC()->cart->get_cart();
    wp_send_json($cart);
}