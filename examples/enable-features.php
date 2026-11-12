<?php
/**
 * Example: enable features and change settings.
 *
 * Where to put this code (pick one):
 * - Your child theme's functions.php
 * - A snippet in the free "Code Snippets" plugin
 * - Save this file as wp-content/mu-plugins/wccc-config.php
 *
 * Only enable the features you need, then test your checkout.
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

/**
 * 1. Choose which features to turn on.
 */
add_filter( 'wccc_features', function ( $features ) {
	return array_merge(
		$features,
		array(
			// Checkout fields.
			'remove_optional_fields' => true,
			'phone_validation'       => true,
			'reorder_fields'         => true,
			'custom_labels'          => true,
			'disable_order_notes'    => false,

			// Custom fields (delivery date + "How did you hear about us?").
			'custom_fields'          => true,

			// Checkout UX.
			'place_order_text'       => true,
			'skip_cart'              => false,
			'trust_message'          => true,
			'free_shipping_notice'   => true,
			'remove_coupon_form'     => false,

			// Fees and payments.
			'cod_fee'                => true,
			'minimum_order'          => true,
			'hide_cod_above_amount'  => true,

			// Thank-you page.
			'custom_thankyou_text'   => true,
			'thankyou_redirect'      => false,
		)
	);
} );

/**
 * 2. Change the settings (amounts are in your store currency).
 */
add_filter( 'wccc_cod_fee', function () {
	return 50;
} );

add_filter( 'wccc_minimum_order_amount', function () {
	return 500;
} );

add_filter( 'wccc_cod_max_amount', function () {
	return 20000;
} );

add_filter( 'wccc_free_shipping_threshold', function () {
	return 1500;
} );

add_filter( 'wccc_place_order_text', function () {
	return 'Confirm Order';
} );

/**
 * 3. Optional: international phone numbers instead of Bangladeshi mobile numbers.
 */
// add_filter( 'wccc_phone_pattern', function () {
// 	return '/^\+?[0-9]{7,15}$/';
// } );

/**
 * 4. Optional: add your own custom checkout field.
 */
add_filter( 'wccc_custom_fields', function ( $fields ) {
	$fields['wccc_gift_message'] = array(
		'type'        => 'textarea',
		'label'       => __( 'Gift message (optional)', 'wccc' ),
		'placeholder' => __( 'Write a short message for the gift card', 'wccc' ),
		'required'    => false,
		'class'       => array( 'form-row-wide' ),
	);
	return $fields;
} );

/**
 * 5. Optional: redirect to your own thank-you page (must be on your site).
 */
// add_filter( 'wccc_thankyou_redirect_url', function () {
// 	return home_url( '/thank-you/' );
// } );
