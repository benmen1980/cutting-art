<?php defined('ABSPATH') or die('No direct script access!'); ?>

<div class="wrap">

    <?php require CTA_ADMIN_DIR . 'tabs.php'; ?>

    <div class="p18a-page-wrapper">

        <?php



            // list table
            $list = new CuttingArt\ListTable();

  
            // types
            $types = $this->getConversionTypes();

            // conversions
            $conversions = [];
            
            foreach($this->getConversions() as $conversion) {
                $conversions[$conversion['type_id']][$conversion['size_id']] = $conversion;
            }

   
            // add columns
            $columns = ['size' => __('Ring size', 'cta')];

            // add dynamic columns - sizes
            foreach($types as $type) {

                $id = $type->id;

                $columns['conversion_' . $id] = sprintf('%s %s', $type->name, __('size', 'cta'));

                // add filter for current column
                $list->filter('conversion_' . $id, function($item, $name) use($id, $conversions){

                    if (isset($conversions[$id][$item['id']])) {
                        return $conversions[$id][$item['id']]['size'];
                    }

                });

            }

            // rest of the columns
            $columns['circumference_mm'] = __('Circumference mm', 'cta');
            $columns['circumference_inch'] = __('Circumference inch', 'cta');
            $columns['diameter'] = __('Diameter', 'cta');
            $columns['actions'] = __('Actions', 'cta');            
    

            $list->columns($columns);

                        
            // default filter
            $list->filter(['size', 'circumference_mm', 'circumference_inch', 'diameter'], function($item, $name) use($types, $conversions){
                return $item[$name];
            });

            
            $list->filter('actions', function($item, $name) {

                $edit_url = admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=edit-conversion&id=' .  $item['id']);
                $delete_url = wp_nonce_url(admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=conversion-table&delete=' .  $item['id']), 'delete_conversion', 'cta');

                return '<a href="' . $edit_url . '" class="button">' . __('Edit', 'cta') . '</a> &nbsp; ' . 
                       '<a href="' . $delete_url . '" class="button cta-delete">' . __('Delete', 'cta') . '</a>';
            });


            global $wpdb;

            $data = $wpdb->get_results('
                SELECT * FROM ' . $wpdb->prefix . 'cta_sizes as sizes', 
                ARRAY_A
            );

            $list->show($data);

        ?>

        <a href="<?php echo admin_url('admin.php?page=' . CTA_PLUGIN_ADMIN_URL . '&tab=add-conversion'); ?>" class="button button-primary"><?php _e('Add conversion', 'cta'); ?></a>

    </div>

</div>