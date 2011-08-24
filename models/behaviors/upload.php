<?php 
/*
 *  
 *  Upload Behavior for CakePHP
 *  
 *  It uses the following class http://www.verot.net/php_class_upload.htm
 *  for processing the upload itself. You might want to take a closer look
 *  at the class if more customization is needed.
 *
 *  Example of a config array used on Model, field_name 
 *  should contain an array of the img width and 
 *  height. Thumbs width and height are optional, if none is given
 *  no thumb will be generated. It is possible to attach as many image fields 
 *  as you want.
 *
 *  The following example would upload the image, resize it but not crop it 
 *  and generate two thumbs.
 *
 *   var $actsAs = array(
 *      'ImageUpload.Upload' => array(
 *          'configs' => array(
 *              'image_ratio' => true,
 *              'image_ratio_crop' => false
 *          ),
 *          'fields' => array(
 *              'field_name' => array(
 *                  300, 300, 50, 50, 30, 30
 *              )
 *          )
 *      ),
 *   );
 *  
 *
 */

class UploadBehavior extends ModelBehavior {
/*
 * Default settings. Crops and resizes image by default.
 */
    var $configs = array(
        'image_resize' => true,
        'image_ratio' => false,
        'image_ratio_crop' => true
    ); 
/*
 * Default image dimensions 
 */
    var $param = 300;
/*
 * The dir name where pics get uploaded 
 */
    var $dir = null;
/*
 *  Set dir name for the pics and the full path to it
 */
    function setup(&$model, array $settings){
         
        $this->dir = Inflector::tableize($model->name);
        $this->fields_array = $settings['fields']; 
        if(!isset($settings['configs']))
            $settings['configs'] = array();
        $this->configs = array_merge($this->configs, $settings['configs']);

        $this->settings[$model->name] = array_merge(
            $this->configs,
            array('path_to_dir' => WWW_ROOT . 'files/' . $this->dir),
            $this->fields_array
        );
    }
/*
 *  Overwrites the FILES array for a null value if no file is being uploaded
 */
    function beforeValidate(&$model){
        foreach($this->fields_array as $field => $configs){
            if(empty($model->data[$model->alias][$field]['name'])){
                $model->data[$model->alias][$field] = null;
            }
        }
        return true;
    }
/*
 *  Process the upload. On edit forms if a new file is uploaded the old one is
 *  removed.
 *  Check number of params and generate thumbs if necessary.
 */
    function beforeSave(&$model){
        
        App::Import('Lib', 'ImageUpload.Upload');
        foreach($this->fields_array as $field => $configs){

            if(!empty($model->data[$model->alias][$field]['name'])){
                
                $handle = new upload($model->data[$model->alias][$field]);
                
                $filename = $this->name($model, $field);

                $handle->file_new_name_body = $filename; 
                $handle->image_resize = $this->settings[$model->alias]['image_resize'];
                $handle->image_ratio = $this->settings[$model->alias]['image_ratio'];
                $handle->image_ratio_crop = $this->settings[$model->alias]['image_ratio_crop'];

                $image_width = $this->settings[$model->alias][$field][0];
                $image_height = $this->settings[$model->alias][$field][1];
                
                $handle->image_x = $this->checkIntParam($image_width);
                $handle->image_y = $this->checkInTParam($image_height);
                
                $this->settings[$model->alias]['path_to_dir']; 
                $handle->process($this->settings[$model->alias]['path_to_dir']);
                if($handle->processed){
                    $model->data[$model->alias][$field] = $filename.'.'.$handle->file_dst_name_ext;
                }else{
                    echo 'File could not be uploaded. '.$handle->error;
                    exit;
                }
                
                if(!isset($model->data[$model->alias]['created'])){
                    $this->delete($model, $field);
                }

                $params_number = count($this->settings[$model->alias][$field]);
                $thumbs = (int)($params_number - 2) / 2;
                $n = 0;
                for($i=0; $i<$thumbs; $i++){
                    
                    $filename_thumb = 'thb'.$i.'_'.$filename;
                    $handle->file_new_name_body = $filename_thumb; 
                    $handle->image_resize = $this->settings[$model->alias]['image_resize'];
                    $handle->image_ratio = $this->settings[$model->alias]['image_ratio'];
                    $handle->image_ratio_crop = $this->settings[$model->alias]['image_ratio_crop'];

                    $thumb_width = $this->settings[$model->alias][$field][2+$n];
                    $thumb_height = $this->settings[$model->alias][$field][3+$n];
                    
                    $handle->image_x = $this->checkIntParam($thumb_width);
                    $handle->image_y = $this->checkInTParam($thumb_height);
                    
                    $handle->process($this->settings[$model->alias]['path_to_dir']);
                    $upThumbImg = $handle->processed;
                    if(!$handle->processed){
                        echo $handle->error;
                        return false;
                    }
                    $n += 2;
                }                

                $handle->Clean();

            }else{
                unset($model->data[$model->alias][$field]);
            }
        }

        return true;
    }
/*
 * Removes all files attached to row.
 */

    function beforeDelete(&$model){
        return $this->delete($model);
    }
/*
 *  Generates name for main image.
 */
    function name(&$model, $field){
        $name = $field.time();
        return $name; 
    }
/*
 *  Removes all files ralated to field_name or all files attached 
 *  to row if no field_name is given.
 */
    function delete(&$model, $field_name=null){
        
        extract($this->settings[$model->alias]);
        foreach($this->fields_array as $field => $configs){
            if($field_name && $field != $field_name){
                continue;
            }
            $filename = $model->field($field); 

            if(!empty($filename)){
                if(!unlink($path_to_dir.'/'.$filename)){
                    return false;
                }
                $params_number = count($this->settings[$model->alias][$field]);
                $thumbs = (int)($params_number - 2) / 2;
                for($i=0; $i<$thumbs; $i++){
                    if(!unlink($path_to_dir.'/thb'.$i.'_'.$filename)){
                        return false;
                    }
                }
            }
        }
        return true;
    }    

/*
 * Checks if user added numeric parameter correctly. If not it returns a 
 * default config dimension. 
 *
 */ 
    function checkIntParam($param){
        if(!is_numeric($param)){
            return $this->param;
        }
        return $param;
    }       
}
