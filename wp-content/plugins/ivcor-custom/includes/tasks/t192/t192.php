<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$option = get_option('ivcor_custom_functions');
$task = str_replace('.php', '', basename(__FILE__));

if ( !isset($option) || !isset($option[$task]) || !$option[$task] ) {
    return;
}

/**
 * hooks
 */
add_action('woocommerce_after_single_product_summary', 't192_woocommerce_after_single_product_summary', 0);
/**
 * end hooks
 */

function t192_woocommerce_after_single_product_summary(){
    remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
}