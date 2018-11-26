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
add_action( 'wp_enqueue_scripts', 't211_wp_enqueue_scripts' );

add_filter( 'wc_epo_builder_after_element_array', 't211_wc_epo_builder_after_element_array', 10);
add_filter( 'wc_epo_builder_element_start_args', 't211_wc_epo_builder_element_start_args', 10, 2);

add_action( 'admin_menu', 't211_admin_menu');
/**
 * end hooks
 */

function t211_admin_menu(){
    add_submenu_page('options-general.php', 'Languages', 'Languages', 'manage_options', 't211_languages', 't211_languages_page');
}

function t211_languages_page(){
    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $languages = wp_get_available_translations();
    $lngs = [];

    if (!isset($languages['en'])) {
        $languages = array_merge($languages, ['en' => ['english_name' => 'English (United States)', 'language' => 'en']]);
        ksort($languages);
    }

    foreach ($languages as $language)
        $lngs[] = [
            'text' => $language['english_name'],
            'value' => $language['language']
        ];

    if (!empty($_POST)){
        $options = [];
        $options_space = [];
        foreach ($lngs as $language) {
            $options[$language['value']] = isset($_POST[$language['value']]) ? $_POST[$language['value']] : '';
            $options_space[$language['value'] . '_space_symbol'] = isset($_POST[$language['value'] . '_space_symbol']) ? 1 : 0;
        }
        update_option('ivcor_languages_code', $options);
        update_option('ivcor_languages_code_space_symbol', $options_space);
    }else{
        $options = get_option('ivcor_languages_code');
        $options_space = get_option('ivcor_languages_code_space_symbol');
    }

    ?>
    <div class="wrap">
        <h1>Languages</h1>
        <form method="post" action="">
            <table class="form-table">
                <tbody>
                <?php foreach ($lngs as $language) { ?>
                    <tr>
                        <th scope="row"><?=$language['text']?></th>
                        <td>
                            <input name="<?=$language['value']?>" type="text" id="ivcor_<?=$language['value']?>" value="<?=(isset($options[$language['value']]) ? $options[$language['value']] : '')?>">
                            <?php
                                $checked = isset($options_space[$language['value'] . '_space_symbol']) ? checked($options_space[$language['value'] . '_space_symbol'], 1, 0) : '';
                            ?>
                            Space Symbol <input name="<?=$language['value']?>_space_symbol" type="checkbox" id="ivcor_<?=$language['value']?>_space_symbol" style="vertical-align: sub" <?=$checked?>>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}

function t211_wp_enqueue_scripts(){
    $ver = time();
    $path_assets = plugin_dir_url(__FILE__) . '/assets/';
    if (is_product()) {

        require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
        $languages = wp_get_available_translations();
        $lngs = [];

        if (!isset($languages['en'])) {
            $languages = array_merge($languages, ['en' => ['english_name' => 'English (United States)', 'language' => 'en']]);
            ksort($languages);
        }

        wp_enqueue_script('t211_front_js', $path_assets . 'js/front.js', ['jquery'], $ver, true);
        wp_localize_script('t211_front_js', 't211', [
                'languages' => get_option('ivcor_languages_code'),
                'languagesName' => $languages,
                'languageSpaceSymbol' => get_option('ivcor_languages_code_space_symbol')
        ]);
    }

}

function t211_wc_epo_builder_after_element_array($elements_array){

    require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
    $languages = wp_get_available_translations();
    $lngs = [];
    $lngs[] = [
        'text' => 'No validation',
        'value' => 0
    ];
    if (!isset($languages['en'])) {
        $languages = array_merge($languages, ['en' => ['english_name' => 'English (United States)', 'language' => 'en']]);
        ksort($languages);
    }

    $options = get_option('ivcor_languages_code');

    foreach ($languages as $language)
        if (isset($options[$language['language']]) && $options[$language['language']])
            $lngs[] = [
                'text' => $language['english_name'],
                'value' => $language['language']
            ];

    $name = 'textfield';

    array_splice($elements_array['textfield'], 40, 0, [[
        "id"          => $name . "_validation_language",
        "wpmldisable" => 1,
        "default"     => "",
        "type"        => "select",
        "tags"        => array( "id" => "builder_" . $name . "_validation_language", "name" => "tm_meta[tmfbuilder][" . $name . "_validation_language][]" ),
        "options"     => $lngs,
        "label"       => __( 'Validate as Language', 'woocommerce-tm-extra-product-options' ),
        "desc"        => __( '', 'woocommerce-tm-extra-product-options' ),
    ]]);

    return $elements_array;
}

$GLOBALS['tm_counter'] = 0;

function t211_wc_epo_builder_element_start_args($args, $element){

    $tm_validation = json_decode( stripslashes(html_entity_decode($args['tm_validation'])), true);

    if (isset($element['builder']['textfield_validation_language']) && $args['element'] === 'textfield') {
        $tm_validation = array_merge($tm_validation, ['language' => $element['builder']['textfield_validation_language'][$GLOBALS['tm_counter']]]);
    }

    $args['tm_validation'] = esc_html(json_encode($tm_validation));

    return $args;
}
