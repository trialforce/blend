<?php

namespace View\Plugin;

/**
 * CkEditor it's a simple view plugin that can be used in blend
 *
 * You can mount your own ckeditor build in https://ckeditor.com/cke4/builder
 *
 * Put the build files in the root of your project /ckeditor
 *
 * Remember to adjust the config.js (inside ckeditor folder)
 * with the defaults CKEDITOR.editorConfig options.
 *
 * And this class will use it.
 */
class CkEditor extends \View\TextArea
{

    public function __construct($idName = NULL, $value = NULL, $class = NULL)
    {
        parent::__construct($idName, $value, $class . ' ckeditor');

        //addScriptOnde add the ckeditor.js only one time, but the callback is called always
        \App::addJs("addScriptOnce('ckeditor/ckeditor.js', function(){createCkEditor({$this->getId()}) } );");
    }

}
