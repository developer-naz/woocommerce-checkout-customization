<?php
/**
 * Module: Thank-you page
 *
 * Personalize the order-received message or redirect to a custom page.
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Personalized "Thank you" message with the customer's name and order number.
 *
 * Feature: custom_thankyou_text
 * Filter:  wccc_thankyou_text (receives the default text and the order)
 */
if ( wccc_enabled( 'custom_thankyou_text' ) ) {
	add_filter( 'woocommerce_thankyou_order_received_text', function ( $text, $order ) {
		if ( ! $order instanceof WC_Order ) {
			return $text;
		}

		$custom = sprintf(
			/* translators: 1: customer first name, 2: order number. */
			__( 'Thank you, %1$s! Your order #%2$s has been received. We will contact you shortly to confirm it.', 'wccc' ),
			esc_html( $order->get_billing_first_name() ),
			esc_html( $order->get_order_number() )
		);

		return apply_filters( 'wccc_thankyou_text', $custom, $order );
	}, 10, 2 );
}

/**
 * Redirect to a custom thank-you page after a successful order.
 * The order ID and key are passed along, so the custom page can show order details.
 *
 * Note: purchase tracking (GA4, Meta Pixel) often runs on the default
 * thank-you page. Make sure your tracking also fires on the custom page.
 *
 * Feature: thankyou_redirect
 * Filter:  wccc_thankyou_redirect_url (must be a URL on your own site; empty = off)
 */
if ( wccc_enabled( 'thankyou_redirect' ) ) {
	add_action( 'template_redirect', function () {
		if ( ! is_order_received_page() ) {
			return;
		}

		$url = (string) apply_filters( 'wccc_thankyou_redirect_url', '' );
		if ( '' === $url ) {
			return;
		}

		global $wp;
		$order_id  = isset( $wp->query_vars['order-received'] ) ? absint( $wp->query_vars['order-received'] ) : 0;
		$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order     = wc_get_order( $order_id );

		// Only redirect for a real order with a matching key.
		if ( ! $order || ! hash_equals( $order->get_order_key(), $order_key ) ) {
			return;
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'order' => $order_id,
					'key'   => $order_key,
				),
				$url
			)
		);
		exit;
	} );
}
