<?php
/**
 * Plugin Name: IVCOR Add Contact Us Popup on Login Page
 * Plugin URI: https://ivcor.com/
 * Description: Add Contact Us Popup on Login Page
 * Version: 0.0.1
 * Author: Denis
 * Author URI: https://ivcor.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main class.
if ( ! class_exists( 'CPLP' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-cplp.php';
}

/**
 * Main instance.
 *
 * Returns the main instance of WC to prevent the need to use globals.
 *
 * @since  2.1
 * @return CPLP
 */
function cplp() {
    return CPLP::instance();
}

// Global for backwards compatibility.
$GLOBALS['wc_dpm'] = cplp();