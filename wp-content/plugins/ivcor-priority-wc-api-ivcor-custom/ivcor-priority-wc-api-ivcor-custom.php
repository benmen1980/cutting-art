<?php
/**
 * Plugin Name: IVCOR Priority WC API
 * Plugin URI: https://ivcor.com/
 * Description: Priority WC API IVCOR
 * Version: 0.0.1
 * Author: Denis
 * Author URI: https://ivcor.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main class.
if ( ! class_exists( 'WC_API_CUSTOM' ) ) {
    include_once dirname(__FILE__) . '/includes/class-wc-api-custom.php';
}
if ( ! class_exists( 'WCAC_DB' ) ) {
    include_once dirname(__FILE__) . '/includes/class-wcac-db.php';
}

/**
 * @return wc_api_custom
 */
function wc_api_custom() {
    return WC_API_CUSTOM::instance();
}

/**
 * @return wc_api_custom
 */
function wcac_db() {
    return WCAC_DB::instance();
}

// Global for backwards compatibility.
$GLOBALS['wc_api_custom'] = wc_api_custom();
$GLOBALS['wcac_db'] = wcac_db();