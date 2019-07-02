<?php

namespace View\Plugin;

/**
 * CkEditor it's a simple view plugin that can be used in blend
 *
 * You can mount your own ckeditor build in https://ckeditor.com/cke4/builder
 *
 * Put the build files in the root of your project /ckeditor
 * 
 * And this class will use it.
 */
class CkEditor extends \View\TextArea
{

    public function __construct($idName = NULL, $value = NULL, $class = NULL)
    {
        parent::__construct($idName, $value, $class . ' ckeditor');

        \App::addJs("addScriptOnce('ckeditor/ckeditor.js',function(){ CKEDITOR.replace( '{$this->getId()}' )} );");
    }

}
