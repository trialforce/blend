<?php

namespace View\Ext;

class Upload extends \View\Input
{
    /**
     * Construct the file upload
     * 
     * @param string $idName
     * @param string $event
     * @param string $accept
     * @param string $class
     */
    public function __construct($idName = NULL, $event =NULL, $accept = '', $class = NULL)
    {
        parent::__construct($idName, \View\Input::TYPE_FILE , null, $class);
        
        $this->setAccept($accept);
        $this->setEvent($event);
    }
    
    /**
     * Define the accept file types
     *
     * file_extension|audio/*|video/*|image/*|media_type
     * 
     * @param string $accept
     */
    public function setAccept($accept = NULL)
    {
        if( $accept)
        {
            $this->setAttribute( 'accept', $accept );
        }
        else
        {
            $this->removeAttribute('accept');
        }
    }
    
    /**
     * Define the upload event
     * 
     * @param string $event
     */
    public function setEvent($event)
    {
        if ( $event)
        {
            $dom = $this->getDom();
            $pageUrl = $dom->getPageUrl();
        
            $this->change( "fileUpload('{$pageUrl}/{$event}',this)" );
        }
    }
}
