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
add_filter( 'login_redirect', 't163_login_redirect', 10, 3 );
/**
 * end hooks
 */

function t163_login_redirect( $redirect_to, $request, $user ) {

    if (method_exists($user, 'has_cap') && $user->has_cap('customer')) {
        return home_url();
    }else{
        return $redirect_to;
    }
}

