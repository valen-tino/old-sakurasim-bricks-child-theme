<?php
/**
 * Checkout Order Receipt Template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-receipt.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woo.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<ul class="order_details">
	<li class="order">
		<?php esc_html_e( 'Order number:', 'woocommerce' ); ?>
		<strong><?php echo esc_html( $order->get_order_number() ); ?></strong>
	</li>
	<li class="date">
		<?php esc_html_e( 'Date:', 'woocommerce' ); ?>
		<strong><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></strong>
	</li>
	<li class="total">
		<?php esc_html_e( 'Total:', 'woocommerce' ); ?>
		<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
	</li>
    <?php if ( has_term( 'data-plan', 'product_cat', $order ) ) : ?>
                    <?php
                        $monthly_fee_total = 0;
                        $items = $order->get_items();
                        foreach ( $items as $item_id => $item ) {
                            $product_id = $item->get_product_id();
                            if ( has_term( 'data-plan', 'product_cat', $product_id ) ) {
                                $monthly_fee = get_field( 'monthly_fee', $product_id );
                                if ( $monthly_fee ) {
                                    $monthly_fee_total += $monthly_fee;
                                }
                            }
                        }
                    ?>
                    <li class="woocommerce-order-overview__monthly-fee">
                        <?php esc_html_e( 'Total Monthly Fee:', 'woocommerce' ); ?>
                        <strong><?php echo wc_price( $monthly_fee_total ); ?></strong>
                    </li>
                <?php endif; ?>
	<?php if ( $order->get_payment_method_title() ) : ?>
	<li class="method">
		<?php esc_html_e( 'Payment method:', 'woocommerce' ); ?>
		<strong><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></strong>
	</li>
	<?php endif; ?>
</ul>

<?php do_action( 'woocommerce_receipt_' . $order->get_payment_method(), $order->get_id() ); ?>

<div class="clear"></div>