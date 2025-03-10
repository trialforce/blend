<?php

namespace Disk;

class Media extends \Disk\File
{

    /**
     * Media path
     * @var string
     */
    protected static $mediaPath;

    /**
     * Media url
     *
     * @var string
     */
    protected static $mediaUrl;

    /**
     * Create a media file
     *
     * @param string $path
     * @param boolean $load
     * @throws \Exception
     */
    public function __construct($path, $load = FALSE)
    {
        //verify if already has media path as prefix
        if (stripos($path, self::getMediaPath()) === false)
        {
            $path = self::getMediaPath() . $path;
        }

        //remove media url from file path
        $path = str_replace(self::getMediaUrl(), '', $path);

        parent::__construct($path, $load);
    }

    /**
     * Return the url of media file
     *
     * @param boolean $complete
     * @return string
     * @throws \Exception
     */
    public function getUrl($complete = TRUE)
    {
        $relativePath = str_replace(self::getMediaPath(), '', $this->getPath());

        if ($complete)
        {
            return self::getMediaUrl() . $relativePath;
        }
        else
        {
            return $relativePath;
        }
    }

    /**
     * Return media path
     *
     * @return string
     * @throws \Exception
     */
    public static function getMediaPath()
    {
        $path = self::$mediaPath;

        //default path
        if (!$path)
        {
            $appPath = str_replace(array('/', '\\'), '/', APP_PATH);
            self::setMediaPath($appPath . '/media/');
        }

        return str_replace(array('/', '\\', '//'), '/', self::$mediaPath);
    }

    /**
     * Define media path
     *
     * @param string $mediaPath media pth
     * @param bool $verifyFolderExists if is to verify folder exist, avoi to optimize
     * @throws \Exception
     */
    public static function setMediaPath($mediaPath, bool $verifyFolderExists = true)
    {
        self::$mediaPath = $mediaPath;

        if ($verifyFolderExists)
        {
            self::createMediaFolderIfNeeded();
        }
    }

    /**
     * Return media url
     *
     * @return string
     */
    public static function getMediaUrl()
    {
        $url = self::$mediaUrl;

        //default path
        if (!$url)
        {
            self::setMediaUrl(\DataHandle\Server::getInstance()->getHost() . 'media/');
        }

        return self::$mediaUrl;
    }

    /**
     * Define the media url
     *
     * @param string $mediaUrl
     */
    public static function setMediaUrl($mediaUrl)
    {
        self::$mediaUrl = $mediaUrl;
    }

    /**
     * Create media folder if needed
     *
     * @throws \Exception
     */
    public static function createMediaFolderIfNeeded()
    {
        $storage = new \Disk\Folder(self::getMediaPath());

        if (!$storage->exists())
        {
            $ok = $storage->create();

            if (!$ok)
            {
                throw new \Exception('Missing media folder! '.self::getMediaPath());
            }
        }
    }

    /**
     * Verify if file exists
     *
     * @return boolean
     */
    public function exists()
    {
        //support base64 png urls
        if (stripos($this->path, 'image/png;base64,'))
        {
            return FALSE;
        }

        return is_file($this->path);
    }

    /**
     * Return media folder
     *
     * @return \Disk\Folder
     * @throws \Exception
     */
    public static function getMediaFolder()
    {
        return new Folder(self::getMediaPath());
    }

}
