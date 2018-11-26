<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$option = get_option('ivcor_custom_functions');
$task = str_replace('.php', '', basename(__FILE__));

if ( !isset($option) || !isset($option[$task]) || !$option[$task] ) {
    return;
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