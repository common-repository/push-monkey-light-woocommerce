<?php
/**
* Push Monkey Light Woocommerce uninstall
*
* Uninstalling Push Monkey Push Monkey Light Woocommerce options.
*/

// If uninstall not called from Wordpress exit 
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {

	exit();
}

require_once( plugin_dir_path( __FILE__ ) . 'push-monkey-light-woocommerce.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-push-monkey-light-woocommerce-client.php' );
require_once( plugin_dir_path( __FILE__ ) . '../models/class-push-monkey-light-woocommerce-review-notice.php' );

// Delete plugin options
delete_option( PM_Light_WooCommerce::ACCOUNT_KEY_KEY );
delete_option( PM_Light_WooCommerce::EMAIL_KEY );
delete_option( PM_Light_WooCommerce::USER_SIGNED_IN );
delete_option( PM_Light_WooCommerce::WEBSITE_NAME_KEY );
delete_option( PM_Light_WooCommerce::WEBSITE_PUSH_ID_KEY );
delete_option( PM_Light_WooCommerce::FLUSH_REWRITE_RULES_FLAG_KEY );
delete_option( PM_Light_WooCommerce::WOO_COMMERCE_ENABLED );
delete_option( PM_Light_WooCommerce_Client::PLAN_NAME_KEY );

// Revice notice uninstall
$review_notice = new PM_Light_WooCommerce_Review_Notice();
$review_notice->uninstall();
