<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

	exit;
}

require_once( plugin_dir_path( __FILE__ ) . '../includes/class-push-monkey-light-woocommerce-debugger.php' );
require_once( plugin_dir_path( __FILE__ ) . '../includes/class-push-monkey-light-woocommerce-date-time.php' );

/**
 * Banner model to set and get properties related to the CTA Banner.
 */
class PM_Light_WooCommerce_Review_Notice {

	/* Public */

	const SIGN_IN_DATE_KEY = 'pm_light_woocommerce_sign_in_date';
	const DISMISS_KEY = 'pm_light_woocommerce_review_dismiss_key';
	const DATE_FORMAT = 'Y-m-d H:i:s';

	/**
	 * Sets the sign in date.
	 *
	 * @param  str  $date   The date
	 */
	public function setSignInDate( $date ) {

		$new_time = $date->format( self::DATE_FORMAT );
		update_option( self::SIGN_IN_DATE_KEY, $new_time );
	}

	/**
	 * Gets the sign in date.
	 *
	 * @return  DateTime  The sign in date.
	 */
	public function pm_light_woocommerce_get_sign_in_date() {

		$date_string = get_option( self::SIGN_IN_DATE_KEY );
		if( ! $date_string ) {

			return new DateTime();
		}
		$stored_time = PM_Light_WooCommerce_Date_Time::createFromFormat( self::DATE_FORMAT, $date_string );
		return $stored_time;
	}

	/**
	 * Sets the dismiss.
	 *
	 * @param  str  $dismiss  The dismiss
	 */
	public function pm_light_woocommerce_set_dismiss( $dismiss ) {

		update_option( self::DISMISS_KEY, $dismiss );
	}

	/**
	 * Gets the dismiss.
	 *
	 * @return  str  The dismiss.
	 */
	public function pm_light_woocommerce_get_dismiss() {

		return get_option( self::DISMISS_KEY );
	}

	/**
	 * Determines ability to display notice.
	 *
	 * @return  boolean  True if able to display notice, False otherwise.
	 */
	public function pm_light_woocommerce_can_display_notice() {

		if ( $this->pm_light_woocommerce_get_dismiss() ) {

			return false;
		}
		$now = new DateTime();
		$stored_date = $this->pm_light_woocommerce_get_sign_in_date();
		$interval = $now->getTimestamp() - $stored_date->getTimestamp();
		if ( $interval >= (60 * 60 * 24 * 7) ) {

			return true;
		}
		return false;
	}

	/**
	 * delete options
	 */
	public function uninstall() {

		delete_option( self::SIGN_IN_DATE_KEY );
		delete_option( self::DISMISS_KEY );
	}

	/**
	 * Constructor that initializes the class.
	 */
	function __construct() {

		$this->d = new PM_Light_WooCommerce_Debugger();
	}
}