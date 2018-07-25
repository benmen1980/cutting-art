<?php
/**
 * Plugin Name: IVCOR Jobs Tab for Cutting Art
 * Plugin URI: https://ivcor.com/
 * Description: IVCOR Jobs Tab for Cutting Art
 * Version: 0.0.1
 * Author: Denis
 * Author URI: https://ivcor.com
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Include the main class.
if ( ! class_exists( 'CA_JOBS' ) ) {
    include_once dirname(__FILE__) . '/includes/class-ca-jobs.php';
}

new CA_JOBS();