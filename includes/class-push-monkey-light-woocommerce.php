<?php
// WordPress Check
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce model for push monkey.
 */
class PM_Light_WooCommerce_Init extends PM_Light_WooCommerce {

	/**
	 * Adds actions.
	 */
	public function add_actions() {

		$woo_enabled = get_option( self::WOO_COMMERCE_ENABLED, false );
		if ( $woo_enabled !== '1' ) {
			return;
		}
		add_filter( 'woocommerce_cart_id', array( $this,'pm_light_woocommerce_filter_wc_cart_id'), 10, 5 );
		add_action( 'woocommerce_add_to_cart', array( $this,  'pm_light_woocommerce_add_to_cart_hook' ) );
		add_action( 'woocommerce_order_status_completed', array( $this,  'pm_light_woocommerce_update_cart_hook' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'pm_light_woocommerce_checkout_update_order_meta' ), 10, 2 );
	}

	/**
	 * Woocommerce cart filter
	 *
	 * @param      number  $cart_id         The cartesian identifier
	 * @param      number  $product_id      The product identifier
	 * @param      array  $variation_id    The variation identifier
	 * @param      array  $variation       The variation
	 * @param      array  $cart_item_data  The cartesian item data
	 *
	 * @return     number  ( return cart ID )
	 */
	public function pm_light_woocommerce_filter_wc_cart_id ( $cart_id, $product_id, $variation_id, $variation, $cart_item_data ) {

		$cart_id = substr( $cart_id, 1, 9 ) . strtotime( 'now' ) . mt_rand( 10, 9999 );
		return $cart_id;
	}

	/**
	 * Woocommerce add to cart hook
	 *
	 * @param      str  $key    The key
	 *
	 * @return     str
	 */
	public function pm_light_woocommerce_add_to_cart_hook( $key ) {

		global $woocommerce;
		$pushmonkey = new PM_Light_WooCommerce();
		$api_token = $pushmonkey->pm_light_woocommerce_account_key();
		$stored_key = WC()->session->get( '_push_monkey' );
		if( $pushmonkey->pm_light_woocommerce_has_account_key() && $stored_key == null ) {

			$response = $pushmonkey->apiClient->pm_light_woocommerce_create_cart( $key, $api_token );
			WC()->session->set( '_push_monkey', $key );
			wc_setcookie( '_push_monkey_wc_cart_id', $key, time()+60*60*24*5 );
		}
		return $key;
	}

	/**
	 * update cart
	 *
	 * @param      number $order_id  The order identifier
	 */
	public function pm_light_woocommerce_update_cart_hook( $order_id ) {

		global $woocommerce;
		$order = new WC_order( $order_id );
		$key = get_post_meta( $order_id, '_cart_id', true );
		$pushmonkey = new PM_Light_WooCommerce();
		$api_token = $pushmonkey->pm_light_woocommerce_account_key();
		//Update cart if key is not empty.
		if( $key != '' ) {

			$response = $pushmonkey->apiClient->pm_light_woocommerce_update_cart( $key, $api_token );
		}
	}

	/**
	 * Woocommerce checkout update order meta
	 *
	 * @param      number  $order_id  The order identifier
	 */
	public function pm_light_woocommerce_checkout_update_order_meta( $order_id ) {

		$key = WC()->session->get( '_push_monkey' );
		update_post_meta( $order_id, '_cart_id', sanitize_text_field( $key ) );
		wc_setcookie( '_push_monkey_wc_cart_id', '', -1 );                
	}

	/**
	 * Private
	 */
	function __construct() {

		$this->add_actions();
	}
}
// Init class
new PM_Light_WooCommerce_Init();
