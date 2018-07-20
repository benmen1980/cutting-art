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
add_filter( 'posts_join', 't159_posts_join', 10, 2 );
add_filter( 'posts_where', 't159_posts_where', 10, 2 );
add_filter( 'the_posts', 't159_the_posts', 10, 2);
/**
 * end hooks
 */


function t159_posts_join( $join, $query ) {
    if ( ! $query->is_main_query() || is_admin() || ! is_search() || ! is_woocommerce() ) {
        return $join;
    }

    global $wpdb;

    $join .= " LEFT JOIN {$wpdb->postmeta} t159_post_meta ON {$wpdb->posts}.ID = t159_post_meta.post_id ";

    return $join;
}

function t159_posts_where( $where, $query ) {
    if ( ! $query->is_main_query() || is_admin() || ! is_search() || ! is_woocommerce() ) {
        return $where;
    }

    global $wpdb;

    $where = preg_replace(
        "/\(\s*{$wpdb->posts}.post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
        "({$wpdb->posts}.post_title LIKE $1) OR (t159_post_meta.meta_key = '_sku' AND t159_post_meta.meta_value LIKE $1)", $where );

    return $where;
}

function t159_the_posts($posts, $query) {
    if ( ! $query->is_main_query() || is_admin() || ! is_search() || ! is_woocommerce() ) {
        return $posts;
    }

    if (empty($posts)) {
        global $wpdb;

        $query_posts = $wpdb->get_results("SELECT t159_posts.ID, t159_posts.post_parent
FROM {$wpdb->posts} t159_posts
LEFT JOIN {$wpdb->postmeta} t159_postmeta ON t159_posts.ID = t159_postmeta.post_id
WHERE 1=1 AND t159_posts.post_type = 'product_variation' AND t159_postmeta.meta_key = '_sku' AND t159_postmeta.meta_value LIKE '{$_GET['s']}'");

        foreach ($query_posts as $post) {
            $posts[] = get_post($post->post_parent);
        }

        $query->found_posts = count($query_posts);
    }

    return $posts;
}

