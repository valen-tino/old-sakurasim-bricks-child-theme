<?php
// Add the fields to the checkout
add_action('woocommerce_after_order_notes', 'customise_checkout_field');

function customise_checkout_field($checkout) {
    echo '<div id="customise_checkout_field"><h4>' . __('Delivery Option & Monthly Fee Payment Method') . '</h4>';

    // Delivery Option field
    woocommerce_form_field('delivery_option', array(
        'type'          => 'select',
        'class'         => array('delivery-option-class form-row-wide'),
        'label'         => __('Delivery Option'),
        'required'      => true,
        'options'       => array(
            'blank' => 'Please select an option',
            'asap' => 'As soon as possible',
            'custom' => 'Choose your preferred date',
        ),
    ), $checkout->get_value('delivery_option'));

    // Delivery Date field - hidden initially
    woocommerce_form_field('delivery_date', array(
        'type'          => 'text',
        'class'         => array('delivery-date-class form-row-wide'),
        'label'         => __('Delivery Date'),
        'placeholder'   => __('Select a date'),
    ), $checkout->get_value('delivery_date'));

    echo '</div>';

    // Choose Your Delivery Time
    woocommerce_form_field('delivery_time', array(
        'type'          => 'select',
        'class'         => array('delivery-time-class form-row-wide'),
        'label'         => __('Choose Your Delivery Time'),
        'required'      => true,
        'options'       => array(
            'blank' => 'Please select an option',
            '08:00-12:00' => '08:00-12:00',
            '14:00-16:00' => '14:00-16:00',
            '16:00-18:00' => '16:00-18:00',
            '18:00-20:00' => '18:00-20:00',
            '19:00-21:00' => '19:00-21:00'
        ),
    ), $checkout->get_value('delivery_time'));

    // Monthly Fee Payment Option field
    woocommerce_form_field('monthlyfee_payment_option', array(
        'type'          => 'select',
        'class'         => array('monthlyfee-payment-class form-row-wide'),
        'label'         => __('Choose Monthly Fee Payment Method'),
        'required'      => true,
        'options'       => array(
            'blank' => 'Please select an option',
            'smartpit' => 'Smartpit (Payment Via Convenience Store)',
            'stripe' => 'Stripe (Payment Via Credit/Debit Card or Convenience Store)',
            'bank-transfer-gmo' => 'GMO Virtual Bank (Payment Via Bank Transfer)',
        ),
    ), $checkout->get_value('monthlyfee_payment_option'));

    echo '<div id="price-change-message"></div>';
}

// Display field value on the order edit page
add_action('woocommerce_admin_order_data_after_billing_address', 'customise_checkout_field_display_admin_order_meta', 10,2);

function customise_checkout_field_display_admin_order_meta($order)
{
    echo '<p><strong>' . __('Delivery Date') . ':</strong> ' . get_post_meta($order->get_id(), 'delivery_date', true) . '</p>';
    echo '<p><strong>' . __('Monthly Fee Payment Option') . ':</strong> ' . get_post_meta($order->get_id(), 'monthlyfee_payment_option', true) . '</p>';
    echo '<p><strong>' . __('Delivery Time') . ':</strong> ' . get_post_meta($order->get_id(), 'delivery_time', true) . '</p>';
    
    // Display Address Image
    $address_image_url = get_post_meta($order->get_id(), 'address_image', true);
    echo '<p><strong>Address Image:</strong> <br/>' . (!empty($address_image_url) ? '<img src="' . esc_url($address_image_url) . '" style="max-width: 200px; max-height: 200px;" />' : '-') . '</p>';
}

// Enqueue the jQuery UI datepicker script
function enqueue_datepicker()
{
    // Load the datepicker script (pre-registered in WordPress).
    wp_enqueue_script('jquery-ui-datepicker');

    // You need styling for the datepicker. For simplicity, I've linked to Google's hosted jQuery UI CSS.
    wp_register_style('jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css');
    wp_enqueue_style('jquery-ui');
}

add_action('wp_enqueue_scripts', 'enqueue_datepicker');

// Add the jQuery script
function add_custom_datepicker_script()
{
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function ($) {
        // Initially hide the date picker field
        $('.delivery-date-class').hide();

        $('#delivery_option').change(function () {
            if ($(this).val() == 'custom') {
                $('.delivery-date-class').show();
                $('#delivery_date').datepicker({
                    dateFormat: 'dd-mm-yy',
                    minDate: '+2d'
                });
            } else if ($(this).val() == 'asap') {
                $('.delivery-date-class').hide();
                // Automatically set the delivery date to 2 days from today
                var asapDate = new Date();
                asapDate.setDate(asapDate.getDate() + 2);
                var day = ("0" + asapDate.getDate()).slice(-2);
                var month = ("0" + (asapDate.getMonth() + 1)).slice(-2);
                var asapDateString = day + '-' + month + '-' + asapDate.getFullYear();
                $('#delivery_date').val(asapDateString);
            } else {
                $('.delivery-date-class').hide();
                $('#delivery_date').val(''); // Clear the date field
            }
        });
    });

    var selectMonthlyFeeOption = document.getElementById('monthlyfee_payment_option');
    if (selectMonthlyFeeOption) {
    selectMonthlyFeeOption.addEventListener("change", function() {
        var selectedOption = this.value;
        var priceChangeMessage = '';
        var methodChange = '';
        var priceChangeAmount = 0;

        switch (selectedOption) {
            case 'stripe':
                priceChangeMessage = '<div class="friend_info">Congratuations, You got a ¥30 discount for choosing Stripe.<br/>For the Convenience Store Method, It can be use only at the following store chains:<br/><ul><li>Family Mart</li><li>Mini Stop</li><li>Lawson</li><li>Seiko Mart</li></ul></div><br/>';
                priceChangeAmount = 30;
                methodChange = 'Stripe (Payment via Credit/Debit Card or Convenience Store) - ¥30 Discount';
                break;
            case 'smartpit':
                priceChangeMessage = '<div class="friend_info">For the Convenience Store Method, It can be use only at the following store chains:<br/><ul><li>Family Mart</li><li>Mini Stop</li><li>Lawson</li><li>Seiko Mart</li></ul></div><br/>';
                priceChangeAmount = 0;
                methodChange = 'Smartpit (Payment via Convenience Store)';
                break;
            case 'bank-transfer-gmo':
                priceChangeMessage = '<div class="friend_info">Congratuations, You got a ¥180 discount for choosing Bank Transfer.</div><br/>';
                priceChangeAmount = 180;
                methodChange = 'GMO Virtual Bank (Payment via Bank Transfer) - ¥180 Discount';
                break;
            case 'choose-later':
                priceChangeMessage = 'Our Customer Support will contact you in regarding the payment method.';
                priceChangeAmount = 0;
                methodChange = 'Choose Later';
                break;
            default:
                priceChangeMessage = '';
                priceChangeAmount = 0;
                methodChange = 'None';
        }


    if (methodChange != ''){
        document.getElementById("price-change-message").classList.add("friend-info");

        // Display the price change message
        var priceChangeElement = document.getElementById('price-change-message');
        priceChangeElement.innerHTML = priceChangeMessage;
        
    }
    else{
        document.getElementById("price-change-message").classList.remove("friend-info");
    }

    // Display the monthly fee method change message in place order
    var methodChangeElement = document.getElementById('thisismonthlyfeepayment');
    methodChangeElement.textContent = methodChange;

    // Update the cart total price
    var cartTotal = parseFloat(document.querySelector('.order-total .woocommerce-Price-amount').textContent.replace(/[^0-9.-]+/g, ""));
    var newCartTotal = cartTotal - priceChangeAmount;
    document.querySelector('.order-total .woocommerce-Price-amount bdi').textContent = newCartTotal.toLocaleString("jp-JP", {style:"currency", currency:"JPY"});
    });
    }

    </script>

        <?php
}

add_action('woocommerce_checkout_create_order', 'update_order_total', 20, 2);
function update_order_total($order, $data) {
    if (isset($_POST['monthlyfee_payment_option'])) {
        $monthlyfee_payment_option = sanitize_text_field($_POST['monthlyfee_payment_option']);
        $price_change_amount = 0;

        switch ($monthlyfee_payment_option) {
            case 'stripe':
                $price_change_amount = 30;
                break;
            case 'smartpit':
                $price_change_amount = 0;
                break;
            case 'bank-transfer-gmo':
                $price_change_amount = 180;
                break;
            case 'choose-later':
                $price_change_amount = 0;
                break;
            default:
                $price_change_amount = 0;
        }

        $order_total = $order->get_total();
        $new_order_total = $order_total - $price_change_amount;
        $order->set_total($new_order_total);
    }
}

add_action('wp_footer', 'add_custom_datepicker_script');

// Custom Validations for Custom Fields
function custom_field_validation() {
    // Check if the custom fields are empty
    if ( ! isset( $_POST['registration_code'] ) || empty( $_POST['registration_code'] ) ) {
        wc_add_notice( __( 'Registration Code is a required field.', 'woocommerce' ), 'error' );
    }

    if ( ! isset( $_POST['sakura_campaign'] ) || empty( $_POST['sakura_campaign'] ) ) {
        wc_add_notice( __( 'Sakura Campaign is a required field.', 'woocommerce' ), 'error' );
    }

    if ( ! isset( $_POST['id_upload_choice'] ) || empty( $_POST['id_upload_choice'] ) ) {
        wc_add_notice( __( 'ID Upload Choice is a required field.', 'woocommerce' ), 'error' );
    }

    if ( ! isset( $_POST['delivery_date'] ) || empty( $_POST['delivery_date'] ) ) {
        wc_add_notice( __( 'Delivery Date is a required field.', 'woocommerce' ), 'error' );
    }

    if ( ! isset( $_POST['monthlyfee_payment_option'] ) || empty( $_POST['monthlyfee_payment_option'] ) ) {
        wc_add_notice( __( 'Monthly Fee Payment Option is a required field.', 'woocommerce' ), 'error' );
    }

    if ( ! isset($_POST['address_image_field']) || empty( $_POST['address_image_field'] ) ) {
        wc_add_notice( __( 'Address Image is a required field.', 'woocommerce' ), 'error' );
    }
}
add_action( 'woocommerce_checkout_process', 'custom_field_validation' );

// Add Custom T&C Checkbox
add_action( 'woocommerce_review_order_before_submit', 'add_checkout_privacy_policy', 9 );
function add_checkout_privacy_policy() {
    woocommerce_form_field( 'privacy_policy', array(
        'type'          => 'checkbox',
        'class'         => array('form-row privacy'),
        'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
        'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
        'required'      => true,
        'label'         => 'Do you agree with the Terms & Conditions?',
    )); 
}

// Validate Terms & Conditions Checkbox
add_action('woocommerce_checkout_process', 'validate_terms_and_conditions_checkbox');
function validate_terms_and_conditions_checkbox() {
    if (!isset($_POST['privacy_policy']) || empty($_POST['privacy_policy'])) {
        wc_add_notice('Please agree to the Terms & Conditions.', 'error');
    }
}

// Display custom monthly fee payment method in order details
add_action('woocommerce_thankyou', 'display_custom_order_meta_in_checkout_success', 20);

function display_custom_order_meta_in_checkout_success($order_id) {
    $custom_data = get_post_meta($order_id, 'monthlyfee_payment_option', true);
    $final = '';

    switch($custom_data){
        case 'stripe':
            $final = 'How to pay the monthly fee Via Credit/Debit Card or Convenience Store (Stripe)';
            break;
        case 'smartpit':
            $final = 'How to pay the monthly fee Via Convenience Store (Smartpit)';
            break;
        case 'bank-transfer-gmo':
            $final = 'How to pay the monthly fee Via Bank Transfer (GMO)';
            break;
        case 'choose-later':
            $final = '';
            break;
        default:
            $final = '';
    }

    if ($final) {
        echo '<div class="monthly-fee-payment">';
        echo '<h3>'; 
        echo '<span class="payment-method">'. esc_html($final) .'</span>';
        echo '</h3><br/>';
        echo '<div id="payment-method-tutorial"></div>';
        echo '</div>';
    }

    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var headingForPaymentMethod = document.querySelector('.monthly-fee-payment');
            var paymentMethod = document.querySelector('.payment-method');

            function updatePaymentContent(method) {
                var paymentContent = '';
                var links = [
                    'https://www.youtube.com/embed/P6BdaR8BThc',
                    'https://www.youtube.com/embed/ITy3badQNYY',
                    'https://www.youtube.com/embed/GLB02i_mqVw',
                    'https://www.youtube.com/embed/p8Ev7MFOkl8',
                    'https://www.youtube.com/embed/LItELN8vL-g'
                ];

                switch(method) {
                    case 'How to pay the monthly fee Via Credit/Debit Card or Convenience Store (Stripe)':
                        paymentContent = '<button class="language-accordion">English</button><div class="panel"><iframe class="youtube-tutorial" frameborder="0" src="' + links[2] + '"></iframe></div><button class="language-accordion">Indonesian</button><div class="panel"><iframe class="youtube-tutorial" frameborder="0" src="' + links[4] + '"></iframe></div>';                        
                        break;
                    case 'How to pay the monthly fee Via Convenience Store (Smartpit)':
                        paymentContent = '<button class="language-accordion">English</button><div class="panel"><iframe class="youtube-tutorial" frameborder="0" src="' + links[0] + '"></iframe></div>';
                        break;
                    case 'How to pay the monthly fee Via Bank Transfer (GMO)':
                        paymentContent = '<button class="language-accordion">English</button><div class="panel"><iframe class="youtube-tutorial" frameborder="0" src="' + links[1] + '"></iframe></div><button class="language-accordion">Indonesian</button><div class="panel"><iframe class="youtube-tutorial" frameborder="0" src="' + links[3] + '"></iframe></div>';
                        break;
                    case 'Choose Later':
                        paymentContent = '';
                        break;
                    default:
                        paymentContent = '';
                }

                document.getElementById('payment-method-tutorial').innerHTML = paymentContent;

                
                var mainacc = document.getElementsByClassName('language-accordion');
                var i;

                for (i = 0; i < mainacc.length; i++) {
                    mainacc[i].addEventListener("click", function() {
                        this.classList.toggle("active");
                        var panel = this.nextElementSibling;
                        if (panel.style.maxHeight) {
                            panel.style.maxHeight = null;
                        } else {
                            panel.style.maxHeight = panel.scrollHeight + "px";
                        } 
                    });
                }

            }

            if (paymentMethod) {
                var initialPaymentMethod = paymentMethod.innerText.trim();
                updatePaymentContent(initialPaymentMethod);

                paymentMethod.addEventListener('change', function() {
                    var selectedPaymentMethod = paymentMethod.innerText.trim();
                    updatePaymentContent(selectedPaymentMethod);
                });
            }
        });
    </script>

    <?php
    echo '<br/>';
}


add_filter( 'wc_get_template', 'hide_order_recieved_customer_details', 10 , 1 );
function hide_order_recieved_customer_details( $template_name ) {
    // Targeting thankyou page and the customer details
    if( is_wc_endpoint_url( 'order-received' ) && strpos($template_name, 'order-details-customer.php') !== false ) {
        return false;
    }
    return $template_name;
}

// Add Custom Fields Data Rows In Order Details After Checkout
add_action('woocommerce_get_order_item_totals', 'add_custom_order_item_row', 10, 3);
function add_custom_order_item_row($total_rows, $order, $tax_display) {
    $order_id = $order->get_id();
    $tmf = get_post_meta($order_id, 'total_monthly_fee', true);
    $custom_data = get_post_meta($order_id, 'monthlyfee_payment_option', true);
    $final = '';
    $discountValue = 0;

    $new_total_rows = array();

    switch($custom_data){
        case 'stripe':
            $final = 'Credit Card (Stripe)';
            $discountValue = 30;
            break;
        case 'smartpit':
            $final = 'Convenience Store (Smartpit)';
            break;
        case 'bank-transfer-gmo':
            $final = 'Bank Transfer (GMO)';
            $discountValue = 180;
            break;
        case 'choose-later':
            $final = 'Choose Later';
            break;
        default:
            $final = '';
    }

    $tmf = '¥' . $tmf;
    $discountValue = '-¥' . number_format($discountValue, 0, '.', ',');

    foreach ($total_rows as $key => $value) {
        if ($key === 'order_total') {

            // Add the custom row before the "Total" row
            $new_total_rows['total_monthly_fee'] = array(
                'label' => __('Total Monthly Fee', 'your-text-domain'),
                'value' => $tmf,
            );

            // Add the custom row before the "Total" row
            $new_total_rows['monthly_fee_payment_method'] = array(
                'label' => __('Monthly Fee Payment Method', 'your-text-domain'),
                'value' => $final,
            );

            // Add the "Discount" row if the payment method is credit card or bank transfer
            if ($custom_data === 'stripe' || $custom_data === 'bank-transfer-gmo') {
                $new_total_rows['discount'] = array(
                    'label' => __('Discount', 'your-text-domain'),
                    'value' => $discountValue,
                );
            }
        }
        $new_total_rows[$key] = $value;
    }

    return $new_total_rows;
}

// Add video link to customer's email based on the payment method
add_action('woocommerce_email_order_meta', 'add_payment_method_video_link', 25, 4);
function add_payment_method_video_link($order, $sent_to_admin, $plain_text, $email) {
        $order_id = $order->get_id();
        $payment_method = get_post_meta($order_id, 'monthlyfee_payment_option', true);
        
        $video_links = array(
            'stripe' => array(
                'English' => 'https://www.youtube.com/watch?v=GLB02i_mqVw',
                'Indonesian' => 'https://www.youtube.com/watch?v=LItELN8vL-g',
            ),
            'smartpit' => array(
                'English' => 'https://www.youtube.com/watch?v=P6BdaR8BThc',
            ),
            'bank-transfer-gmo' => array(
                'English' => 'https://www.youtube.com/watch?v=ITy3badQNYY',
                'Indonesian' => 'https://www.youtube.com/watch?v=p8Ev7MFOkl8',
            ),
        );
        
        $payment_content = '';

        if (isset($video_links[$payment_method])) {
            $payment_content .= '<h3>Monthly Fee Payment Method Tutorial Video</h3><br/><p>Please refer to the Video Tutorials Below for more information.</p>';
            
            foreach ($video_links[$payment_method] as $language => $link) {
                $payment_content .= '<p><strong>' . $language . ':</strong> <a href="' . $link . '" target="_blank">' . $link . '</a></p>';
            }
        }

        $payment_content .= 'Note: This is an automated email, please do not reply to this email.<br/>If you have any questions, please contact us through here:<br/>Facebook: <a href="https://www.facebook.com/MashUpRSupportPage" target="_blank">facebook.com/MashUpRSupportPage</a><br/>WhatsApp: <a href="https://www.wa.me/818071543261" target="_blank">+81 80 7154 3261</a><br/>LINE: <a href="https://line.me/R/ti/p/976hvcff" target="_blank">@976hvcff</a><br/><br/>';
            
        echo $payment_content;
}

add_action( 'woocommerce_email_customer_details', 'removing_customer_details_in_emails', 5, 4 );
// Removing Customer Details in Emails
function removing_customer_details_in_emails( $order, $sent_to_admin, $plain_text, $email ){
    $wmail = WC()->mailer();
    remove_action( 'woocommerce_email_customer_details', array( $wmail, 'email_addresses' ), 20, 3 );
}

// Change Description before Order Details at Email
add_action( 'woocommerce_email_before_order_table', 'custom_processing_message', 10, 2 );
function custom_processing_message( $order, $sent_to_admin ) {
    echo '<br/>We will contact you during working hours to process your order.<br/>';
}

// Change Footer Text at Email
add_filter( 'woocommerce_email_footer', 'change_email_footer_text', 10, 2 );
function change_email_footer_text( $footer_text ) {
    $new_footer_text = "Thank you for trusting Sakura SIM!";
    return $new_footer_text;
}