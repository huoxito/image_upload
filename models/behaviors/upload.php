<?php 
/*
 *  
 *  Example of a config array used on Model
 *  field_name should contain an array of the img width and 
 *  height. Thumbs width and height are optional, if none is given
 *  no thumb will be generated.
 *
 *   var $actsAs = array(
 *       'ImageUpload.Upload' => array(
 *            'field_name' => array(
 *               'w','h','Tw','Th'
 *            )
 *       )
 *   );
 *
 *
 */

class UploadBehavior extends ModelBehavior {

/*
 * Default image dimensions 
 */
    var $param = 300;
/*
 * The dir name where pics get uploaded 
 */
    var $dir = null;
     
    function setup(&$model, array $settings){
        
        $this->dir = Inflector::tableize($model->name);
        $this->fields_array = $settings; 

        $this->settings[$model->name] = array_merge(
            array('path_to_dir' => WWW_ROOT . 'files/' . $this->dir),
            $this->fields_array
        );
    }
    
    function beforeValidate(&$model){
        foreach($this->fields_array as $field => $configs){
            if(empty($model->data[$model->alias][$field]['name'])){
                $model->data[$model->alias][$field] = null;
            }
        }
        return true;
    }
     
    function beforeSave(&$model){

        App::Import('Lib', 'ImageUpload.Upload');
        foreach($this->fields_array as $field => $configs){

            if(!empty($model->data[$model->alias][$field]['name'])){
                
                $handle = new upload($model->data[$model->alias][$field]);
                
                $filename = $this->nome($model);
                $handle->file_new_name_body = $filename; 
                $handle->image_resize = true;
                $handle->image_ratio_crop = true;

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
                    $this->delete($model);
                }

                $params_number = count($this->settings[$model->alias][$field]);
                $thumbs = (int)($params_number - 2) / 2;
                $n = 0;
                for($i=0; $i<$thumbs; $i++){
                    
                    $filename_thumb = 'thb'.$i.'_'.$filename;
                    $handle->file_new_name_body = $filename_thumb; 
                    $handle->image_resize = true;
                    $handle->image_ratio_crop = true;

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
    

    function beforeDelete(&$model){
        return $this->delete($model);
    }

    function nome(&$model, $thumb = false){
        $nome = time();
        return $nome; 
    }

    function delete(&$model){
        
        extract($this->settings[$model->alias]);
        foreach($this->fields_array as $field => $configs){
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
