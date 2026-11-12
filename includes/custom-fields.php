<?php
/**
 * Module: Custom checkout fields
 *
 * Adds extra fields to the checkout, validates them, saves them to the order
 * (HPOS compatible) and shows them in wp-admin, order emails, the thank-you
 * page and My Account.
 *
 * Feature: custom_fields
 * Filter:  wccc_custom_fields (add, remove or change fields)
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

if ( ! wccc_enabled( 'custom_fields' ) ) {
	return;
}

/**
 * Field definitions. Uses the same format as woocommerce_form_field().
 *
 * @return array<string, array>
 */
function wccc_custom_fields() {
	return apply_filters(
		'wccc_custom_fields',
		array(
			'wccc_delivery_date'   => array(
				'type'              => 'date',
				'label'             => __( 'Preferred delivery date', 'wccc' ),
				'required'          => false,
				'class'             => array( 'form-row-wide' ),
				'custom_attributes' => array(
					'min' => wp_date( 'Y-m-d', time() + DAY_IN_SECONDS ),
				),
			),
			'wccc_referral_source' => array(
				'type'     => 'select',
				'label'    => __( 'How did you hear about us?', 'wccc' ),
				'required' => false,
				'class'    => array( 'form-row-wide' ),
				'options'  => array(
					''          => __( 'Select an option', 'wccc' ),
					'facebook'  => 'Facebook',
					'google'    => 'Google',
					'instagram' => 'Instagram',
					'youtube'   => 'YouTube',
					'friend'    => __( 'Friend or family', 'wccc' ),
					'other'     => __( 'Other', 'wccc' ),
				),
			),
		)
	);
}

/**
 * Add the fields to the "Additional information" section of the checkout.
 */
add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	$priority = 200;

	foreach ( wccc_custom_fields() as $key => $field ) {
		$priority               += 10;
		$field['priority']       = $priority;
		$fields['order'][ $key ] = $field;
	}

	return $fields;
} );

/**
 * Extra validation: the delivery date must be in the future.
 */
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( empty( $data['wccc_delivery_date'] ) ) {
		return;
	}

	$date  = DateTime::createFromFormat( '!Y-m-d', $data['wccc_delivery_date'], wp_timezone() );
	$today = new DateTime( 'today', wp_timezone() );

	if ( ! $date || $date <= $today ) {
		$errors->add( 'validation', __( 'Please choose a delivery date from tomorrow onwards.', 'wccc' ) );
	}
}, 10, 2 );

/**
 * Save the values to the order (works with both HPOS and legacy storage).
 */
add_action( 'woocommerce_checkout_create_order', function ( $order, $data ) {
	foreach ( array_keys( wccc_custom_fields() ) as $key ) {
		if ( isset( $data[ $key ] ) && '' !== $data[ $key ] ) {
			$order->update_meta_data( '_' . $key, sanitize_text_field( $data[ $key ] ) );
		}
	}
}, 10, 2 );

/**
 * Get the saved custom field values of an order as "Label => Value" pairs.
 *
 * @param WC_Order $order Order object.
 * @return array<string, string>
 */
function wccc_get_custom_field_rows( $order ) {
	$rows = array();

	if ( ! $order instanceof WC_Order ) {
		return $rows;
	}

	foreach ( wccc_custom_fields() as $key => $field ) {
		$value = (string) $order->get_meta( '_' . $key );

		if ( '' === $value ) {
			continue;
		}

		if ( 'select' === $field['type'] && isset( $field['options'][ $value ] ) ) {
			$value = $field['options'][ $value ];
		} elseif ( 'date' === $field['type'] ) {
			$value = date_i18n( get_option( 'date_format' ), strtotime( $value ) );
		}

		$rows[ $field['label'] ] = $value;
	}

	return $rows;
}

/**
 * Show the values in wp-admin, under the billing address.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	foreach ( wccc_get_custom_field_rows( $order ) as $label => $value ) {
		printf( '<p><strong>%s:</strong> %s</p>', esc_html( $label ), esc_html( $value ) );
	}
} );

/**
 * Show the values in order emails (customer and admin).
 */
add_filter( 'woocommerce_email_order_meta_fields', function ( $fields, $sent_to_admin, $order ) {
	foreach ( wccc_get_custom_field_rows( $order ) as $label => $value ) {
		$fields[] = array(
			'label' => $label,
			'value' => $value,
		);
	}
	return $fields;
}, 10, 3 );

/**
 * Show the values on the thank-you page and in My Account > Orders.
 */
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
	$rows = wccc_get_custom_field_rows( $order );

	if ( empty( $rows ) ) {
		return;
	}

	echo '<section class="wccc-order-extra"><h2>' . esc_html__( 'Additional information', 'wccc' ) . '</h2><ul>';
	foreach ( $rows as $label => $value ) {
		printf( '<li><strong>%s:</strong> %s</li>', esc_html( $label ), esc_html( $value ) );
	}
	echo '</ul></section>';
} );
