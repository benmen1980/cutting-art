<?php defined('ABSPATH') or die('No direct script access!'); ?>

<div class="wrap">

    <?php require CTA_ADMIN_DIR . 'tabs.php'; ?>

    <div class="p18a-page-wrapper">

        <h2><?php _e('Edit conversion', 'cta'); ?></h2>

        <form name="edit_conversion" method="post" action="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-table'); ?>">

            <?php wp_nonce_field('edit_conversion', 'cta'); ?>
            
            <input type="hidden" name="conversion_id" value="<?php echo $data->id; ?>" />

            <?php 

            $types = $this->getConversionTypes();

            $conversions = [];
            
            foreach($this->getConversions() as $conversion) {
                $conversions[$conversion['type_id']][$conversion['size_id']] = $conversion;
            }

            
            ?>

            
            <table id="cta_add_param_table">

                <tr>
                    <td>
                        <?php _e('Ring size', 'cta'); ?> 
                    </td>
                    <td>
                        <input type="text" name="size" value="<?php echo $data->id; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php _e('Circumference mm', 'cta'); ?> 
                    </td>
                    <td>
                        <input type="text" name="circumference_mm" value="<?php echo $data->circumference_mm; ?>" />
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php _e('Circumference inch', 'cta'); ?> 
                    </td>
                    <td>
                        <input type="text" name="circumference_inch" value="<?php echo $data->circumference_inch; ?>"  />
                    </td>
                </tr>

                <tr>
                    <td>
                        <?php _e('Diameter', 'cta'); ?> 
                    </td>
                    <td>
                        <input type="text" name="diameter" value="<?php echo $data->diameter; ?>"  />
                    </td>
                </tr>

                <?php foreach($this->getConversionTypes() as $type):  ?>
                    <tr>
                        <td>
                            <?php echo $type->name; ?> <?php _e('Size', 'cta'); ?> <?php echo $type->code; ?> 
                        </td>
                        <td>                        
                            <input type="text" name="conversion_size[<?php echo $type->id; ?>]" value="<?php if(isset($conversions[$type->id][$data->id])) echo $conversions[$type->id][$data->id]['size']; ?>" />
                        </td>
                    </tr>
                <?php endforeach; ?>


            </table>

            <br>

            <input type="submit" name="edit_conversion" value="<?php _e('Save'); ?>" class="button button-primary">

        </form>



    </div>

</div>