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
add_action( "admin_enqueue_scripts", "t131_admin_enqueue_scripts" );
add_action( 'wp_ajax_t131_get_admin_url_product', 't131_get_admin_url_product');
/**
 * end hooks
 */

function t131_admin_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    wp_localize_script('jquery', 't131', ['ajaxUrl' => admin_url('admin-ajax.php')]);

    if (isset($_GET['page']) && $_GET['page'] === 'priority-woocommerce-api') {
        wp_enqueue_script('t131_admin_js', $path_assets . 'js/admin.js', ['jquery'], $ver, true);
        wp_enqueue_style( 't131_admin_css', $path_assets . 'css/admin.css', $ver);
    }
}

function t131_get_admin_url_product() {
    $res = [];
    $res['product_id'] = wc_get_product_id_by_sku($_POST['sku']);
    $res['product'] = get_post($res['product_id']);
    if ($res['product']->post_parent !== 0)
        $res['product_id'] = $res['product']->post_parent;
    $res['url'] = get_edit_post_link($res['product_id'], '');
    wp_die(json_encode($res));
}