<?php

class ImageHelper extends AppHelper {
    
    var $helpers = array("Html");

    function show($file, $model=null){
        
        if(empty($file)){
            return null;
        }

        if(!$model){
            $model = Inflector::tableize($this->model());
        }

        $path = 'files/'. $model .'/'.$file;
        $url = $this->Html->url('/'.$path);

        $dir = WWW_ROOT . $path;
        $attrs = getimagesize($dir);
        $width = $attrs[0];
        $height = $attrs[1];

        echo "<img src='$url' alt='user' widht='$width' height='$height' />"; 
    }

    function thumb($file, $model=null, $number=0){

        if(empty($file)){
            return null;
        }
        if(!$model){
            $model = Inflector::tableize($this->model());
        }
        
        $path = 'files/'. $model .'/thb'.$number.'_'. $file;
        $url = $this->Html->url('/'.$path);

        $dir = WWW_ROOT . $path;
        $attrs = getimagesize($dir);
        $width = $attrs[0];
        $height = $attrs[1];

        echo "<img src='$url' alt='user' widht='$width' height='$height' />"; 
    }

}
