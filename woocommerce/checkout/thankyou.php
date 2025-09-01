<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 *
 * @var WC_Order $order
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="woocommerce-order">

	<?php
	if ( $order ) :

		do_action( 'woocommerce_before_thankyou', $order->get_id() );
		?>

		<?php if ( $order->has_status( 'failed' ) ) : ?>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php esc_html_e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

			<p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
				<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php esc_html_e( 'Pay', 'woocommerce' ); ?></a>
				<?php if ( is_user_logged_in() ) : ?>
					<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php endif; ?>
			</p>

		<?php else : ?>

			<div class="banner-checkout-success">
				<div class="top">
					<?php 
                        $frc_image = wp_get_attachment_url(1136);
                        if ( $frc_image ) {
                            echo '<img class="residence_card_example" src="' . esc_url( $frc_image ) . '" alt="Order Placed" /></div>';
                        } else {
                            echo 'Order Placed Image not found';
                        }
                    ?>
					<h1>Your Order Has Been Placed!</h1>
					<p>Order Number : <strong><?php echo $order->get_order_number(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong></p>
				</div>

				<div class="description">
				We have successfully sent your order details along with the tutorial on how to pay the monthly fee to <strong style="color:#de3594;"><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>.<br/>
				Our team will reach out to you for your order confirmation receipt during our working hours. <br/>
				Please await the confirmation email from us at <strong style="color:#de3594;"><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></strong>.
				</div>
		
				
				<div class="contact">
				If you have any questions, please contact us through here:<br/>
				
				Facebook: <a href="https://www.facebook.com/MashUpRSupportPage" target="_blank">facebook.com/MashUpRSupportPage</a><br/>
				WhatsApp: <a href="https://www.wa.me/818071543261" target="_blank">+81 80 7154 3261</a><br/>LINE: <a href="https://line.me/R/ti/p/976hvcff" target="_blank">@976hvcff</a>
				</div>
				
			</div>


		<?php endif; ?>

		<?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

	<?php else : ?>

		<?php wc_get_template( 'checkout/order-received.php', array( 'order' => false ) ); ?>

	<?php endif; ?>

</div>