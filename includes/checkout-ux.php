<?php
/**
 * Module: Checkout UX
 *
 * Small changes that make the checkout faster and more trustworthy.
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Change the "Place order" button text.
 *
 * Feature: place_order_text
 * Filter:  wccc_place_order_text
 */
if ( wccc_enabled( 'place_order_text' ) ) {
	add_filter( 'woocommerce_order_button_text', function () {
		return apply_filters( 'wccc_place_order_text', __( 'Confirm Order', 'wccc' ) );
	} );
}

/**
 * Skip the cart page: send customers straight to checkout after "Add to cart".
 * Great for single-product stores and landing-page funnels.
 *
 * Feature: skip_cart
 */
if ( wccc_enabled( 'skip_cart' ) ) {
	// AJAX add-to-cart on shop pages can't redirect, so turn it off.
	add_filter( 'pre_option_woocommerce_enable_ajax_add_to_cart', function () {
		return 'no';
	} );

	add_filter( 'woocommerce_add_to_cart_redirect', function () {
		return wc_get_checkout_url();
	} );

	// Hide the "Product has been added to your cart" notice.
	add_filter( 'wc_add_to_cart_message_html', '__return_empty_string' );

	// Send anyone who opens the cart page to checkout (when the cart has items).
	add_action( 'template_redirect', function () {
		if ( is_cart() && WC()->cart && ! WC()->cart->is_empty() ) {
			wp_safe_redirect( wc_get_checkout_url() );
			exit;
		}
	} );
}

/**
 * Show a trust message right above the "Place order" button.
 *
 * Feature: trust_message
 * Filter:  wccc_trust_message
 */
if ( wccc_enabled( 'trust_message' ) ) {
	add_action( 'woocommerce_review_order_before_submit', function () {
		$message = apply_filters(
			'wccc_trust_message',
			__( '🔒 Secure checkout. Your personal information is safe with us.', 'wccc' )
		);

		echo '<p class="wccc-trust-message" style="margin:0 0 1em;font-size:0.9em;opacity:0.85;">' . wp_kses_post( $message ) . '</p>';
	} );
}

/**
 * "Add X more for free shipping" notice at the top of the checkout.
 * This only shows the message. Set up the Free Shipping method itself in
 * WooCommerce > Settings > Shipping with the same minimum amount.
 *
 * Feature: free_shipping_notice
 * Filter:  wccc_free_shipping_threshold (default 1000, in store currency)
 */
if ( wccc_enabled( 'free_shipping_notice' ) ) {
	add_action( 'woocommerce_before_checkout_form', function () {
		if ( ! WC()->cart ) {
			return;
		}

		$threshold = (float) apply_filters( 'wccc_free_shipping_threshold', 1000 );
		$subtotal  = (float) WC()->cart->get_displayed_subtotal();

		if ( $threshold > 0 && $subtotal < $threshold ) {
			wc_print_notice(
				sprintf(
					/* translators: %s: amount left for free shipping. */
					__( 'Add %s more to your cart to get free shipping!', 'wccc' ),
					wc_price( $threshold - $subtotal )
				),
				'notice'
			);
		}
	}, 5 );
}

/**
 * Remove the "Have a coupon?" form from the checkout page.
 *
 * Feature: remove_coupon_form
 */
if ( wccc_enabled( 'remove_coupon_form' ) ) {
	add_action( 'wp', function () {
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
	} );
}
