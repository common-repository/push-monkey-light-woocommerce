<?php
/*
 * Plugin Name: Push Monkey Light and Abandoned Cart for WooCommerce 
 * Plugin URI: 
 * Author: Get Push Monkey Ltd.
 * Description: Engage & delight your readers with Desktop Push Notifications - a new subscription channel directly to the mobiles or desktops of your readers. Remind your shoppers of abandoned carts when using WooCommerce. To start, register on <a href="https://www.getpushmonkey.com?source=plugin_desc" target="_blank">getpushmonkey.com</a>. Currently this works for  Chrome, Firefox and Safari on MacOS, Windows and Android.
 * Version: 1.0.0
 * Text Domain: push-monkey-light-woocommerce
 * Domain Path: /languages
 * Author URI: http://www.getpushmonkey.com/?source=plugin
 * License: GPL2
 */

/*  
Push Monkey Light and Abandoned Cart for WooCommerce
Copyright (C) 2018 Get Push Monkey Ltd. (email : tudor@getpushmonkey.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// WordPress Check
if ( ! defined( 'ABSPATH' ) ) exit;

// Require core file
$pm_corefile = plugin_dir_path( __FILE__ ) . 'includes/class-push-monkey-light-woocommerce-core.php';
if ( file_exists( $pm_corefile ) ) {
  
  require_once $pm_corefile;
  new PM_Light_WooCommerce();
}

/**
 * Load plugin textdomain.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', 'pm_light_woocommerce_load_textdomain' );
function pm_light_woocommerce_load_textdomain() {

  load_plugin_textdomain( 'push-monkey-light-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' ); 
}

/**
 * Push Monkey Light WooCommerce deactivate.
 */
register_deactivation_hook( __FILE__, 'pm_light_woocommerce_deactivate' );
function pm_light_woocommerce_deactivate() {

  flush_rewrite_rules( true );
}

/**
 * Push Monkey Light WooCommerce activate.
 */
register_activation_hook( __FILE__, 'pm_light_woocommerce_activate' );
function pm_light_woocommerce_activate() {

  pm_light_woocommerce_rewrite_service_worker_url();
  flush_rewrite_rules( true );
}

/**
 * Push Monkey Light WooCommerce rewrite service worker url.
 */
function pm_light_woocommerce_rewrite_service_worker_url() {

  $account_key = get_option( PM_Light_WooCommerce::ACCOUNT_KEY_KEY, NULL );
  if ( $account_key ) {
  
    add_rewrite_rule( '^service\-worker\-' . $account_key . '\.php/?', plugin_dir_path( __DIR__ ) . 'templates/pages/service_worker.php', 
      'top' );
  }
}

/**
 * Push Monkey Light WooCommerce plugin updated.
 *
 * @param      <type>  $upgrader_object  The upgrader object
 * @param      <type>  $options          The options
 */
add_action( 'upgrader_process_complete', 'pm_light_woocommerce_plugin_updated', 10, 2 );
function pm_light_woocommerce_plugin_updated( $upgrader_object, $options ) { 
  
  $current_plugin_path_name = plugin_basename( __FILE__ );
  if ( $options['action'] == 'update' && $options['type'] == 'plugin' ){
    
    if ( isset( $options['packages'] ) ) {
      
      foreach( $options['packages'] as $each_plugin ) {
        
        if ( $each_plugin == $current_plugin_path_name ) {
          
          rewrite_service_worker_url();
          flush_rewrite_rules(true);
        }
      }
    }
  }
}
