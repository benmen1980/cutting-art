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
add_action('wp_enqueue_scripts', 't204_wp_enqueue_scripts' );
/**
 * end hooks
 */

function t204_wp_enqueue_scripts() {

    $tm_meta = get_post_meta(get_the_ID(), 'tm_meta', true);
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';
    if (is_product()) {
        wp_enqueue_script('t204_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        if (isset($tm_meta['tmfbuilder']) && isset($tm_meta['tmfbuilder']['selectbox_header_title']))
            wp_localize_script('t204_front_js', 't204', ['tmMeta' => $tm_meta['tmfbuilder']['selectbox_header_title']]);
    }
}

/*function t198_woocommerce_variation_options($loop, $variation_data, $variation){
?>
    <p class="form-field variable_product_code[<?=$loop?>]_field form-row form-row-last">
        <label for="variable_product_code[<?=$loop?>]">
            <abbr title="Product Code">Product Code</abbr>
        </label>
        <input type="text" class="short" style="" name="variable_product_code[<?=$loop?>]" id="variable_product_code<?=$loop?>" value="<?=get_post_meta($variation->ID,'product_code', true)?>" placeholder="">
    </p>
<?php
}

function t198_woocommerce_before_order_itemmeta($item_id, $item, $product){

    if ( method_exists($item, 'get_variation_id') ) {
        $variation_id = $item->get_variation_id();
        ?>
        <div class="wc-order-item-variation"><strong>Product Code:</strong> <?=get_post_meta($variation_id,'product_code', true)?></div>
        <?php
    }
}

function t198_woocommerce_save_product_variation($variation_id){
    $product_code = $_POST["variable_product_code"];
    if(!empty($product_code))
        update_post_meta( $variation_id, 'product_code', sanitize_text_field($product_code[0]) );
}*/