<?php

namespace Media;

/**
 * Acess GD (PHP image Lib function);
 */
class GD
{

    /**
     * Verify if gd is installed
     *
     * @return boolean
     */
    public static function isInstalled()
    {
        return extension_loaded( 'gd' );
    }

    public static function getInfo()
    {
        return gd_info();
    }

    public static function getVersion()
    {
        return GD_VERSION;
    }

    public static function getReleaseVersion()
    {
        return GD_RELEASE_VERSION;
    }

    public static function getMinorVersion()
    {
        return GD_MINOR_VERSION;
    }

    public static function getMajorVersion()
    {
        return GD_MAJOR_VERSION;
    }

    public static function getExtraVersion()
    {
        return GD_EXTRA_VERSION;
    }

    public static function getBundled()
    {
        return GD_BUNDLED;
    }

    /**
     * Returns the image types supported by the current PHP installation.
     *
     * Returns a bit-field corresponding to the image formats supported
     * by the version of GD linked into PHP. The following bits are returned,
     * IMG_GIF | IMG_JPG | IMG_PNG | IMG_WBMP | IMG_XPM.
     *
     * @return type
     */
    public static function getImageTypes()
    {
        return imagetypes();
    }

}
