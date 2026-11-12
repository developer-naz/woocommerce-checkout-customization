<?php
/**
 * Module: Checkout fields
 *
 * Remove, validate, reorder and relabel the default WooCommerce checkout fields.
 * Works with the classic checkout ([woocommerce_checkout] shortcode).
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove fields most stores don't need.
 *
 * Feature: remove_optional_fields
 * Filter:  wccc_fields_to_remove
 */
if ( wccc_enabled( 'remove_optional_fields' ) ) {
	add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
		$remove = apply_filters(
			'wccc_fields_to_remove',
			array(
				'billing'  => array( 'billing_company', 'billing_address_2' ),
				'shipping' => array( 'shipping_company', 'shipping_address_2' ),
			)
		);

		foreach ( $remove as $section => $keys ) {
			foreach ( (array) $keys as $key ) {
				unset( $fields[ $section ][ $key ] );
			}
		}

		return $fields;
	}, 20 );
}

/**
 * Make the phone number required and validate its format.
 *
 * Feature: phone_validation
 * Filter:  wccc_phone_pattern
 *
 * The default pattern accepts Bangladeshi mobile numbers:
 * 01XXXXXXXXX, 8801XXXXXXXXX and +8801XXXXXXXXX.
 */
if ( wccc_enabled( 'phone_validation' ) ) {
	add_filter( 'woocommerce_billing_fields', function ( $fields ) {
		if ( isset( $fields['billing_phone'] ) ) {
			$fields['billing_phone']['required'] = true;
		}
		return $fields;
	} );

	add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
		if ( empty( $data['billing_phone'] ) ) {
			return; // An empty value is already handled by the "required" check.
		}

		$phone   = preg_replace( '/[\s\-().]/', '', $data['billing_phone'] );
		$pattern = apply_filters( 'wccc_phone_pattern', '/^(?:\+?88)?01[3-9]\d{8}$/' );

		if ( ! preg_match( $pattern, $phone ) ) {
			$errors->add( 'validation', __( 'Please enter a valid mobile number, for example 01712345678.', 'wccc' ) );
		}
	}, 10, 2 );
}

/**
 * Reorder fields: name, phone, email, then the address.
 *
 * Feature: reorder_fields
 */
if ( wccc_enabled( 'reorder_fields' ) ) {
	// Address fields are ordered through the default address fields,
	// so the order survives WooCommerce's country-based JavaScript.
	add_filter( 'woocommerce_default_address_fields', function ( $fields ) {
		$priorities = array(
			'first_name' => 10,
			'last_name'  => 20,
			'country'    => 50,
			'state'      => 60,
			'city'       => 70,
			'address_1'  => 80,
			'address_2'  => 85,
			'postcode'   => 90,
		);

		foreach ( $priorities as $key => $priority ) {
			if ( isset( $fields[ $key ] ) ) {
				$fields[ $key ]['priority'] = $priority;
			}
		}

		return $fields;
	} );

	// Phone and email right after the name.
	add_filter( 'woocommerce_billing_fields', function ( $fields ) {
		if ( isset( $fields['billing_phone'] ) ) {
			$fields['billing_phone']['priority'] = 30;
		}
		if ( isset( $fields['billing_email'] ) ) {
			$fields['billing_email']['priority'] = 40;
		}
		return $fields;
	} );
}

/**
 * Friendlier labels and placeholders.
 *
 * Feature: custom_labels
 */
if ( wccc_enabled( 'custom_labels' ) ) {
	add_filter( 'woocommerce_default_address_fields', function ( $fields ) {
		if ( isset( $fields['address_1'] ) ) {
			$fields['address_1']['label']       = __( 'Full address', 'wccc' );
			$fields['address_1']['placeholder'] = __( 'House no, road, area', 'wccc' );
		}
		if ( isset( $fields['city'] ) ) {
			$fields['city']['label'] = __( 'City / Upazila', 'wccc' );
		}
		return $fields;
	}, 20 );

	add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['label']       = __( 'Mobile number', 'wccc' );
			$fields['billing']['billing_phone']['placeholder'] = '01XXXXXXXXX';
		}
		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['placeholder'] = __( 'Any special instructions for delivery?', 'wccc' );
		}
		return $fields;
	}, 20 );
}

/**
 * Remove the "Order notes" field.
 *
 * Feature: disable_order_notes
 */
if ( wccc_enabled( 'disable_order_notes' ) ) {
	add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
}
