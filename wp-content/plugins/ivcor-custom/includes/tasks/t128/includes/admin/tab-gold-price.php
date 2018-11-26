<?php defined('ABSPATH') or die('No direct script access!'); ?>
<div class="wrap">
    <?php

    $options = get_option('cutting_art_gold_price');

    $gold_based_price = $options['gold_based_price'] ? $options['gold_based_price'] : 0;
    $current_gold_price = $options['current_gold_price'] ? $options['current_gold_price'] : 0;
    $extra_proc = $options['extra_proc'] ? $options['extra_proc'] : 0;

    if ($_POST) {
        update_option('cutting_art_gold_price', $_POST);

        if (isset($_POST['gold_based_price']) && $_POST['gold_based_price']) {
            $gold_based_price = $_POST['gold_based_price'];
        }
        if (isset($_POST['current_gold_price']) && $_POST['current_gold_price']) {
            $current_gold_price = $_POST['current_gold_price'];
        }
        if (isset($_POST['extra_proc']) && $_POST['extra_proc']) {
            $extra_proc = $_POST['extra_proc'];
        }
    }

    ?>
    <h1>
        <?php echo CTA_PLUGIN_NAME; ?>
        <span id="p18a_version"><?php echo CTA_VERSION; ?></span>
    </h1>

    <br />

    <div id="p18a_tabs_menu">
        <ul>
            <li>
                <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL); ?>">
                    <?php _e('Parameters', 'cta'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-types'); ?>">
                    <?php _e('Conversion types', 'cta'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-table'); ?>">
                    <?php _e('Conversion table', 'cta'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo admin_url('admin.php?page=tab_gold_price'); ?>" class="active">
                    <?php _e('Gold Price', 'cta'); ?>
                </a>
            </li>
        </ul>
    </div>

    <div class="p18a-page-wrapper">
        <form method="post" action="<?php echo admin_url('admin.php?page=tab_gold_price'); ?>">
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="gold_based_price">Gold Based Price</label></th>
                        <td><input name="gold_based_price" type="number" id="gold_based_price" value="<?=$gold_based_price?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="current_gold_price">Current Gold Price</label></th>
                        <td><input name="current_gold_price" type="number" id="current_gold_price" value="<?=$current_gold_price?>"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="extra_proc">extra %</label></th>
                        <td><input readonly name="extra_proc" type="number" id="extra_proc" value="<?=$extra_proc?>"></td>
                    </tr>
                </tbody>
            </table>

            <input type="submit" name="save_gold_price" value="Save" class="button button-primary">
        </form>
    </div>

</div>