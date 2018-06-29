<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class IvcorCustom
{
    /**
     * IvcorCustom constructor.
     */
    public function __construct()
    {
        $tasks = $this->get_tasks();
        foreach ($tasks as $task) {
            include_once dirname( __FILE__ ) . "/tasks/$task/$task.php";
        }

        $this->init_hooks();
    }

    /**
     *
     */
    private function init_hooks()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     *
     */
    public function admin_menu()
    {
        add_submenu_page('options-general.php', 'Custom Functions', 'Custom Functions', 'manage_options', 'ivcor_custom_functions', [$this, 'ivcor_custom_functions_page']);
    }

    /**
     *
     */
    public function ivcor_custom_functions_page()
    {
        $tasks = $this->get_tasks();

        if (empty($tasks)) return;

        if (!empty($_POST)){
            $options = [];
            foreach ($tasks as $task) {
                $options[$task] = isset($_POST[$task]) ? 1 : 0;
            }
            update_option('ivcor_custom_functions', $options);
        }else{
            $options = get_option('ivcor_custom_functions');
        }

        ?>
        <div class="wrap">
            <h1>IVCOR Custom Functions</h1>
            <form method="post" action="">
                <table class="form-table">
                    <tbody>
                    <?php foreach ($tasks as $task) { ?>
                        <tr>
                            <th scope="row"><?=strtoupper($task)?></th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text">
                                        <span><?=strtoupper($task)?></span>
                                    </legend>
                                    <label for="<?=$task?>">
                                        <input name="<?=$task?>" type="checkbox" id="<?=$task?>" value="1" <?=checked($options[$task],1)?>>
                                    </label>
                                </fieldset>
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

    /**
     * @return array
     */
    private function get_tasks()
    {
        $tasks = [];
        $files = scandir(dirname( __FILE__ ) . '/tasks');

        foreach ($files as $file) {
            if (strpos($file, '.') !== 0) $tasks[] = $file;
        }

        return $tasks;
    }
}