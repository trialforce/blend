<?php

namespace Media;

class ImageMagick
{

    /**
     * Verify if image magick lib is installed
     */
    public static function isInstalled()
    {
        return extension_loaded( 'imagick' );
    }

}
