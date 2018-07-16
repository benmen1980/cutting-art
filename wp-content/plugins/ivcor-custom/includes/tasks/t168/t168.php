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
add_filter('woocommerce_cart_item_remove_link', 't168_woocommerce_cart_item_remove_link', 10, 2);
/**
 * end hooks
 */

function t168_woocommerce_cart_item_remove_link($value, $cart_item_key){
    if (is_cart()) {
        return sprintf(
            '<a href="%s" aria-label="" style="text-align: center" data-product_id="" data-product_sku="">Remove<br>Product</a>',
            esc_url(wc_get_cart_remove_url($cart_item_key)));
    }
    return $value;

}