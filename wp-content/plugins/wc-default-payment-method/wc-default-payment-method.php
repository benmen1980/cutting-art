<?php
/**
 * Plugin Name: IVCOR WooCommerce Default Payment Method
 * Plugin URI: https://ivcor.com/
 * Description: WooCommerce Default Payment Method for Users and special Price for User
 * Version: 0.0.1
 * Author: Denis
 * Author URI: https://ivcor.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main class.
if ( ! class_exists( 'WC_DPM' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-wc-dpm.php';
}

/**
 * Main instance.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 * @since  2.1
 * @return WooCommerce
 */
function wc_dpm() {
    return WC_DPM::instance();
}

// Global for backwards compatibility.
$GLOBALS['wc_dpm'] = wc_dpm();