<?php

/* WordPress Check */
if ( ! defined( 'ABSPATH' ) ) {

  exit;
}

/**
 * Class used to help debugging.
 */
class PM_Light_WooCommerce_Debugger { 

  /**
   * Prints a message to php.log
   * @param string $text 
   */
  public function pm_light_woocommerce_debug( $text ) {

    // error_log( "========= " . $text);
  }

  /**
   * Prints a message in the outputed HTML with an easy to notice prefix.
   * @param string $prefix 
   * @param string $text 
   */
  public function pm_light_woocommerce_debug2( $prefix, $text ) {

    // $output = print_r( $text, true );
    // print_r( '<br />==== ' . $prefix . ': ' . $output );
  }
}
