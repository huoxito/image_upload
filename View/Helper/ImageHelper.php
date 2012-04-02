<?php

class ImageHelper extends AppHelper {
    
    public $helpers = array("Html");

    public function show($file, $model=null, $options = array()){
        
        if(empty($file)){
            return null;
        }

        if(!$model){
            $model = Inflector::tableize($this->model());
        }

        $path = 'files/' . $model . '/' . $file;
        $url = $this->Html->url('/' . $path);

        $dir = WWW_ROOT . $path;
        if(!is_file($dir)){
            return "File not found";
        }

        $options = $this->mergeOptions($dir, $options);
        return $this->Html->image($url, $options);
    }

    public function thumb($file, $model=null, $number=0, $options = array()){

        if(empty($file)){
            return null;
        }
        if(!$model){
            $model = Inflector::tableize($this->model());
        }
        
        $path = 'files/' . $model . '/thb' . $number . '_' . $file;
        $url = $this->Html->url('/' . $path);

        $dir = WWW_ROOT . $path;
        if(!is_file($dir)){
            return "File not found";
        }

        $options = $this->mergeOptions($dir, $options);

        return $this->Html->image($url, $options);
    }

    private function mergeOptions($image, $options){

        $attrs = getimagesize($image);
        $size = array('width' => $attrs[0], 'height' => $attrs[1]);

        return $options = array_merge($size, $options);
    }

}
