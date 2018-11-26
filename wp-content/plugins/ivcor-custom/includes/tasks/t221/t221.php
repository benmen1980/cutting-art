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
add_filter( 'wc_tax_enabled', 't221_wc_tax_enabled', 10);
/**
 * end hooks
 */

function t221_wc_tax_enabled( $tax_enabled ){
    $price_display = get_user_meta(get_current_user_id(), '_price_display', true);
    if ($price_display === 'retail')
        $tax_enabled = 0;

    global $wp;
    if ( is_checkout() && !empty( $wp->query_vars['order-received'] ) ) {
        $tax_enabled = 1;
    }

    return $tax_enabled;
}