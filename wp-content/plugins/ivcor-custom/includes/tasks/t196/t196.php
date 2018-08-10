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
add_action('woocommerce_cart_collaterals', 't196_woocommerce_cart_collaterals', 0);
/**
 * end hooks
 */

function t196_woocommerce_cart_collaterals(){
    remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');
}
