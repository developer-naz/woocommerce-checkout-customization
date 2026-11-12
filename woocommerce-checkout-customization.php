<?php
/**
 * Plugin Name:          WooCommerce Checkout Customization
 * Plugin URI:           https://github.com/developer-naz/woocommerce-checkout-customization
 * Description:          A modular toolkit to customize the WooCommerce classic checkout: fields, custom fields, UX, fees, payment rules and the thank-you page. Every feature is off by default.
 * Version:              1.0.0
 * Author:               Md Nazmul Islam (developer-naz)
 * Author URI:           https://www.linkedin.com/in/developernaz/
 * License:              GPL-2.0-or-later
 * License URI:          https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:          wccc
 * Requires at least:    6.0
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 7.0
 *
 * @package WCCC
 */

defined( 'ABSPATH' ) || exit;

define( 'WCCC_VERSION', '1.0.0' );
define( 'WCCC_FILE', __FILE__ );
define( 'WCCC_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Declare compatibility with WooCommerce High-Performance Order Storage (HPOS).
 */
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WCCC_FILE, true );
	}
} );

/**
 * Feature switches.
 *
 * Every feature is OFF by default, so activating the plugin changes nothing.
 * Turn features on with the 'wccc_features' filter (see examples/enable-features.php).
 *
 * @return array<string, bool>
 */
function wccc_features() {
	$defaults = array(
		// Module: includes/checkout-fields.php.
		'remove_optional_fields' => false,
		'phone_validation'       => false,
		'reorder_fields'         => false,
		'custom_labels'          => false,
		'disable_order_notes'    => false,

		// Module: includes/custom-fields.php.
		'custom_fields'          => false,

		// Module: includes/checkout-ux.php.
		'place_order_text'       => false,
		'skip_cart'              => false,
		'trust_message'          => false,
		'free_shipping_notice'   => false,
		'remove_coupon_form'     => false,

		// Module: includes/fees-and-payments.php.
		'cod_fee'                => false,
		'minimum_order'          => false,
		'hide_cod_above_amount'  => false,

		// Module: includes/thank-you.php.
		'custom_thankyou_text'   => false,
		'thankyou_redirect'      => false,
	);

	return wp_parse_args( (array) apply_filters( 'wccc_features', array() ), $defaults );
}

/**
 * Check whether a feature is enabled.
 *
 * @param string $feature Feature key from wccc_features().
 * @return bool
 */
function wccc_enabled( $feature ) {
	static $features = null;

	if ( null === $features ) {
		$features = wccc_features();
	}

	return ! empty( $features[ $feature ] );
}

/**
 * Load the modules after the theme, so 'wccc_features' filters added
 * in a child theme's functions.php are respected.
 */
add_action( 'after_setup_theme', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'WooCommerce Checkout Customization requires WooCommerce to be installed and active.', 'wccc' ) . '</p></div>';
		} );
		return;
	}

	$modules = array( 'checkout-fields', 'custom-fields', 'checkout-ux', 'fees-and-payments', 'thank-you' );

	foreach ( $modules as $module ) {
		require_once WCCC_PATH . 'includes/' . $module . '.php';
	}
}, 20 );
