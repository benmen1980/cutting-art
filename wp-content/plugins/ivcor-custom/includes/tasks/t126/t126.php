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
add_action( "admin_enqueue_scripts", "t126_admin_enqueue_scripts" );
add_action( "wp_ajax_t126_get_external_name_by_term_id", "t126_get_external_name_by_term_id" );
add_action( "edit_terms", "t126_edit_terms" );
add_action( "woocommerce_dropdown_variation_attribute_options_html", "t126_woocommerce_dropdown_variation_attribute_options_html", 10, 2 );
add_action( "woocommerce_get_item_data", "t126_woocommerce_get_item_data", 10, 2);
add_action( "woocommerce_display_item_meta", "t126_woocommerce_display_item_meta", 10, 3);
/**
 * end hooks
 */

function t126_woocommerce_display_item_meta($html, $item, $args) {

    $items = [];

    foreach ($item->get_formatted_meta_data() as $meta_id => $meta ) {
        $term = get_term_by('slug', $meta->value, $meta->key);
        if ($term)
            $external_name = get_option($term->term_id . '_external_name');
        $items[$meta_id] = [
            'key' => $meta->key,
            'value' => $meta->value,
            'display_key' => $meta->display_key,
            'display_value' => $external_name ? $external_name : $meta->display_value
        ];
    }
    $strings = [];
    if ($items)
        foreach ( $items as $meta_id => $meta ) {
            $value     = $args['autop'] ? wp_kses_post( $meta['display_value'] ) : wp_kses_post( make_clickable( trim( $meta['display_value'] ) ) );
            $strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post( $meta['display_key'] ) . ':</strong> ' . $value;
        }

    if ( $strings ) {
        $html = $args['before'] . implode( $args['separator'], $strings ) . $args['after'];
    }

    return $html;
}

function t126_woocommerce_get_item_data( $item_data, $cart_item ) {
    $variation = $cart_item['variation'];

    foreach ($variation as $taxonomy => $attribute) {
        $term = get_term_by('slug', $attribute, str_replace('attribute_', '', $taxonomy));
        $external_name = get_option($term->term_id . '_external_name');
        $variation[$taxonomy] = $external_name ? $external_name : $term->name;
    }

    foreach ($item_data as $key => $data) {
        $slug = 'attribute_pa_' . sanitize_title($data['key']);
        $item_data[$key]['value'] = $variation[$slug] ? $variation[$slug] : $item_data[$key]['value'];
    }

    return $item_data;
}

function t126_woocommerce_dropdown_variation_attribute_options_html($html, $args) {
    $terms = $args['product']->get_attributes()[$args['attribute']]->get_terms();

    $terms = array_map(function($term){
        $external_name = get_option($term->term_id . '_external_name');
        return [
            'old_name' => $term->name,
            'new_name' => $external_name ? $external_name : $term->name
        ];
    }, $terms);

    foreach ($terms as $term) {
        $html = str_replace('>'.$term['old_name'].'<', '>'.$term['new_name'].'<', $html);
        $html = str_replace('data-text-b="'.$term['old_name'].'"', 'data-text-b="'.$term['new_name'].'"', $html);
    }

    return $html;
}

function t126_admin_enqueue_scripts() {
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';

    $screen = get_current_screen();

    if ( $screen->post_type === 'product' && strpos($screen->taxonomy, 'pa_') !== false && ($screen->base === 'edit-tags' || $screen->base === 'term')) {
        wp_enqueue_script('t126_admin_js', $path_assets . 'js/admin.js', ['jquery'], $ver, true);
        wp_localize_script('t126_admin_js', 't126', ['ajaxUrl' => admin_url('admin-ajax.php')]);
    }

}

function t126_get_external_name_by_term_id() {
    $term_id = $_POST['tag_ID'];
    $external_name = get_option($term_id . '_external_name');

    die(json_encode($external_name ? $external_name : ''));
}

function t126_edit_terms() {

    if (isset($_POST['external-name'])) {
        $term_id = $_POST['tag_ID'];
        $external_name = $_POST['external-name'];

        update_option($term_id . '_external_name', $external_name);
    }

}
