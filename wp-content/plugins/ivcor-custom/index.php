<?php
/**
 * Plugin Name: IVCOR Custom Functions
 * Plugin URI: https://ivcor.com/
 * Description: IVCOR Custom Functions
 * Version: 0.0.1
 * Author: Denis
 * Author URI: https://ivcor.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'IvcorCustom' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-ivcor-custom.php';
}

$GLOBALS['IvcorCustom'] = new IvcorCustom();