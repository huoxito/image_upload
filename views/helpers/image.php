<?php

class ImageHelper extends AppHelper {
    
    var $helpers = array("Html");

    function show($file, $model=null, $options = array()){
        
        if(empty($file)){
            return null;
        }

        if(!$model){
            $model = Inflector::tableize($this->model());
        }

        $path = 'files/'. $model .'/'.$file;
        $url = $this->Html->url('/'.$path);

        $dir = WWW_ROOT . $path;
        if(!is_file($dir)){
            return "File not found";
        }

        $attrs = getimagesize($dir);
        $size = array('width' => $attrs[0], 'height' => $attrs[1]);
        
        $options = array_merge($size, $options);

        return "<img src='$url' alt='user' widht='".$options['width']."' height='".$options['height']."' />"; 
    }

    function thumb($file, $model=null, $number=0, $options = array()){

        if(empty($file)){
            return null;
        }
        if(!$model){
            $model = Inflector::tableize($this->model());
        }
        
        $path = 'files/'. $model .'/thb'.$number.'_'. $file;
        $url = $this->Html->url('/'.$path);

        $dir = WWW_ROOT . $path;
        if(!is_file($dir)){
            return "File not found";
        }

        $attrs = getimagesize($dir);
        $size = array('width' => $attrs[0], 'height' => $attrs[1]);
        
        $options = array_merge($size, $options);

        return "<img src='$url' alt='user' widht='".$options['width']."' height='".$options['height']."' />"; 
    }

}
