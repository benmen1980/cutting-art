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
add_filter('default_checkout_shipping_first_name',      't212_default_checkout');
add_filter('default_checkout_shipping_last_name',       't212_default_checkout');
add_filter('default_checkout_shipping_company',         't212_default_checkout');
add_filter('default_checkout_shipping_country_field',   't212_default_checkout');
add_filter('default_checkout_shipping_address_1',       't212_default_checkout');
add_filter('default_checkout_shipping_address_2',       't212_default_checkout');
add_filter('default_checkout_shipping_city',            't212_default_checkout');
add_filter('default_checkout_shipping_state',           't212_default_checkout');
add_filter('default_checkout_shipping_postcode',        't212_default_checkout');
/**
 * end hooks
 */

function t212_default_checkout() {
    return '';
}


