<?php
/**
 * Module: Fees and payment rules
 *
 * COD fee, minimum order amount and payment gateway rules.
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add an extra fee when the customer chooses Cash on Delivery.
 *
 * Feature: cod_fee
 * Filters: wccc_cod_fee (default 50), wccc_cod_fee_label
 */
if ( wccc_enabled( 'cod_fee' ) ) {
	add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}
		if ( ! is_checkout() || ! WC()->session ) {
			return;
		}
		if ( 'cod' !== WC()->session->get( 'chosen_payment_method' ) ) {
			return;
		}

		$fee = (float) apply_filters( 'wccc_cod_fee', 50 );

		if ( $fee > 0 ) {
			$cart->add_fee( apply_filters( 'wccc_cod_fee_label', __( 'Cash on Delivery fee', 'wccc' ) ), $fee, false );
		}
	} );

	// Recalculate the totals when the customer switches payment method.
	add_action( 'wp_footer', function () {
		if ( ! is_checkout() || is_order_received_page() ) {
			return;
		}
		?>
		<script>
		jQuery( function ( $ ) {
			$( 'form.checkout' ).on( 'change', 'input[name="payment_method"]', function () {
				$( document.body ).trigger( 'update_checkout' );
			} );
		} );
		</script>
		<?php
	} );
}

/**
 * Block orders below a minimum subtotal.
 *
 * Feature: minimum_order
 * Filter:  wccc_minimum_order_amount (default 500, in store currency)
 */
if ( wccc_enabled( 'minimum_order' ) ) {
	/**
	 * Build the minimum order message, or return an empty string if the cart qualifies.
	 *
	 * @return string
	 */
	function wccc_minimum_order_message() {
		if ( ! WC()->cart ) {
			return '';
		}

		$minimum  = (float) apply_filters( 'wccc_minimum_order_amount', 500 );
		$subtotal = (float) WC()->cart->get_subtotal();

		if ( $minimum <= 0 || $subtotal >= $minimum ) {
			return '';
		}

		return sprintf(
			/* translators: 1: minimum amount, 2: current subtotal. */
			__( 'The minimum order amount is %1$s. Your current subtotal is %2$s.', 'wccc' ),
			wc_price( $minimum ),
			wc_price( $subtotal )
		);
	}

	// Stop the order from being placed.
	add_action( 'woocommerce_checkout_process', function () {
		$message = wccc_minimum_order_message();
		if ( $message ) {
			wc_add_notice( $message, 'error' );
		}
	} );

	// Warn the customer early on the cart and checkout pages.
	$wccc_min_notice = function () {
		$message = wccc_minimum_order_message();
		if ( $message ) {
			wc_print_notice( $message, 'notice' );
		}
	};
	add_action( 'woocommerce_before_cart', $wccc_min_notice );
	add_action( 'woocommerce_before_checkout_form', $wccc_min_notice, 6 );
}

/**
 * Hide Cash on Delivery for orders above a set amount (reduces fake/risky COD orders).
 *
 * Feature: hide_cod_above_amount
 * Filter:  wccc_cod_max_amount (default 20000, in store currency)
 */
if ( wccc_enabled( 'hide_cod_above_amount' ) ) {
	add_filter( 'woocommerce_available_payment_gateways', function ( $gateways ) {
		if ( is_admin() || ! WC()->cart || ! isset( $gateways['cod'] ) ) {
			return $gateways;
		}

		$limit = (float) apply_filters( 'wccc_cod_max_amount', 20000 );

		if ( $limit > 0 && (float) WC()->cart->get_subtotal() > $limit ) {
			unset( $gateways['cod'] );
		}

		return $gateways;
	} );
}
