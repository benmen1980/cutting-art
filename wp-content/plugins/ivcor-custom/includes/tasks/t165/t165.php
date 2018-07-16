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
add_action('storefront_header','t165_storefront_header',10);
/**
 * end hooks
 */

function t165_storefront_header() {
    $logout_url = wp_logout_url();
?>
    <style>
        #t165_logout_button {
            float: right;
        }
        #t165_logout_button:before {
            content: "\f2f5";
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: inline-block;
            font-style: normal;
            font-variant: normal;
            font-weight: normal;
            line-height: 1;
            vertical-align: -.125em;
            font-family: 'Font Awesome 5 Free';
            font-weight: 900;
            line-height: inherit;
            vertical-align: baseline;
            line-height: 1.618;
            margin-left: 0.5407911001em;
            width: 1.41575em;
            text-align: right;
            float: right;
        }
    </style>
    <a id="t165_logout_button" href="<?=$logout_url?>">Logout</a>
<?php
}