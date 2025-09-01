<?php 
/**
 * Register/enqueue custom scripts and styles
 */
add_action( 'wp_enqueue_scripts', function() {
	// Enqueue your files on the canvas & frontend, not the builder panel. Otherwise custom CSS might affect builder)
	if ( ! bricks_is_builder_main() ) {
		wp_enqueue_style( 'bricks-child', get_stylesheet_uri(), ['bricks-frontend'], filemtime( get_stylesheet_directory() . '/style.css' ) );
	}
} );

/**
 * Register custom elements
 */
add_action( 'init', function() {
  $element_files = [
    __DIR__ . '/elements/title.php',
  ];

  foreach ( $element_files as $file ) {
    \Bricks\Elements::register_element( $file );
  }
}, 11 );

/**
 * Add text strings to builder
 */
add_filter( 'bricks/builder/i18n', function( $i18n ) {
  // For element category 'custom'
  $i18n['custom'] = esc_html__( 'Custom', 'bricks' );

  return $i18n;
} );

// Custom Code Below (DON'T EDIT IT WITHOUT INFORMING THE MAIN DEVELOPER)

// Change Custom Fields Limit 
function change_custom_field_limit($limit){return 40;}
add_filter('postmeta_form_limit','change_custom_field_limit'); 

// Redirect to Cart Page after "Buy Now" Button is clicked
function custom_redirect_buy_now_to_cart() {
    // Check if WooCommerce is active
    if ( class_exists( 'WooCommerce' ) ) {
        // Hook into 'woocommerce_add_to_cart_redirect'
        add_filter( 'woocommerce_add_to_cart_redirect', 'redirect_to_cart' );
        
        function redirect_to_cart( $url ) {
            // Redirect to the cart page
            return wc_get_cart_url();
        }
    }
}
add_action( 'init', 'custom_redirect_buy_now_to_cart' );


// Calls external php file to simplify codebase
require_once( get_stylesheet_directory() . '/inc/rest_api_routes.php' );
require_once( get_stylesheet_directory() . '/inc/seller_code_cc.php');

require_once( get_stylesheet_directory(  ) . '/inc/edit_cart_page.php');

require_once ( get_stylesheet_directory() . '/inc/test_display_and_handle_g_1.php');
require_once( get_stylesheet_directory() . '/inc/remove_address_on_checkout.php' );

require_once ( get_stylesheet_directory() . '/inc/test_handle_monthly_fee.php');
require_once( get_stylesheet_directory() . '/inc/test_checkout_qs_1.php');

require_once( get_stylesheet_directory() . '/inc/preferred_dd_and_mf_option.php' );

require_once( get_stylesheet_directory() . '/inc/test_thankyou_hooks.php');

















