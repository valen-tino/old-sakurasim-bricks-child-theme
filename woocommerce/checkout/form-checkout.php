<?php
/**
 * Custom Checkout Form for Sakura SIM
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

?>

    <!-- Multi-step checkout indicator -->
	<div class="checkout-steps-indicator">
		<div class="step" id="indicator-step-questions">1. Questions</div>
		<div class="step" id="indicator-step-billing">2. Personal Information</div>
		<div class="step" id="indicator-step-tos">3. Terms & Conditions</div>
		<div class="step" id="indicator-step-order-review">4. Order Review</div>
	</div>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">




    <!-- Multi-step checkout starts here -->
    <div class="multi-step-checkout">
		
	<!-- Step 1: Questions -->
        <div class="checkout-step" id="checkout-step-questions">
            <?php if ( $checkout->get_checkout_fields() ) : ?>
                <?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
            <?php endif; ?><br/>
                
            <br/><button type="button" class="button next-step" data-next-step="#checkout-step-billing">Next Step</button>
        </div>

        <!-- Step 2: Personal Details -->
        <div class="checkout-step" id="checkout-step-billing">
            <?php if ( $checkout->get_checkout_fields() ) : ?>
                <?php do_action( 'woocommerce_checkout_billing' ); ?>
				<?php do_action( 'woocommerce_checkout_shipping' ); ?>
            <?php endif; ?><br/>
			
            	<button type="button" class="button prev-step" data-prev-step="#checkout-step-questions">Previous Step</button>
				<button type="button" class="button next-step" data-next-step="#checkout-step-tos">Next Step</button>
        </div>

        <!-- Step 3: Terms Of Conditions -->
        <div class="checkout-step" id="checkout-step-tos" style="display:none;">
            <h2>Terms of Conditions</h2><br/>
            <?php echo do_shortcode('[bricks_template id="1219"]'); ?>
            
			<br/>
			<button type="button" class="button prev-step" data-prev-step="#checkout-step-billing">Previous Step</button>
            <button type="button" class="button next-step" data-next-step="#checkout-step-order-review">Next Step</button>
        </div>

        <!-- Step 3: Order Review -->
        <div class="checkout-step" id="checkout-step-order-review" style="display:none;">
            <?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
            <h3 id="order_review_heading"><?php esc_html_e( 'Your order', 'woocommerce' ); ?></h3>
            <div id='monthlyfeebanner'></div>
            <?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action( 'woocommerce_checkout_order_review' ); ?>
            </div>
            <?php do_action( 'woocommerce_checkout_after_order_review' ); ?><br/>
			<button type="button" class="button prev-step" data-prev-step="#checkout-step-tos">Previous Step</button>
        </div>
    </div>
    <!-- End of multi-step checkout -->

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
