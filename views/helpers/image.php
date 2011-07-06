<?php

class ImageHelper extends AppHelper {
    
    var $helpers = array("Html");

    function show($file, $model=null){

        if(!$model){
            $model = Inflector::tableize($this->model());
        }

        $url = $this->Html->url('/files/'. $model .'/'. $file);

        $dir = WWW_ROOT . 'files/'. $model .'/'. $file;
        $attrs = getimagesize($dir);
        $width = $attrs[0];
        $height = $attrs[1];

        echo "<img src='$url' alt='user' widht='$width' height='$height' />"; 
    }

}
