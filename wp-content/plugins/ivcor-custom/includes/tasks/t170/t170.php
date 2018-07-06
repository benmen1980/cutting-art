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
add_action( "woocommerce_after_shipping_calculator", "t170_woocommerce_after_shipping_calculator");
add_filter( "woocommerce_shipping_settings", "t170_woocommerce_shipping_settings");
/**
 * end hooks
 */

function t170_woocommerce_after_shipping_calculator() {
    $page_id = get_option('woocommerce_shipping_page_detail');
    $page_id = $page_id ? $page_id : 0;
    if (!$page_id) return;

    $page_link = get_permalink($page_id);
?>
    <a href="<?=$page_link?>">Shipping Options - Details</a>
<?php
}

function t170_woocommerce_shipping_settings($fields) {

    $last_field = array_pop($fields);

    $pages_query = new WP_Query([
        'post_type' => 'page',
        'posts_per_page' => -1,
        'post_status' => 'publish'
    ]);

    $pages = $pages_query->posts;

    foreach ($pages as $page) {
        $options[$page->ID] = $page->post_title;
    }

    $fields[] = [
        'title'           => __( 'Shipping Page Detail', 'woocommerce' ),
        'desc'            => __( '', 'woocommerce' ),
        'id'              => 'woocommerce_shipping_page_detail',
        /*'default'         => 'billing',*/
        'type'            => 'select',
        'options'         => $options,
        'autoload'        => false,
        'desc_tip'        => true,
        'show_if_checked' => 'option',
    ];

    $fields[] = $last_field;

    return $fields;
}