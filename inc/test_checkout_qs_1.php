<?php



// Custom checkout step function

add_action('woocommerce_checkout_before_customer_details', 'custom_checkout_step');

function custom_checkout_step() {

        // Check for 'seller_code' parameter in the URL

        $seller_code = isset($_GET['seller_code']) ? sanitize_text_field($_GET['seller_code']) : '';

        preselect_based_on_url_parameter_and_persist();



        // Query the ACF Custom Post Type "registration-code"

        $args = array(

            'post_type' => 'registration-code',

            'posts_per_page' => -1,

        );

        $query = new WP_Query($args);



        $seller_code_exists = false;

        if ($query->have_posts()) {

            while ($query->have_posts()) {

                $query->the_post();

                $code_title = get_the_title();

                if ($code_title == $seller_code) {

                    $seller_code_exists = true;

                    break;

                }

            }

        }

        wp_reset_postdata();



        // Output the registration code selection

        ?>

        <div id="custom-checkout-step">

            <h2>Questions</h2>

            <p>Answer the following questions:</p>



            <!-- Registration Code Selection -->

            <?php if ($seller_code_exists) : ?>

                <label for="registration_code">Registration Code </label>

                <input type="text" id="registration_code_name" name="registration_code_name" value="<?php echo esc_attr($seller_code); ?>" disabled/>

                <input type="hidden" id="registration_code" name="registration_code" value="<?php echo esc_attr($seller_code); ?>" />

            <?php else : ?>

                <label for="registration_code">Choose Your Registration Code <span class="required">Required</span></label>

                <select id="registration_code" name="registration_code" required>

                    <option value="" disabled selected>Select An Option</option>

                    <?php

                    if ($query->have_posts()) {

                        while ($query->have_posts()) {

                            $query->the_post();

                            $code_title = get_the_title();

                            echo '<option value="' . esc_attr($code_title) . '">' . esc_html($code_title) . '</option>';

                        }

                    }

                    ?>

                    <option value="website">Website</option>

                    <option value="none">None</option>

                </select>

            <?php endif; ?>

        </div>



        <!-- 1.2 Friend's Information -->

        <br/>

        <div class="friend_info">Note : <u>In order to get gift & reward from your order, please ask your friend's information so that we will contact them.</u></div><br/>





        <!-- 1.3 How did you see our Sakura SIM Campaign? -->

        <label for="sakura_campaign">Hello! How did you see our Sakura SIM Campaign? <span class="required">Required</span></label>

        <select id="sakura_campaign" name="sakura_campaign" required>

            <option value="none" disabled selected>Select An Option</option>

            <option value="email">Email</option>

            <option value="line">Line</option>

            <option value="facebook_friend">Facebook Friend</option>

            <option value="facebook_page">Facebook Page</option>

            <option value="whatsapp">Whatsapp</option>

            <option value="zalo">Zalo</option>

            <option value="upload_qr">Upload QR Code (Your friend's Line, WeChat, Whatsapp & Zalo)</option>

            <option value="website">Website</option>

        </select><br/>



        <!-- 1.4 Facebook Page (if selected in 1.3) -->

        <div id="facebook_page" style="display: none;">

            <br/>

            <label for="facebook_page_name">What's the Facebook Page?</label>

            <select id="facebook_page_name" name="facebook_page_name">
                <option value="none" disabled selected>Select An Option</option>
                <option value="Japan Smart Life Guide">Japan Smart Life Guide</option>
                <option value="Nippon Electronics Store">Nippon Electronics Store</option>
                <option value="Sakura Connection">Sakura Connection</option>
                <option value="Kazoku Internet Service">Kazoku Internet Service</option>
                <option value="Wireless Digital Japan">Wireless Digital Japan</option>
                <option value="Global Net Store">Global Net Store</option>
                <option value="Japan Internet Gadgets">Japan Internet Gadgets</option>
                <option value="Pinoy Marketing x JSLG">Pinoy Marketing x JSLG</option>
                <option value="Japan Techzone">Japan Techzone</option>
                <option value="MIX Electronics Store">MIX Electronics Store</option>
                <option value="Mua Bán Thiết Bị Điện Tử Japan">Mua Bán Thiết Bị Điện Tử Japan</option>
                <option value="Tomodachi Internet">Tomodachi Internet</option>
                <option value="AK JAPAN Electronics">AK JAPAN Electronics</option>
                <option value="Japan Expat Tech Shop">Japan Expat Tech Shop</option>
                <option value="other">Other</option>
            </select>
        </div>


        <!-- 1.5 Please Specify the Facebook Page Name (if "Other Page" selected in 1.3) -->
        <div id="facebook_page_other" style="display: none;">
            <br/>
            <label for="facebook_page_name_other">Please Specify the Facebook Page Name</label>
            <input type="text" id="facebook_page_name_other" name="facebook_page_name_other">
        </div>


        <!-- 1.6 Friend's Name -->
        <div id="friend_name" style="display: none;"><br/>
            <label for="friend_name">Please input your Friend's Name</label>
            <input type="text" id="friend_name" name="friend_name">
        </div>


        <!-- 1.7 Facebook Friend: Choose between Email or Phone Number -->
        <div id="facebook_friend_info" style="display: none;"><br/>
            <label for="friend_contact_option">For Facebook Friend, Please choose between Email or Phone Number</label>
            <select id="friend_contact_option" name="friend_contact_option">
                <option disabled selected>Select Here</option>
                <option value="email">Email</option>
                <option value="phone">Phone Number</option>
            </select>
        </div>



        <!-- 1.8 Your Friend's Email Address (if "Email" selected in 1.7) -->

        <div id="friend_email" style="display: none;">

            <br/>

            <label for="friend_email">Your Friend's Email Address</label>

            <input type="email" id="friend_email" name="friend_email">

        </div>



        <!-- 1.9 Your Friend's Phone Number (if "Phone Number" selected in 1.7) -->

        <div id="friend_phone" style="display: none;">

            <br/>

            <label for="friend_phone">Your Friend's Phone Number</label>

            <input type="tel" id="friend_phone" name="friend_phone" autocomplete="tel">

        </div>



        <!-- 1.10 Upload Your Friend's QR CODE Here -->

        <div id="friend_qr_code" style="display: none;"><br/>

            <label for="friend_qr_code">Upload Your Friend's QR CODE Here</label>

            <input type="file" name="friend_qr_code" accept="image/*" />

            <input type="hidden" name="friend_qr_code_field">

            <div id="preview_friend_qr_code"></div>

        </div>



        





    <script>

        jQuery( function($){



            $('#billing_phone').on( 'input focusout', function() {

                var p = $(this).val();

                $(this).val($(this).val().replace(/[^0-9()-\s]/g, ''));

            });



            $('#friend_phone').on('input', function() {

                $(this).val($(this).val().replace(/[^0-9()-\s]/g, ''));

            });



        });



        jQuery(document).ready(function($) {

            $('#facebook_page_name').change(function(){

                if ($(this).val() === 'other'){

                    $('#facebook_page_other').show();

                }

                else{

                    $('#facebook_page_other').hide();

                }

            })



             $('#id_upload_choice').change(function() {

                if ($(this).val() === 'upload_now') {

                    $('#upload_now_section').show();

                    $('#upload_later_section').hide();

                } else {

                    $('#upload_now_section').hide();

                    $('#upload_later_section').show();

                }

            })





            // Add change event for 'id_upload_choice' to incorporate the reference code for "Upload Now"

            $('#id_upload_choice').change(function() {

                if ($(this).val() === 'upload_now') {

                    $('#upload_now_section').show();

                    // Make file upload fields required dynamically

                    $('#front_residence_card, #back_residence_card').prop('required', true);

                } else {

                    $('#upload_now_section').hide();

                    $('#front_residence_card, #back_residence_card').prop('required', false);

                }

            });

        });



	// Get relevant DOM elements

        var sakuraCampaignSelect = document.getElementById("sakura_campaign");

        var friendNameField = document.getElementById("friend_name");

        var friendPhoneField = document.getElementById("friend_phone");

        var facebookFriendInfo = document.getElementById("facebook_friend_info");

        var friendEmailField = document.getElementById("friend_email");

        var facebookPage = document.getElementById("facebook_page");

        var facebookPageOther = document.getElementById("facebook_page_other");

        var friendQRCode = document.getElementById("friend_qr_code");



        // Function to hide all optional sections

        function hideOptionalSections() {

            var optionalSections = [friendNameField, friendPhoneField, facebookFriendInfo, friendEmailField, facebookPage, facebookPageOther, friendQRCode];

            optionalSections.forEach(function(element) {

                element.style.display = "none";

            });

        }



        // Show/hide fields based on the selected option in 1.3

        sakuraCampaignSelect.addEventListener("change", function() {

            var selectedOption = this.value;



            // Hide all optional sections first

            hideOptionalSections();



            // Show the appropriate sections based on the selected option

            if (selectedOption === "email") {

                friendEmailField.style.display = "block";

            } else if (selectedOption === "line") {

                friendNameField.style.display = "block";

                friendPhoneField.style.display = "block";

            } else if (selectedOption === "facebook_friend") {

                facebookFriendInfo.style.display = "block";

            } else if (selectedOption === "facebook_page") {

                facebookPage.style.display = "block";

            } else if (selectedOption === "whatsapp") {

                friendPhoneField.style.display = "block";

            } else if (selectedOption === "zalo") {

                friendEmailField.style.display = "block";

            } else if (selectedOption === "upload_qr") {

                friendQRCode.style.display = "block";

            }

        });



        // Show/hide fields based on the selected option in 1.7

        var friendContactOptionSelect = document.getElementById("friend_contact_option");



        friendContactOptionSelect.addEventListener("change", function() {

            var selectedOption = this.value;



            // Hide all contact-related fields first

            friendEmailField.style.display = "none";

            friendPhoneField.style.display = "none";



            // Show the appropriate contact field based on the selected option

            if (selectedOption === "email") {

                friendEmailField.style.display = "block";

            } else if (selectedOption === "phone") {

                friendPhoneField.style.display = "block";

            }

        });







        jQuery(document).ready(function($) {



        // Ajax function to upload images

        $('input[type="file"]').change(function() {

            var file = $(this)[0].files[0];

            var field_name = $(this).attr('name');

            var formData = new FormData();

            formData.append('action', 'custom_upload_images');

            formData.append('security', custom_upload_vars.nonce);

            formData.append(field_name, file);



            $.ajax({

                url: custom_upload_vars.ajax_url,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,

                success: function(response) {

                    var data = JSON.parse(response);



                    if (data.hasOwnProperty('errors')) {

                        // Display error message

                        alert(data.errors.join('\n'));

                        // Clear the file input to allow re-uploading

                        $('input[type="file"]').val('');

                        // Cancel the checkout process

                        return;

                    }



                    $.each(data, function(key, value) {

                        $('input[name="' + key + '_field"]').val(value);

                        $('#preview_' + key).html('Photo Preview:<br/><img src="' + value + '" class="preview_pic" />');

                    });

                }

            });

        });

        });

    </script>

    <?php

    // Nonce field for security
    wp_nonce_field('custom_checkout_action', 'custom_checkout_nonce_field');
}



add_filter('gettext', 'change_billing_details_text', 20, 3);

function change_billing_details_text($translated_text, $text, $domain) {
    if ($text === 'Billing details' && $domain === 'woocommerce') {
        $translated_text = __('Personal Information', 'woocommerce');
    }

    return $translated_text;

}



add_action('woocommerce_after_checkout_billing_form','upload_address_image_field');

function upload_address_image_field() {

    ?>

    <!-- 1.11 ID Upload -->
        <label for="id_upload_choice">ID Upload <span class="required">Required</span></label>

        <div class="friend_info">Note :

            <ol>

                <li>Please make sure the ID in your photo can be clearly seen.</li>

                <li>Please check your ID document expiration date.</li>

                <li>If your ID is expired, please consult with our <a href="#footer" style="color:white; background-color:#de3594; border-radius:4px; padding:4px 8px 4px 8px;">Customer Support</a></li>

                <li>We only accept image file size: 2 MB</li>

                <li>We only accept these image file types: JPG, JPEG and PNG.</li>               

            </ol>

        </div>

        <br/>

        <select id="id_upload_choice" name="id_upload_choice" required>

            <option disabled selected>Select Your Option</option>

            <option value="upload_now">Upload Now</option>

            <option value="upload_later">Upload Later</option>

        </select>


        <!-- Upload Now - Front & Back Residence Cards -->
        <div id="upload_now_section" style="display: none;">

            <div class="form-row"><br/>

                <div class="form-row-first">

                    <label for="front_residence_card">Front Residence Card</label>

                    <input type="file" id="front_residence_card" name="front_residence_card" accept="image/*">

                    <input type="hidden" name="front_residence_card_field">

                    <div class="form-row-wide">

                        <?php 

                            $frc_image = wp_get_attachment_url(1108);

                            if ( $frc_image ) {

                                echo '<div class="form-row-first">Example:<br/><img class="residence_card_example" src="' . esc_url( $frc_image ) . '" alt="Front Residence Card Example" /></div>';

                            } else {

                                echo 'front_residence_card_example Image not found';

                            }

                        ?>

                        <div class="form-row-last" id="preview_front_residence_card"></div>

                    </div>



                </div>

                <div class="form-row-last">

                    <label for="back_residence_card">Back Residence Card</label>

                    <input type="file" id="back_residence_card" name="back_residence_card" accept="image/*">

                    <input type="hidden" name="back_residence_card_field">

                    <div class="form-row form-row-wide">

                        <?php 

                            $brc_image = wp_get_attachment_url(1107);

                            if ( $brc_image ) {

                                echo '<div class="form-row-first">Example:<br/><img class="residence_card_example" src="' . esc_url( $brc_image ) . '" alt="Front Residence Card Example" /></div>';

                            } else {

                                echo 'back_residence_card_example Image not found';

                            }

                        ?>

                        <div class="form-row-last" id="preview_back_residence_card"></div>

                    </div>

                </div>

            </div><br/>

        </div>



        <!-- Upload Later -->
        <div id="upload_later_section" style="display:none;"><br/>

            <div class="friend_info">Note : <u>We will give instruction paper to upload your id after you recieve the item</u></div>

        </div>
    
        <br/>
    <div class="form-row form-row-wide">

        <label for="address_image">Upload Address Proof <span class="required">Required</span></label>

        <div class="friend_info">Note :

            <ol>

                <li>Please make sure the address in your photo can be clearly seen.</li>

                <li>We only accept image file size: 2 MB</li>

                <li>We only accept these image file types: JPG, JPEG and PNG.</li>                

            </ol>

        </div><br/>

        <input type="file" id="address_image" name="address_image" accept="image/*" required/>

        <input type="hidden" name="address_image_field">

        <div id="preview_address_image"></div>

    </div>

    



    <?php

}



// Enqueue jQuery and script

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts() {

    wp_enqueue_script('jquery');

    ?>

    <script>

        var custom_upload_vars = {

            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',

            nonce: '<?php echo wp_create_nonce('custom-upload-nonce'); ?>'

        };

    </script>

    <?php

}

// Ajax handler for uploading images
add_action('wp_ajax_custom_upload_images', 'custom_upload_images');

add_action('wp_ajax_nopriv_custom_upload_images', 'custom_upload_images');

function custom_upload_images() {

    check_ajax_referer('custom-upload-nonce', 'security');

    

    // Start the session if it hasn't been started already

    if (session_status() == PHP_SESSION_NONE) {

        session_start();

    }

    

    if (isset($_FILES) && !empty($_FILES)) {

        $files = $_FILES;

        $upload_overrides = array('test_form' => false);

        $uploaded_files = array();

        $errors = array();

        

        foreach ($files as $field_name => $file) {

            // Check file size

            if ($file['size'] > 2 * 1024 * 1024) { // 2 MB in bytes

                $errors[] = 'File size exceeds the limit of 2 MB. Please upload a photo that is smaller than 2 MB';

                continue; // Skip processing this file

            }

            

            // Check file type

            $allowed_types = array('image/jpeg', 'image/png', 'image/jpg');

            if (!in_array($file['type'], $allowed_types)) {

                $errors[] = 'Invalid file type. Only JPG, JPEG and PNG files are allowed.';

                continue; // Skip processing this file

            }

            

            $file_info = wp_handle_upload($file, $upload_overrides);

            if ($file_info && !isset($file_info['error'])) {

                // Store the uploaded file URL in the session

                $_SESSION['custom_upload_files'][$field_name] = $file_info['url'];

                

                // Debug: Check if the URL is being stored in the session

                error_log('Stored in Session: ' . $field_name . ' = ' . $_SESSION['custom_upload_files'][$field_name]);

                

                // Store the uploaded file URL for display

                $uploaded_files[$field_name] = $file_info['url'];

            } else {

                $errors[] = 'Error uploading file: ' . $file_info['error'];

            }

        }

        

        if (!empty($errors)) {

            // Return errors

            echo json_encode(array('errors' => $errors));

            wp_die();

        } else {

            // Return the URLs for display

            echo json_encode($uploaded_files);

        }

    }

    wp_die();

}

// Hook to save the custom checkout fields

add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_fields');

function save_custom_checkout_fields($order_id) {

    if (!isset($_POST['custom_checkout_nonce_field']) || !wp_verify_nonce($_POST['custom_checkout_nonce_field'], 'custom_checkout_action')) {
        return;
    }

    // Save each custom field, check if $_POST is set before saving

    if (isset($_POST['delivery_date'])) {
        update_post_meta($order_id, 'delivery_date', sanitize_text_field($_POST['delivery_date']));
    }

    if (isset($_POST['delivery_time'])) {
        update_post_meta($order_id, 'delivery_time', sanitize_text_field($_POST['delivery_time']));

    }

    if (isset($_POST['total_monthly_fee'])) {
        update_post_meta($order_id, 'total_monthly_fee', sanitize_text_field($_POST['total_monthly_fee']));

    }

    if (isset($_POST['registration_code'])) {
        update_post_meta($order_id, 'registration_code', sanitize_text_field($_POST['registration_code']));
    }

    if (isset($_POST['sakura_campaign'])) {
        update_post_meta($order_id, 'sakura_campaign', sanitize_text_field($_POST['sakura_campaign']));
    }

    if (isset($_POST['facebook_page_name'])) {
        update_post_meta($order_id, 'facebook_page_name', sanitize_text_field($_POST['facebook_page_name']));
    }

    if (isset($_POST['facebook_page_name_other'])) {
        update_post_meta($order_id, 'facebook_page_name_other', sanitize_text_field($_POST['facebook_page_name_other']));
    }

    if (isset($_POST['friend_name'])) {
        update_post_meta($order_id, 'friend_name', sanitize_text_field($_POST['friend_name']));
    }

    if (isset($_POST['friend_contact_option'])) {
        update_post_meta($order_id, 'friend_contact_option', sanitize_text_field($_POST['friend_contact_option']));

    }

    if (isset($_POST['friend_email'])) {
        update_post_meta($order_id, 'friend_email', sanitize_email($_POST['friend_email']));
    }

    if (isset($_POST['friend_phone'])) {
        update_post_meta($order_id, 'friend_phone', sanitize_text_field($_POST['friend_phone']));
    }

    if (isset($_POST['monthlyfee_payment_option'])) {
        update_post_meta($order_id, 'monthlyfee_payment_option', sanitize_text_field($_POST['monthlyfee_payment_option']));
    }



    // Save uploaded file URLs from the session to the order meta

    if (isset($_SESSION['custom_upload_files'])) {
        foreach ($_SESSION['custom_upload_files'] as $field_name => $url) {
            update_post_meta($order_id, $field_name, $url);
            // Debug: Check if the URL is being saved to the order meta
            error_log('Saved to Order Meta: ' . $field_name . ' = ' . $url);
        }

        // Clear the session data after saving to the order meta
        unset($_SESSION['custom_upload_files']);
    }

    if (isset($_POST['friend_qr_code_field'])) {
        update_post_meta($order_id, 'friend_qr_code', sanitize_text_field($_POST['friend_qr_code_field']));

    }

    if (isset($_POST['front_residence_card_field'])) {
        update_post_meta($order_id, 'front_residence_card', sanitize_text_field($_POST['front_residence_card_field']));

    }

    if (isset($_POST['back_residence_card_field'])) {
        update_post_meta($order_id, 'back_residence_card', sanitize_text_field($_POST['back_residence_card_field']));

    }

    if (isset($_POST['address_image_field'])) {
        update_post_meta($order_id, 'address_image', sanitize_text_field($_POST['address_image_field']));

    }

    return $order_id;

}

// Save Total Monthly Fee To Order
add_action('woocommerce_checkout_update_order_meta', 'save_total_monthly_fee_to_order');
function save_total_monthly_fee_to_order($order_id) {

    $order = wc_get_order($order_id);

    // Calculate Total Monthly Fee
    $total_monthly_fee = 0;

    foreach ($order->get_items() as $item_id => $item) {
        $monthly_fee = $item->get_meta('monthly_fee', true);

        if ($monthly_fee) {
            $total_monthly_fee += (float) $monthly_fee;
        }

    }

    // Format Total Monthly Fee with commas as thousands separator
    $final_mf = number_format($total_monthly_fee, 0, '.', ',');

    // Save Total Monthly Fee as a custom field in the order
    $order->update_meta_data('total_monthly_fee', $final_mf);
    $order->save();

}

// Custom Webhook Response
add_action('woocommerce_order_status_processing', 'send_order_processed_webhook', 10, 1);

function send_order_processed_webhook($order_id) {
    $order = wc_get_order($order_id);

    $formatted_order = array(
        'order_id' => $order->get_id(),
        'registration_code' => $order->get_meta('registration_code'),
        'order_number' => $order->get_order_number(),
        'order_status' => $order->get_status(),
        'order_total' => $order->get_total(),
        'first_name' => $order->get_billing_first_name(),
        'last_name' => $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'phone' => $order->get_billing_phone(),
        "status" => $order->get_status(),
        "date_created" => $order->get_date_created()->format('Y-m-d H:i:s'),
        "date_modified" => $order->get_date_modified()->format('Y-m-d H:i:s'),
        "date_paid" => $order->get_date_paid() ? $order->get_date_paid()->format('Y-m-d H:i:s') : '',
        'delivery_date' => $order->get_meta('delivery_date'),
        'delivery_time' => $order->get_meta('delivery_time'),
        'total' => $order->get_total(),
        'total_monthly_fee' => $order->get_meta('total_monthly_fee'),
        'monthlyfee_payment_option' => $order->get_meta('monthlyfee_payment_option'),
        'address_image' => $order->get_meta('address_image'),
        'front_residence_card' => $order->get_meta('front_residence_card'),
        'back_residence_card' => $order->get_meta('back_residence_card'),
        'friends' => array(     
            'sakura_campaign' => $order->get_meta('sakura_campaign'),
            'friend_name' => $order->get_meta('friend_name'),
            'friend_contact_option' => $order->get_meta('friend_contact_option'),
            'friend_email' => $order->get_meta('friend_email'),
            'friend_phone' => $order->get_meta('friend_phone'),
            'facebook_page_name' => $order->get_meta('facebook_page_name'),
            'facebook_page_name_other' => $order->get_meta('facebook_page_name_other'),
            'friend_qr_code' => $order->get_meta('friend_qr_code'),
        ),
        "customer_ip_address" => $order->get_customer_ip_address(),
        "customer_user_agent" => $order->get_customer_user_agent(),
        "cart_hash" => $order->get_cart_hash(),
        "line_items" => array(),
        "is_editable" => $order->is_editable()
    );

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $selected_gift_id = $item->get_meta('_selected_gift', true);
        $selected_gift_name = $item->get_meta('selected_gift_name', true);
        $monthly_fee = $product->get_meta('monthly_fee', true);
        $applydiscount = $item->get_meta('apply_discount', true) == 1 ? 'true' : 'false';
    
        $formatted_order['line_items'][] = array(
            'item_id' => $item_id,
            'name' => $product->get_name(),
            'product_id' => $product->get_id(),
            'selected_gift' => $selected_gift_id,
            'selected_gift_name' => $selected_gift_name,
            'monthly_fee' => $monthly_fee,
            'quantity' => $item->get_quantity(),
            'subtotal' => $order->get_line_subtotal($item),
            'apply_discount' => $applydiscount,
            'total' => $order->get_line_total($item),
        );
    }

    $payload_json = json_encode($formatted_order);

    // Send the webhook payload
    $webhook_url = 'https://webhook.site/cc991b86-f86a-45d2-be8d-02f021561653';
    $args = array(
        'body' => $payload_json,
        'headers' => array(
            'Content-Type' => 'application/json'
        )
    );
    $response = wp_remote_post($webhook_url, $args);

    // Log the response
    if (is_wp_error($response)) {
        error_log('Webhook Error: ' . $response->get_error_message());
    } else {
        error_log('Webhook Response: ' . wp_remote_retrieve_body($response));
    }
}


// Display Saved Custom Field Data on the Order Page
add_action('woocommerce_admin_order_data_after_order_details', 'display_custom_order_meta_in_admin');

function display_custom_order_meta_in_admin($order){

    echo '<div class="order_data_column">';

    error_log('Friend QR Code URL (retrieved): ' . get_post_meta($order->get_id(), 'friend_qr_code', true));

    error_log('Front Residence Card URL (retrieved): ' . get_post_meta($order->get_id(), 'front_residence_card', true));

    error_log('Back Residence Card URL (retrieved): ' . get_post_meta($order->get_id(), 'back_residence_card', true));

    error_log('Address Image URL (retrieved): ' . get_post_meta($order->get_id(), 'address_image', true));



    // Display registration code

    $registration_code = get_post_meta($order->get_id(), 'registration_code', true);
    echo '<p><strong>Registration Code:</strong> ' . (!empty($registration_code) ? esc_html($registration_code) : '-') . '</p>';

    // Display Sakura campaign source

    $sakura_campaign = get_post_meta($order->get_id(), 'sakura_campaign', true);

    echo '<p><strong>Sakura Campaign Source:</strong> ' . (!empty($sakura_campaign) ? esc_html($sakura_campaign) : '-') . '</p>';



    // Display Facebook page name

    $facebook_page_name = get_post_meta($order->get_id(), 'facebook_page_name', true);

    echo '<p><strong>Facebook Page Selection:</strong> ' . (!empty($facebook_page_name) ? esc_html($facebook_page_name) : '-') . '</p>';



    // Display Other Facebook Page Name

    $other_facebook_page_name = get_post_meta( $order->get_id(), 'facebook_page_name_other', true );

    echo '<p><strong>Other Facebook Page Name:</strong> ' . (!empty($other_facebook_page_name) ? esc_html($other_facebook_page_name) : '-') . '</p>';



    // Display Friend's Name

    $friend_name = get_post_meta($order->get_id(), 'friend_name', true);

    echo '<p><strong>Friend\'s Name:</strong> ' . (!empty($friend_name) ? esc_html($friend_name) : '-') . '</p>';



    // Display Friend's Email Address

    $friend_email = get_post_meta($order->get_id(), 'friend_email', true);

    echo '<p><strong>Friend\'s Email Address:</strong> ' . (!empty($friend_email) ? esc_html($friend_email) : '-') . '</p>';



    // Display Friend's Phone Number

    $friend_phone = get_post_meta($order->get_id(), 'friend_phone', true);

    echo '<p><strong>Friend\'s Phone Number:</strong> ' . (!empty($friend_phone) ? esc_html($friend_phone) : '-') . '</p>';



    // Display Friend's Contact Option

    $friend_contact_option = get_post_meta($order->get_id(), 'friend_contact_option', true);

    echo '<p><strong>Friend\'s Contact Option:</strong> ' . (!empty($friend_contact_option) ? esc_html($friend_contact_option) : '-') . '</p>';



    // Display Friend's QR Code

    $friend_qr_code_url = get_post_meta($order->get_id(), 'friend_qr_code', true);

    echo '<p><strong>Friend\'s QR Code:</strong> ' . (!empty($friend_qr_code_url) ? '<img src="' . esc_url($friend_qr_code_url) . '" style="max-width: 200px; max-height: 200px;" />' : '-') . '</p>';

    

    // Display Front Residence Card

    $front_residence_card_url = get_post_meta($order->get_id(), 'front_residence_card', true);

    echo '<p><strong>Front Residence Card:</strong> ' . (!empty($front_residence_card_url) ? '<img src="' . esc_url($front_residence_card_url) . '" style="max-width: 200px; max-height: 200px;" />' : '-') . '</p>';

    

    // Display Back Residence Card

    $back_residence_card_url = get_post_meta($order->get_id(), 'back_residence_card', true);

    echo '<p><strong>Back Residence Card:</strong> ' . (!empty($back_residence_card_url) ? '<img src="' . esc_url($back_residence_card_url) . '" style="max-width: 200px; max-height: 200px;" />' : '-') . '</p>';



    // Display Total Monthly Fee

    $total_monthly_fee = 0;

    foreach ($order->get_items() as $item_id => $item) {

        $monthly_fee = $item->get_meta('monthly_fee');

        if ($monthly_fee) {

            $total_monthly_fee += $monthly_fee;

        }

    }



    if ($total_monthly_fee) {

        echo '<p>';

        echo '<strong>' . __('Total Monthly Fee:', 'woocommerce') . '</strong><br/>';

        echo '' . wc_price($total_monthly_fee) . '';

        echo '</p>';

    }



    echo '</div>';

}