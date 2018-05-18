<?php defined('ABSPATH') or die('No direct script access!'); 

global $post;

$parameters = $this->getProductParameters($post->ID);

?>

<?php if($parameters): ?>

                
    <?php foreach($parameters as $parameter): ?>

        <div style="margin-bottom: 15px;">

            <?php if($parameter->type == 'dropdown'): ?>

                <?php if($parameter->use_conversion) : ?>
        
                    <label for="cta_parameter_<?php echo $parameter->id; ?>">
                        <a style="float:right;" href="#" id="show-conversion-table"><?php _e('View our ring size guide', 'cta'); ?></a>
                        <?php _e($parameter->name, 'cta'); ?>
                    </label> <br />

                <?php else: ?>

                    <label for="cta_parameter_<?php echo $parameter->id; ?>"><?php echo $parameter->name; ?></label> <br />                

                <?php endif; ?>

                <select id="cta_parameter_<?php echo $parameter->id; ?>" name="cta_parameters[<?php echo $parameter->id; ?>]" required>

                    <?php foreach($this->getParametersMeta($post->ID, $parameter->id) as $data): ?>

                        <?php if($parameter->use_conversion) : ?>

                            <?php if($value = $this->getConvertedValue($data->meta_value)) :  ?>

                                <option value="<?php echo $data->meta_value; ?>"><?php echo $value; ?></option>

                            <?php else: ?>

                                <option value="<?php echo $data->meta_value; ?>"><?php echo $data->meta_value; ?></option>

                            <?php endif; ?>

                        <?php else: ?>

                            <option value="<?php echo $data->meta_value; ?>"><?php echo $data->meta_value; ?></option>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </select>

            <?php elseif($parameter->type == 'numeric'): ?>

                <input id="cta_parameter_<?php echo $parameter->id; ?>" type="number" name="cta_parameters[<?php echo $parameter->id; ?>]" value="" required />

            <?php else: ?>

                <input id="cta_parameter_<?php echo $parameter->id; ?>" type="text" name="cta_parameters[<?php echo $parameter->id; ?>]" value="" required />

            <?php endif; ?>

        </div>


    <?php endforeach; ?>


<?php endif; ?>


<?php if($sizes = $this->getDefaultSizes()): 
    
    // conversions
    $conversions = [];

    foreach($this->getConversions() as $conversion) {
        $conversions[$conversion['type_id']][$conversion['size_id']] = $conversion;
    }        
    
    ?>

    <div style="display:none;" id="conversion-table">

        <?php $conversionTypes = $this->getConversionTypes();  ?>

        <h2 style="text-align:center;"><?php _e('Ring size guide', 'cta'); ?></h2>
        <table id="contbl">
            <tr>
                <?php foreach($conversionTypes as $type): ?>
                    <td>
                        <?php _e($type->name, 'cta'); ?> <?php _e('size', 'cta'); ?></strong>
                    </td>
                <?php endforeach; ?>

                <?php /*
                <td><?php _e('Circumreference mm', 'cta'); ?></td>
                <td><?php _e('Circumreference inch', 'cta'); ?></td>
                <td><?php _e('Diameter', 'cta'); ?></td>
                */ ?>
            </tr>
        <?php foreach($sizes as $size): ?>
            <tr>
                <?php foreach($conversionTypes as $i => $type): ?>
                    <td>
                        <?php if(isset($conversions[$type->id][$size->id])): ?>

                            <?php echo $conversions[$type->id][$size->id]['size']; ?>

                        <?php endif; ?>
                    </td>
                <?php endforeach; ?>
                <?php /*
                <td>
                    <?php echo $size->circumference_mm; ?>
                </td>
                <td>
                    <?php echo $size->circumference_inch; ?>
                </td>
                <td>
                    <?php echo $size->diameter; ?>
                </td>
                */ ?>
            </tr>
        <?php endforeach; ?>
        </table>
    </div>

<?php endif; ?>