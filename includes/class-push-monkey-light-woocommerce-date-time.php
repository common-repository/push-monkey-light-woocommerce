<?php
/**
 * Extend DateTime to ensure PHP 5.2 compatibility
 */
class PM_Light_WooCommerce_Date_Time extends DateTime {

    /**
     * Creates a from format.
     *
     * @param str $format The format
     * @param str $time The time
     * @param DateTimeZone $timezone The timezone
     *
     * @return     PM_Light_WooCommerce_Date_Time
     */
    public static function createFromFormat( $format, $time, $timezone = null ) {

        if( ! $timezone ) {

        	$timezone = new DateTimeZone( date_default_timezone_get() );
        }
        if ( method_exists( 'DateTime', 'createFromFormat' ) ) {
        	
        	return parent::createFromFormat( $format, $time, $timezone );
        }
        return new PM_Light_WooCommerce_Date_Time( date( $format, strtotime( $time ) ), $timezone );
    }

    /**
     * Gets the timestamp.
     *
     * @return str The timestamp.
     */
    public function getTimestamp() {

         return method_exists( 'DateTime', 'getTimestamp' ) ? 

             parent::getTimestamp() : $this->format( 'U' );
    }
}