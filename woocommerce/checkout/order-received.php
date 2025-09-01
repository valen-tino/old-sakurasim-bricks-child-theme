<?php
/**
 * "Order received" message.
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
 * @version 8.3.0
 *
 * @var WC_Order|false $order
 */

defined( 'ABSPATH' ) || exit;
?>

<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received">
	<?php
	/**
	 * Filter the message shown after a checkout is complete.
	 *
	 * @since 2.2.0
	 *
	 * @param string         $message The message.
	 * @param WC_Order|false $order   The order created during checkout, or false if order data is not available.
	 */
	$message = apply_filters(
		'woocommerce_thankyou_order_received_text',
		esc_html( __( '', 'woocommerce' ) ),
		$order
	);

    $message .= 'Dear Customer,<br/><br/>Thank you for your order!<br/>We will contact you for your order confirmation receipt during working hours.<br/>Please wait for the confirmation email.<br/><br/>If you have any questions, please contact us through here:<br/>Facebook: <a href="https://www.facebook.com/MashUpRSupportPage" target="_blank">facebook.com/MashUpRSupportPage</a><br/>WhatsApp: <a href="https://www.wa.me/818071543261" target="_blank">+81 80 7154 3261</a><br/>LINE: <a href="https://line.me/R/ti/p/976hvcff" target="_blank">@976hvcff</a>';

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $message;
	?>
</p>