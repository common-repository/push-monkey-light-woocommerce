<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

class PM_Light_WooCommerce_Review_Notice_Controller { 

	/* Public */

	const REVIEW_NOTICE_DISMISS_KEY = 'push_monkey_review_dismiss';
	private $is_saas = false;

	/**
	 * Public render function
	 */
	public function pm_light_woocommerce_render() {

		parse_str( $_SERVER['QUERY_STRING'], $params );
		$query_string = '?' . http_build_query( array_merge( $params, array( self::REVIEW_NOTICE_DISMISS_KEY => '1' ) ) );

		$review_url = esc_url( 'https://wordpress.org/support/view/plugin-reviews/push-monkey-desktop-push-notifications' );
		if ( ! $this->is_saas ) {
			
			$review_url = esc_url( 'http://codecanyon.net/item/push-monkey-native-desktop-push-notifications/10543634' );
		}
		$icon_src = esc_url( plugins_url( '../img/review-notice-icon.png', plugin_dir_path( __FILE__ ) ) );
		require_once( plugin_dir_path( __FILE__ ) . '../../templates/messages/push_monkey_review_notice.php' );
	}

	/**
	 * Constructor that initializes the Push Monkey class.
	 *
	 * @param      boolean  $is_saas  Indicates if saas
	 */
	function __construct( $is_saas ) {

		$this->is_saas = $is_saas;
	}
}