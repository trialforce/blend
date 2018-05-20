<?php

namespace View\Ext;

/**
 * @deprecated since version number
 */
class FileUpload extends \View\Div
{

    public $imgResult;

    public function __construct( $id, $href, $phpFunction = 'fileUpload', $accept = 'image/*', $class = NULL )
    {
        parent::__construct( $id, $class );
        $this->addClass( 'fileUpload' );

        $upload = new \View\Input( 'label' . $id, \View\Input::TYPE_FILE );
        $upload->change( "fileUpload('{$phpFunction}',this)" );

        if ( $accept )
        {
            $upload->setAttribute( 'accept', $accept );
        }

        $upload->hide();
        $this->appendChild( $upload );

        $img = self::getImg( $href );

        $this->imgResult = new \View\Div( 'imgResult' . $id, $img );
        $labelImg = new \View\Label( 'label_' . $id, null, $this->imgResult );
        //faz funcionar o onclick no firefox e outros navegadores
        $labelImg->click( "$(this).parent().find('input').click()" );

        $this->appendChild( $labelImg );
    }

    public static function getImg( $href )
    {
        $img = new \View\Img( 'empLogo', $href );
        $img->setClass( 'img-polaroid' );
        $img->addStyle( 'cursor', 'pointer' );
        $img->addStyle( 'width', '200px' ); //FIXME HARDCODED

        return $img;
    }

}
