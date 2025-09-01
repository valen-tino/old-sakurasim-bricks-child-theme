<?php


add_filter( 'rest_authentication_errors', 'restrict_wc_v3_to_logged_in_users' );

function restrict_wc_v3_to_logged_in_users( $result ) {
    // Check if the request is for the wc/v3 endpoint
    $request = $_SERVER['REQUEST_URI'];
    if (strpos($request, '/wp-json/wc/v3') !== false) {
        // If user is not logged in, return a rest_forbidden error
        if ( ! is_user_logged_in() ) {
            return new WP_Error( 'rest_forbidden', __( 'Sorry, you are not allowed to access this endpoint.' ), array( 'status' => rest_authorization_required_code() ) );
        }
    }

    // If everything is okay, return the result unchanged
    return $result;
}

// Custom Rest API Routes

add_action( 'rest_api_init', function () {

    // Route to uget each order details by order id
    register_rest_route('wc/v3', '/orders/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'modify_each_order_data',
        'permission_callback' => function($request){      
            return is_user_logged_in();
        },
        'args' => array(
            'id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));

    // route to get all orders with the customized details
    register_rest_route('wc/v3', '/orders', array(
        'methods' => 'GET',
        'callback' => 'modify_order_data',
        'permission_callback' => function($request) {
            return is_user_logged_in();
        }
    ));


	// Route to call all products & their details
    register_rest_route( 'wc/v3', '/products', array(

        'methods' => 'GET',

        'callback' => 'custom_products_endpoint_handler',

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ) );


	// Route for each product details
    register_rest_route( 'wc/v3', '/products/(?P<id>\d+)', array(

        'methods' => 'GET',

        'callback' => 'custom_product_detail_handler',

        'args' => array(

            'id' => array(

                'validate_callback' => function($param, $request, $key) {

                    return is_numeric($param);

                }

            ),

        ),

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ) );


	// Route for 'data-plan' category products
    register_rest_route( 'wc/v3', '/products/data-plan', array(

        'methods' => 'GET',

        'callback' => 'custom_products_data_plan_handler',

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ) );

	// Route for 'data-plan' based on the slug
    register_rest_route( 'wc/v3', '/products/data-plan/(?P<slug>[a-zA-Z0-9-]+)', array(

        'methods' => 'GET',

        'callback' => 'custom_products_data_plan_by_slug_handler',

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ) );

    // Route for 'gifts' category products
    register_rest_route( 'wc/v3', '/products/gifts', array(

        'methods' => 'GET',

        'callback' => 'custom_products_gifts_handler',

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ) );

	// Route to get 'gifts' based on the slug
	register_rest_route('wc/v3', '/products/gifts/(?P<slug>[a-zA-Z0-9-]+)', array(

        'methods' => 'GET',

        'callback' => 'custom_products_gifts_by_slug_handler',

        'args' => array(

            'slug' => array(

                'validate_callback' => function($param, $request, $key) {

                    return is_string($param);

                }

            ),

        ),

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ));

	// Route to update Gifts Product Stock by using Slug
	register_rest_route('wc/v3', '/products/gifts/update/(?P<slug>[a-zA-Z0-9-]+)', array(

        'methods' => 'POST',

        'callback' => 'update_product_stock_status_by_slug',

        'args' => array(

            'slug' => array(

                'validate_callback' => function($param, $request, $key) {

                    return is_string($param);

                }

            ),

            'stock_status' => array(

                'required' => true,

                'validate_callback' => function($param, $request, $key) {

                    return in_array($param, ['instock', 'outofstock', 'onbackorder']);

                }

            ),

        ),

        'permission_callback' => function($request){      

      		return is_user_logged_in();

    	}

    ));	

    // Route to update data plan details by slug
    register_rest_route('wc/v3', '/products/data-plan/update/(?P<slug>[a-zA-Z0-9-]+)', array(

        'methods' => 'POST',

        'callback' => 'update_data_plan_details',

        'args' => array(

            'slug' => array(

                'validate_callback' => function($param, $request, $key) {

                    return is_string($param);

                }

            ),

            'new_price' => array(

                'required' => false,

                'validate_callback' => function($param, $request, $key) {

                    return is_numeric($param);

                }

            ),

            'sale_price' => array(

                'required' => false,

                'validate_callback' => function($param, $request, $key) {

                    return is_numeric($param) || $param == '';

                }

            ),

            'monthly_fee' => array(

                'required' => false,

                'validate_callback' => function($param, $request, $key) {

                    return is_numeric($param);

                }

            ),

            'stock_status' => array(

                'required' => false,

                'validate_callback' => function($param, $request, $key) {

                    return in_array($param, ['instock', 'outofstock', 'onbackorder']);

                }

            ),

        ),

        'permission_callback' => function($request){      

            return current_user_can('edit_posts');

        }

    ));


} );


function modify_each_order_data($request) {
    $order_id = $request->get_param('id');
    $order = wc_get_order($order_id);

    if (!$order) {
        return new WP_Error('no_order_found', 'No order found with the provided ID', array('status' => 404));
    }

    $modified_order = array(
        'order_id' => $order->get_id(),
        'registration_code' => $order->get_meta('registration_code'),
        'sku' => '',
        'order_key' => $order->get_order_key(),
        'customer_id' => $order->get_customer_id(),
        'first_name' => $order->get_billing_first_name(),
        'last_name' => $order->get_billing_last_name(),
        'address_image' => $order->get_meta('address_image'),
        'email' => $order->get_billing_email(),
        'phone' => $order->get_billing_phone(),
        'front_residence_card' => $order->get_meta('front_residence_card'),
        'back_residence_card' => $order->get_meta('back_residence_card'),
        'delivery_date' => $order->get_meta('delivery_date'),
        'total' => $order->get_total(),
        'total_monthly_fee' => $order->get_meta('total_monthly_fee'),
        'price' => '',
        'status' => $order->get_status(),
        'date_created' => $order->get_date_created()->format('Y-m-d H:i:s'),
        'date_modified' => $order->get_date_modified()->format('Y-m-d H:i:s'),
        'date_paid' => $order->get_date_paid() ? $order->get_date_paid()->format('Y-m-d H:i:s') : '',
        'customer_ip_address' => $order->get_customer_ip_address(),
        'customer_user_agent' => $order->get_customer_user_agent(),
        'cart_hash' => $order->get_cart_hash(),
        'friend' => array(
            'sakura_campaign' => $order->get_meta('sakura_campaign'),
            'friend_name' => $order->get_meta('friend_name'),
            'friend_email' => $order->get_meta('friend_email'),
            'friend_phone' => $order->get_meta('friend_phone'),
            'facebook_page_name_other' => $order->get_meta('facebook_page_name_other'),
            'friend_qr_code' => $order->get_meta('friend_qr_code'),
        ),
        'line_items' => array(),
        'is_editable' => $order->is_editable(),
    );

    foreach ($order->get_items() as $item_id => $item) {
        $modified_order['line_items'][] = array(
            'item_id' => $item->get_id(),
            'name' => $item->get_name(),
            'product_id' => $item->get_product_id(),
            'selected_gift' => $item->get_meta('_selected_gift'),
            'selected_gift_name' => $item->get_meta('selected_gift_name'),
            'monthly_fee' => $item->get_meta('monthly_fee'),
            'quantity' => $item->get_quantity(),
            'subtotal' => $item->get_subtotal(),
            'total' => $item->get_total(),
        );
    }

    return $modified_order;
}

// Callback function to modify order data
function modify_order_data( WP_REST_Request $request) {
    $orders = wc_get_orders(array(
        'status' => 'processing', // Modify this to suit your needs
        'limit' => -1, // Get all orders
    ));

    $formatted_orders = array();

    foreach ($orders as $order) {
        $formatted_order = array(
            'order_id' => $order->get_id(),
            'registration_code' => get_post_meta($order->get_id(), 'registration_code', true),
            'sku' => '',
            'order_key' => $order->get_order_key(),
            'customer_id' => $order->get_customer_id(),
            'first_name' => $order->get_billing_first_name(),
            'last_name' => $order->get_billing_last_name(),
            'address_image' => get_post_meta($order->get_id(), 'address_image', true),
            'email' => $order->get_billing_email(),
            'phone' => $order->get_billing_phone(),
            'front_residence_card' => get_post_meta($order->get_id(), 'front_residence_card', true),
            'back_residence_card' => get_post_meta($order->get_id(), 'back_residence_card', true),
            'delivery_date' => get_post_meta($order->get_id(), 'delivery_date', true),
            'total' => $order->get_total(),
            'total_monthly_fee' => get_post_meta($order->get_id(), 'total_monthly_fee', true),
            'price' => '',
            'status' => $order->get_status(),
            'date_created' => $order->get_date_created(),
            'date_modified' => $order->get_date_modified(),
            'date_paid' => $order->get_date_paid() ? $order->get_date_paid() : null,
            'customer_ip_address' => $order->get_customer_ip_address(),
            'customer_user_agent' => $order->get_customer_user_agent(),
            'cart_hash' => $order->get_cart_hash(),
            'friend' => array(
                'sakura_campaign' => get_post_meta($order->get_id(), 'sakura_campaign', true),
                'friend_name' => get_post_meta($order->get_id(), 'friend_name', true),
                'friend_email' => get_post_meta($order->get_id(), 'friend_email', true),
                'friend_phone' => get_post_meta($order->get_id(), 'friend_phone', true),
                'facebook_page_name_other' => get_post_meta($order->get_id(), 'facebook_page_name_other', true),
                'friend_qr_code' => get_post_meta($order->get_id(), 'friend_qr_code', true),
            ),
            'line_items' => array(),
            'is_editable' => $order->is_editable(),
        );

        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $formatted_order['line_items'][] = array(
                'item_id' => $item_id,
                'name' => $product->get_name(),
                'product_id' => $product->get_id(),
                'selected_gift' => get_post_meta($item_id, '_selected_gift', true),
                'selected_gift_name' => get_post_meta($item_id, 'selected_gift_name', true),
                'monthly_fee' => get_post_meta($item_id, 'monthly_fee', true),
                'quantity' => $item->get_quantity(),
                'subtotal' => $order->get_line_subtotal($item),
                'total' => $order->get_line_total($item),
            );
        }

        $formatted_orders[] = $formatted_order;
    }

    return rest_ensure_response($formatted_orders);
}

function update_data_plan_details( WP_REST_Request $request ) {

    $slug = $request['slug'];

    // Query to get the product by slug

    $args = array(
        'post_type' => 'product',
        'name' => $slug,
        'posts_per_page' => 1,
        'post_status' => 'publish',
    );

    $products = get_posts($args);    

    // Check if the product was found

    if (empty($products)) {
        return new WP_Error( 'no_product_found', 'No product found with the given slug.', array( 'status' => 404 ) );
    }

    $wc_product_id = $products[0]->ID;
    $wc_product = wc_get_product($wc_product_id);

    // Update price if provided
    if (isset($request['new_price'])) {
        $wc_product->set_price($request['new_price']);
        $wc_product->set_regular_price($request['new_price']);
    }

    // Update monthly fee using ACF if provided
    if (isset($request['monthly_fee'])) {
        update_field('monthly_fee', $request['monthly_fee'], $wc_product_id);
    }

    // Update stock status if provided
    if (isset($request['stock_status'])) {
        $wc_product->set_stock_status($request['stock_status']);
    }

    $wc_product->save();

    return new WP_REST_Response('Product updated successfully', 200);

}

function custom_products_data_plan_by_slug_handler( WP_REST_Request $request ) {

    $slug = $request['slug'];



    // Query to get the product by slug

    $args = array(

        'post_type' => 'product',

        'name' => $slug,

        'posts_per_page' => 1,

        'post_status' => 'publish',

    );



    $products = get_posts($args);

    

    // Check if the product was found

    if (empty($products)) {

        return new WP_Error( 'no_product_found', 'No product found with the given slug.', array( 'status' => 404 ) );

    }



    $wc_product = wc_get_product($products[0]->ID);

    $product_data = array(

        'id' => $wc_product->get_id(),

            'name' => $wc_product->get_name(),

            'slug' => $wc_product->get_slug(),

            'type' => $wc_product->get_type(),

            'status' => $wc_product->get_status(),

            'stock_status' => $wc_product->get_stock_status(),

            'description' => $wc_product->get_description(),

            'sku' => $wc_product->get_sku(),

            'price' => $wc_product->get_price(),

            'regular_price' => $wc_product->get_regular_price(),

            'sale_price' => $wc_product->get_sale_price(),

            'on_sale' => $wc_product->is_on_sale(),

            'date_on_sale_from' => $wc_product->get_date_on_sale_from(),

            'date_on_sale_to' => $wc_product->get_date_on_sale_to(),

    );



    // Check if the product belongs to the 'data-plan' category

    $data_plan_term_id = get_term_by('slug', 'data-plan', 'product_cat')->term_id;

    if (in_array($data_plan_term_id, wp_get_post_terms($wc_product->get_id(), 'product_cat', array('fields' => 'ids')))) {

        $product_data['data_plan_code'] = get_field('data_plan_code', $wc_product->get_id());

        $product_data['monthly_fee'] = get_field('monthly_fee', $wc_product->get_id());

    }



    return new WP_REST_Response($product_data, 200);

}

function update_product_stock_status_by_slug( WP_REST_Request $request ) {

    $slug = $request['slug'];

    $new_stock_status = $request['stock_status'];



    // Find the product by slug

    $args = array(

        'post_type' => 'product',

        'name' => $slug,

        'posts_per_page' => 1,

        'post_status' => 'publish',

    );



    $posts = get_posts($args);



    if (empty($posts)) {

        return new WP_Error( 'no_product_found', 'No product found with given slug.', array( 'status' => 404 ) );

    }



    $product = wc_get_product($posts[0]->ID);



    // Check if product is in 'gifts' category

    if ( ! has_term( 'gifts', 'product_cat', $product->get_id() ) ) {

        return new WP_Error( 'invalid_category', 'Product is not in the gifts category.', array( 'status' => 403 ) );

    }



    // Update stock status

    $product->set_stock_status($new_stock_status);

    $product->save();



    return new WP_REST_Response( 'Stock status updated successfully.', 200 );

}

function custom_products_gifts_by_slug_handler($request) {

    $product_slug = $request['slug'];

    $args = array(

        'post_type' => 'product',

        'posts_per_page' => 1,

        'name' => $product_slug,

        'tax_query' => array(

            array(

                'taxonomy' => 'product_cat',

                'field' => 'slug',

                'terms' => 'gifts'

            ),

        ),

    );

    $products = get_posts($args);



    if (empty($products)) {

        return new WP_Error('no_product', 'Product not found', array('status' => 404));

    }



    $wc_product = wc_get_product($products[0]->ID);

    $product_data = array(

            'id' => $wc_product->get_id(),

            'name' => $wc_product->get_name(),

            'slug' => $wc_product->get_slug(),

            'type' => $wc_product->get_type(),

            'status' => $wc_product->get_status(),

            'stock_status' => $wc_product->get_stock_status(),

            'description' => $wc_product->get_description(),

            'sku' => $wc_product->get_sku(),

            'price' => $wc_product->get_price(),

            'regular_price' => $wc_product->get_regular_price(),

            'sale_price' => $wc_product->get_sale_price(),

            'on_sale' => $wc_product->is_on_sale(),

            'date_on_sale_from' => $wc_product->get_date_on_sale_from(),

            'date_on_sale_to' => $wc_product->get_date_on_sale_to(),

            'sold_individually' => $wc_product->is_sold_individually()

        );



    return new WP_REST_Response($product_data, 200);

}

function custom_products_data_plan_handler( $request ) {

   return get_products_by_category('data-plan');

}

function custom_products_gifts_handler( $request ) {

    return get_products_by_category('gifts');

}

function get_products_by_category($category_slug) {

    $args = array(

        'post_type' => 'product',

        'posts_per_page' => -1,

        'tax_query' => array(

            array(

                'taxonomy' => 'product_cat',

                'field'    => 'slug',

                'terms'    => $category_slug,

            ),

        ),

    );

    return get_custom_products($args);

}

function get_custom_products($args) {

    $products = get_posts($args);

    $custom_products = array();



    foreach ($products as $product) {

        $wc_product = wc_get_product($product->ID);



        // Gather necessary data

        $product_data = array(

            'id' => $wc_product->get_id(),

            'name' => $wc_product->get_name(),

            'slug' => $wc_product->get_slug(),

            'type' => $wc_product->get_type(),

            'status' => $wc_product->get_status(),

            'stock_status' => $wc_product->get_stock_status(),

            'description' => $wc_product->get_description(),

            'sku' => $wc_product->get_sku(),

            'price' => $wc_product->get_price(),

            'regular_price' => $wc_product->get_regular_price(),

            'sale_price' => $wc_product->get_sale_price(),

            'on_sale' => $wc_product->is_on_sale(),

            'date_on_sale_from' => $wc_product->get_date_on_sale_from(),

            'date_on_sale_to' => $wc_product->get_date_on_sale_to(),

            'sold_individually' => $wc_product->is_sold_individually()

        );



        // Add ACF fields if the product belongs to the 'data-plan' category

        $data_plan_term_id = get_term_by('slug', 'data-plan', 'product_cat')->term_id;

        if (in_array($data_plan_term_id, wp_get_post_terms($wc_product->get_id(), 'product_cat', array('fields' => 'ids')))) {

            $product_data['data_plan_code'] = get_field('data_plan_code', $wc_product->get_id());

            $product_data['monthly_fee'] = get_field('monthly_fee', $wc_product->get_id());

        }



        $custom_products[] = $product_data;

    }

    return new WP_REST_Response($custom_products, 200);

}

function custom_products_endpoint_handler( $request ) {

    // Fetch products using WC API

    $args = array('post_type' => 'product', 'posts_per_page' => -1);
    $products = get_posts( $args );


    $custom_products = array();

    foreach ( $products as $product ) {
        $wc_product = wc_get_product( $product->ID );


        // Gather necessary data

        $product_data = array(

            'id' => $wc_product->get_id(),

            'name' => $wc_product->get_name(),

            'slug' => $wc_product->get_slug(),

            'type' => $wc_product->get_type(),

            'status' => $wc_product->get_status(),

            'stock_status' => $wc_product->get_stock_status(),

            'description' => $wc_product->get_description(),

            'sku' => $wc_product->get_sku(),

            'price' => $wc_product->get_price(),

            'regular_price' => $wc_product->get_regular_price(),

            'sale_price' => $wc_product->get_sale_price(),

            'on_sale' => $wc_product->is_on_sale(),

            'date_on_sale_from' => $wc_product->get_date_on_sale_from(),

            'date_on_sale_to' => $wc_product->get_date_on_sale_to(),

            'sold_individually' => $wc_product->is_sold_individually(),

            'categories' => array_map(function($term) {

				return array(

					'id' => $term->term_id,

					'name' => $term->name

				);

			}, wp_get_post_terms($product->ID, 'product_cat', array('fields' => 'all')))

        );



        $data_plan_term_id = get_term_by( 'slug', 'data-plan', 'product_cat' )->term_id;

		$product_cat_ids = wp_list_pluck($product_data['categories'], 'id');



		if ( in_array( $data_plan_term_id, $product_cat_ids ) ) {

			$product_data['data_plan_code'] = get_field('data_plan_code', $wc_product->get_id());

			$product_data['monthly_fee'] = get_field('monthly_fee', $wc_product->get_id());

		} else {

			$product_data['data_plan_code'] = null;

			$product_data['monthly_fee'] = null;

		}



        $custom_products[] = $product_data;

    }



    return new WP_REST_Response( $custom_products, 200 );

}

function custom_product_detail_handler( $request ) {

    // Get the product ID from the URL

    $product_id = $request['id'];

    $wc_product = wc_get_product( $product_id );



    if ( ! $wc_product ) {

        return new WP_Error( 'no_product', 'Product not found', array( 'status' => 404 ) );

    }



    // Gather necessary data

    $product_data = array(

        'id' => $wc_product->get_id(),
            'name' => $wc_product->get_name(),

            'slug' => $wc_product->get_slug(),

            'type' => $wc_product->get_type(),

            'status' => $wc_product->get_status(),

            'stock_status' => $wc_product->get_stock_status(),

            'description' => $wc_product->get_description(),

            'sku' => $wc_product->get_sku(),

            'price' => $wc_product->get_price(),

            'regular_price' => $wc_product->get_regular_price(),

            'sale_price' => $wc_product->get_sale_price(),

            'on_sale' => $wc_product->is_on_sale(),

            'date_on_sale_from' => $wc_product->get_date_on_sale_from(),

            'date_on_sale_to' => $wc_product->get_date_on_sale_to(),

            'sold_individually' => $wc_product->is_sold_individually(),



        'categories' => array_map(function($term) {

            return array(

                'id' => $term->term_id,

                'name' => $term->name

            );

        }, wp_get_post_terms($wc_product->get_id(), 'product_cat', array('fields' => 'all')))

    );



    // ACF Integration

    $data_plan_term_id = get_term_by( 'slug', 'data-plan', 'product_cat' )->term_id;

    $product_cat_ids = wp_list_pluck($product_data['categories'], 'id');



    if ( in_array( $data_plan_term_id, $product_cat_ids ) ) {

        $product_data['data_plan_code'] = get_field('data_plan_code', $wc_product->get_id());

		$product_data['monthly_fee'] = get_field('monthly_fee', $wc_product->get_id());

    } else {

		$product_data['data_plan_code'] = null;

        $product_data['monthly_fee'] = null;

    }



    return new WP_REST_Response( $product_data, 200 );

}

