<?php
/*
    Plugin name: Priority 18 API
*/

// add_action('admin_menu', 'priority18api_admin_menu');

// function priority18api_admin_menu() {
//     add_menu_page( 'Priority 18 API', 'Priority 18 API', 'administrator', '', 'priority18api_api_panel_page');
//     add_submenu_page('priority18api/priority18api.php', 'Test Unit', 'Test Unit', 'manage_options', 'priority18api/test-unit.php');
//     add_options_page('Priority 18 API', 'Priority 18 API', 'manage_options', 'priority18api/priority18api.php');
// }

function priority18api_admin_head() {
        echo '<link rel="stylesheet" type="text/css" href="' .plugins_url('style.css', __FILE__). '">';
}
add_action('admin_head', 'priority18api_admin_head');



function priority18api_api_panel_page()
{
    ?>
    <div class="wrap">
        <h1>fdgdfgfdgdfgfdgdfgdfgdfgdfgdfgdfgdfgdfg</h1>
        <form action="options.php" method="post">
            <?php
            // output security fields for the registered setting "wporg_options"
            //settings_fields('wporg_options');
            // output setting sections and their fields
            // (sections are registered for "wporg", each field is registered to a specific section)
            //do_settings_sections('wporg');
            // output save settings button
            //submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function wporg_options_page()
{
    add_menu_page( 'Priority 18 API', 'Priority 18 API', 'administrator', 'priority18api/priority18api.php', 'priority18api_api_panel_page');
}
add_action('admin_menu', 'wporg_options_page');
?>