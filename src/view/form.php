<?php
namespace View;
/**
 * Default html form
 */
class Form extends \View\View
{
    const ENCTYPE_MULTIPART = 'multipart/form-data';

    public function __construct( $idName = 'form', $fields = \NULL, $action = 'index.php', $method = 'post', $class = \NULL )
    {
        parent::__construct( 'form', $idName, $fields, $class );
        $this->setAttribute( 'action', $action );
        $this->setAttribute( 'method', $method );
    }

    public function setEnctype( $enctype )
    {
        $this->setAttribute( 'enctype', $enctype );
    }

    public function getEnctype()
    {
        return $this->getAttribute( 'enctype' );
    }

}