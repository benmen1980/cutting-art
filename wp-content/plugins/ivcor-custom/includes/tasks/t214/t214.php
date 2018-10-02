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
add_action( 'product_cat_add_form_fields', 't214_product_cat_add_form_fields', 99, 2);
add_action( 'product_cat_edit_form_fields', 't214_product_cat_edit_form_fields', 99, 2);

add_action( 'edited_product_cat', 't214_save_product_cat', 10, 2);
add_action( 'create_product_cat', 't214_save_product_cat', 10, 2);

add_action( 'show_user_profile', 't214_show_user_profile', 99 );
add_action( 'edit_user_profile', 't214_show_user_profile', 99 );

add_action( 'personal_options_update', 't214_save_user_profile' );
add_action( 'edit_user_profile_update', 't214_save_user_profile' );
/**
 * end hooks
 */

function t214_product_cat_add_form_fields() {
    ?>
    <div class="form-field">
        <label for="attribute_display_category"><?php esc_html_e( 'Display Category for Customers?', 'woocommerce' ); ?></label>
        <input type="checkbox" name="attribute_display_category" id="attribute_display_category">
        <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
    </div>
    <?php
}

function t214_product_cat_edit_form_fields($term) {

    global $wpdb;

    $term_id = $term->term_id;
    $table_name = "{$wpdb->prefix}woocommerce_termmeta";

    $attribute_display_category = $wpdb->get_var( "SELECT meta_value FROM $table_name WHERE woocommerce_term_id = $term_id AND meta_key = '_attribute_display_category'");

    if (is_null($attribute_display_category)) $attribute_display_category = 1;

    ?>
    <tr class="form-field form-required">
        <th scope="row" valign="top">
            <label for="attribute_display_category"><?php esc_html_e( 'Display Category for Customers?', 'woocommerce' ); ?></label>
        </th>
        <td>
            <input name="attribute_display_category" id="attribute_display_category" type="checkbox" <?=checked($attribute_display_category, 1)?> value="1" />
            <p class="description"><?php esc_html_e( '', 'woocommerce' ); ?></p>
        </td>
    </tr>
    <?php
}

function t214_save_product_cat($term_id) {
    global $wpdb;

    $table_name = "{$wpdb->prefix}woocommerce_termmeta";

    if (isset($_POST['attribute_display_category']) && $_POST['attribute_display_category'] == 1) {
        $update = $wpdb->update( $table_name,
            array( 'meta_key' => '_attribute_display_category', 'meta_value' => $_POST['attribute_display_category'] ),
            array( 'woocommerce_term_id' => $term_id )
        );

        if ($update === 0) {
            $wpdb->insert( $table_name,
                array( 'woocommerce_term_id' => $term_id, 'meta_key' => '_attribute_display_category', 'meta_value' => $_POST['attribute_display_category'] )
            );
        }
    } else {
        $update = $wpdb->update( $table_name,
            array( 'meta_key' => '_attribute_display_category', 'meta_value' => 0 ),
            array( 'woocommerce_term_id' => $term_id )
        );

        if ($update === 0) {
            $wpdb->insert( $table_name,
                array( 'woocommerce_term_id' => $term_id, 'meta_key' => '_attribute_display_category', 'meta_value' => 0 )
            );
        }
    }
}

function t214_show_user_profile($user) {

    $product_cat = get_user_meta($user->ID, '_display_product_cat', true);

    global $wpdb;
    $term_ids = $wpdb->get_col("SELECT woocommerce_term_id as term_id FROM {$wpdb->prefix}woocommerce_termmeta WHERE meta_key = '_attribute_display_category' AND meta_value = '0'");
    if (!$term_ids) $term_ids = []; else $term_ids = array_unique($term_ids);

    if (is_array($product_cat)) {
        $selected_cats = $product_cat;
    }else{
        $selected_cats = get_terms([
            'hide_empty' => 0,
            'fields' => 'ids',
            'taxonomy' => 'product_cat'
        ]);
    }


    $args = [
        'taxonomy' => 'product_cat',
        'selected_cats' => $selected_cats,
        'popular_cats' => $term_ids
    ];

    ?>
    <h3>Categories for Customer</h3>
    <style>ul.children {margin-left: 20px;}li.popular-category > label {display: none;}</style>
    <ul><?php wp_terms_checklist( 0, $args ); ?></ul>
    <?php
}

function t214_save_user_profile($user_id) {
    if ( !current_user_can( 'edit_user', $user_id ) )
        return false;

    $product_cat = [];

    if (isset($_POST['tax_input']) && isset($_POST['tax_input']['product_cat'])) $product_cat = $_POST['tax_input']['product_cat'];

    update_user_meta($user_id, '_display_product_cat', $product_cat);
}