<?php

namespace View;

/**
 * The <object> tag defines an embedded object within an HTML document.
 * Use this element to embed multimedia (like SVG, audio, video, Java applets, ActiveX, PDF, and Flash)
 * in your web pages.
 *
 * You can also use the <object> tag to embed another webpage into your HTML document.
 */
class Object extends \View\View
{

    public function __construct($idName = \NULL, $data = NULL, $type = NULL, $class = NULL, $father = NULL)
    {
        parent::__construct('object', $idName, null, $class, $father);
        $this->attr('data', $data)->attr('type', $type);
    }

}
