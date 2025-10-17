<?php

namespace Disk;

/**
 * Represrnts a file in disk
 */
class File implements \JsonSerializable
{

    /**
     * File path
     * @var string
     */
    protected $path;

    /**
     * File content
     * @var string | mixed
     */
    protected $content;

    /**
     * Storage path (cache, optimizes, logs)
     * @var string
     */
    protected static $storagePath;

    /**
     * Storage url
     *
     * @var string
     */
    protected static $storageUrl;

    /**
     * Construc and load the file
     *
     * @param string $path
     * @param boolean $load
     */
    public function __construct($path, $load = FALSE)
    {
        $this->setPath($path);

        if ($load)
        {
            $this->load();
        }
    }

    /**
     * Return the path
     *
     * @return string
     */
    public function getPath()
    {
        $path = str_replace(array('/', '\\'), '/', $this->path);

        //remove parameters after link, put keep :? from grid/colum replaceDataInString
        if (stripos($path, '?') > 0 && stripos($path, ':?') === false)
        {
            $explode = explode('?', $path);
            $path = $explode[0];
        }

        return $path;
    }

    /**
     * Define path
     *
     * @param string $path
     * @return \Disk\File
     */
    public function setPath($path)
    {
        //padronize paths
        if ($path)
        {
            $path = str_replace('//', '/', str_replace(array('/', '\\'), '/', $path));
        }

        $this->path = $path;
        return $this;
    }

    /**
     * Return the content
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Define the content of the file
     *
     * @param string $content
     * @return \Disk\File
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
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
     * Load the content of the file to object
     */
    public function load()
    {
        if ($this->exists())
        {
            $this->content = file_get_contents($this->path);
            return true;
        }

        return false;
    }

    /**
     * Return if the file is loaded or not
     * @return bool
     */
    public function isLoaded()
    {
        return (bool)$this->content;
    }

    /**
     * Save the file content to path
     *
     * @return bool
     */
    public function save($content = NULL)
    {
        if ($content)
        {
            $this->setContent($content);
        }

        $this->createFolderIfNeeded();

        return file_put_contents($this->path, $this->content);
    }

    /**
     * Return the file size
     * @return int|false
     */
    public function getSize()
    {
        return filesize($this->path);
    }

    /**
     * Return size formated (MB, KB)
     *
     * @return string
     */
    public function getFormatedSize()
    {
        return self::formatBytes($this->getSize());
    }

    /**
     * Append a content to the file.
     *
     * Do not open the entire file
     *
     * @return bool
     */
    public function append($content)
    {
        if ($content)
        {
            $this->setContent($content);
        }

        $this->createFolderIfNeeded();

        return @file_put_contents($this->path, $this->content, FILE_APPEND);
    }

    /**
     * Create the folder recursive if needed
     */
    public function createFolderIfNeeded()
    {
        $folder = $this->getFolder();

        if (!$folder->exists())
        {
            $folder->create();
        }

        return $this;
    }

    /**
     * Remove the file from disk
     */
    public function remove()
    {
        if ($this->exists())
        {
            return @unlink($this->path);
        }

        return false;
    }

    /**
     * Verify if file is a really a file
     *
     * @return bool
     */
    public function isFile()
    {
        return is_file($this->path);
    }

    /**
     * Verify if path is a directory
     *
     * @return boolean
     */
    public function isDir()
    {
        return is_dir($this->path);
    }

    /**
     * Verify if file is a simbolic link
     *
     * @return bool
     */
    public function isLink()
    {
        return is_link($this->path);
    }

    /**
     * Verify if the file is writable
     *
     * @return boolean
     */
    public function isWritable()
    {
        return is_writeable($this->path);
    }

    /**
     * Return file extension
     *
     * @return string
     */
    public function getExtension()
    {
        $explode = explode('.', $this->path);
        return strtolower($explode[count($explode) - 1]);
    }

    /**
     * Return the mime type of file
     *
     * @return string
     */
    public function getMimeType()
    {
        $mimeTypes = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'webp' => 'image/webp',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = $this->getExtension();

        if (array_key_exists($ext, $mimeTypes))
        {
            return $mimeTypes[$ext];
        }

        return 'application/octet-stream';
    }

    /**
     * Define the file extension
     *
     * @param string $extension
     * @return \Disk\File
     */
    public function setExtension($extension)
    {
        $explode = explode('.', $this->path);
        $explode[count($explode) - 1] = $extension;
        $this->setPath(implode('.', $explode));

        return $this;
    }

    /**
     * Return the file basename
     *
     * @param bool $withExtension with extension
     * @return string
     */
    public function getBasename($withExtension = TRUE)
    {
        $suffix = $withExtension ? '' : '.' . $this->getExtension();
        return basename($this->path, $suffix);
    }

    /**
     * Return the name of directory of the file
     *
     * @return string
     */
    public function getDirname()
    {
        return dirname($this->path);
    }

    /**
     * Return modification time
     *
     * @return int|false modification time
     */
    public function getMTime()
    {
        return filemtime($this->path);
    }

    /**
     * Returne the folder of the file.
     *
     * @return \Disk\Folder
     */
    public function getFolder()
    {
        if ($this->isDir())
        {
            return new \Disk\Folder($this->getPath());
        }

        return new \Disk\Folder($this->getDirname());
    }

    /**
     * Return the path
     *
     * @return string
     */
    public function __toString()
    {
        return $this->path . '';
    }

    /**
     * Static construtor
     *
     * @param string $path
     * @param boolean $load
     * @return \Disk\File
     */
    public static function get($path, $load = FALSE)
    {
        return new \Disk\File($path, $load);
    }

    /**
     * Make a find in files
     *
     * @param string $glob
     * @return \Disk\File[]
     */
    public static function find($glob, $flags = 0, $recursive = FALSE)
    {
        //auto brace
        $isBrace = stripos($glob, '{') && stripos($glob, ',') && stripos($glob, '}');

        if ($isBrace)
        {
            $flags = $flags | GLOB_BRACE;
        }

        $flags = $flags == null ? 0 : $flags;

        if ($recursive)
        {
            $globs = globRecursive($glob, $flags);
        }
        else
        {
            $globs = glob($glob, $flags);
        }

        $files = array();

        if (is_array($globs))
        {
            foreach ($globs as $line => $file)
            {
                $files[$line] = new \Disk\File($file);
            }
        }

        return $files;
    }

    /**
     * Return the url for the file
     * @todo need a full refactor, its a crap legacy code
     *
     * @return string
     */
    public function getUrl($type = 1)
    {
        if ($type == 2)
        {
            return \DataHandle\Server::getInstance()->getHost() . str_replace(APP_PATH, '', $this->getPath());
        }

        //FIXME GAMBIARRA!
        $media = stripos($this->getPath(), 'media/');
        $small = stripos($this->getPath(), 'small/');
        $thumb = stripos($this->getPath(), 'thumb/');

        $isMedia = $media === 0 || $media > 0 || $small === 0 || $small > 0 || $thumb === 0 || $thumb > 0;

        $storageUrl = self::getStorageUrl();
        $storagePath = self::getStoragePath();

        if ($storageUrl && $storagePath)
        {
            if ($isMedia)
            {
                $filePath = str_replace('/', "/", str_replace(APP_PATH, '', $this->getPath()));

                if ($type == 1)
                {
                    $filePath = \DataHandle\Server::getInstance()->getHost() . $filePath;
                }
            }
            else
            {
                $filePath = str_replace('/', "/", str_replace($storagePath, '', $this->getPath()));

                if ($type == 1)
                {
                    $filePath = $storageUrl . $filePath;
                }
            }
        }
        else
        {
            //default case
            $filePath = str_replace('/', "/", str_replace(APP_PATH, '', $this->getPath()));
        }

        return $filePath;
    }

    /**
     * move file to another place.
     *
     * @see rename http://php.net/rename
     * @param \Disk\File $file
     * @param bool $createIfNotExists
     * @return bool
     *
     * @throws \Exception
     */
    public function move(\Disk\File $file, $createIfNotExists = FALSE)
    {
        if (!$this->exists())
        {
            throw new \Exception('Arquivo não encontrado ao mover: ' . $this->getPath());
        }

        if ($createIfNotExists)
        {
            $file->createFolderIfNeeded();
        }

        $ok = rename($this->getPath(), $file);

        if ($ok)
        {
            //update internal path/url control
            $this->path = $file->getPath();
        }

        return $ok;
    }

    /**
     * Copy file to another place, on success returns true.
     *
     * @see  http://php.net/copy
     * @param \Disk\File $newFile
     * @return boolean
     */
    public function copy(\Disk\File $newFile)
    {
        return copy($this->getPath(), $newFile->getPath());
    }

    /**
     * Return a friend name, commonly used for alt ou title attributes
     *
     * @return string
     */
    public function getFriendName()
    {
        $basename = explode('_', $this->getBasename(FALSE));
        unset($basename[count($basename) - 1]);

        return ucfirst(str_replace('-', ' ', implode(' ', $basename)));
    }

    /**
     * Verify if the file is a image (by extension)
     *
     * @return boolean
     */
    public function isImage()
    {
        $exts[] = 'jpeg';
        $exts[] = 'jpg';
        $exts[] = 'gif';
        $exts[] = 'png';
        $exts[] = 'webp';

        return in_array($this->getExtension(), $exts);
    }

    /**
     * Verify if file is a text file (by extension)
     * @return bool
     */
    public function isText()
    {
        $exts[] = 'txt';
        $exts[] = 'php';
        $exts[] = 'js';
        $exts[] = 'html';
        $exts[] = 'css';
        $exts[] = 'json';
        $exts[] = 'xml';

        return in_array($this->getExtension(), $exts);
    }

    /**
     * Make output to browser
     *
     * @return \Disk\File
     */
    public function outputToBrowser($skipCache = TRUE)
    {
        $ext = $this->getExtension();
        $fileUrl = $this->getUrl();

        //skip cache
        if ($skipCache && $this->exists())
        {
            //if has ? don't put it again
            if (stripos($fileUrl, '?') !== FALSE)
            {
                $fileUrl .= '&_=' . $this->getMTime();
            }
            else
            {
                $fileUrl .= '?_=' . $this->getMTime();
            }
        }

        $windowOpen[] = 'html';
        $windowOpen[] = 'php';
        $windowOpen[] = 'xml';
        $windowOpen[] = 'pdf';
        $windowOpen[] = 'json';

        if (in_array($ext, $windowOpen))
        {
            \App::windowOpen($fileUrl);
        }
        else
        {
            \App::addJs("location.href='$fileUrl';");
        }

        return $this;
    }

    /**
     * Inline output
     *
     * http://www.iana.org/assignments/cont-disp/cont-disp.xhtml
     * http://greenbytes.de/tech/tc2231/#inlwithasciifilenamepdf
     *
     * @param string $disposition
     * @return $this
     * @throws \Exception
     */
    public function outputInline($disposition = 'inline', $request = NULL)
    {
        if ($this->isImage() && $request instanceof \DataHandle\Request)
        {
            $image = new \Media\Image($this->path);
            $image->outputInline($disposition, $request);
            return $this;
        }

        header('Content-Description: File Transfer');
        header("Content-Type: " . $this->getMimeType());
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Length:' . $this->getSize());
        header('Content-Disposition: ' . $disposition . '; filename="' . $this->getBasename(TRUE) . '"');
        header("Content-Transfer-Encoding: binary\n");
        header("Last-Modified: " . $this->getMTime());
        header('Connection: close');

        $this->outputBrowser();
        return $this;
    }

    protected function outputBrowser()
    {
        readfile($this->getPath());
    }

    /**
     * Format bytes of a number
     *
     * @param int $size
     * @param int $precision
     * @return string
     */
    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');

        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    /**
     * Return a file from storafe path
     *
     * @param string $path
     * @param string $load
     * @return \Disk\File
     */
    public static function getFromStorage($path, $load = FALSE)
    {
        return new \Disk\File(self::getStoragePath() . $path, $load);
    }

    /**
     * Create storage path if needed
     *
     * @throws \Exception
     */
    public static function createStorageFolderIfNeeded()
    {
        //try to find the directory
        $storage = new \Disk\Folder(self::$storagePath);

        if (!$storage->exists())
        {
            $ok = $storage->create();

            if (!$ok)
            {
                throw new \Exception('Missing storage folder!');
            }
        }
    }

    /**
     * Define storage path
     *
     * @param string $storagePath
     *
     */
    public static function setStoragePath($storagePath)
    {
        self::$storagePath = $storagePath;
        //self::createStorageFolderIfNeeded();
    }

    /**
     * Return storage path
     *
     * @return string
     */
    public static function getStoragePath()
    {
        $storagePath = self::$storagePath;

        //default path
        if (!$storagePath)
        {
            $server = \DataHandle\Server::getInstance();
            self::setStoragePath(APP_PATH . '/storage/');
            self::setStorageUrl($server->getHost() . '/storage/');
        }

        return self::$storagePath;
    }

    /**
     * Return storage url
     *
     * @return string
     */
    public static function getStorageUrl()
    {
        if (!self::$storageUrl)
        {
            $server = \DataHandle\Server::getInstance();
            self::$storageUrl = $server->getHost() . '/storage/';
        }

        return self::$storageUrl;
    }

    /**
     * Define storage url
     *
     * @param string $storageUrl
     *
     */
    public static function setStorageUrl($storageUrl)
    {
        self::$storageUrl = $storageUrl;
    }

    /**
     * Return the base64 string representating the currente image/file
     * @return string
     */
    public function getBase64()
    {
        $byteArray = file_get_contents($this->getPath());
        $encode = base64_encode($byteArray);
        return $encode;
    }

    /**
     * Json serializado
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        $result = new \stdClass();
        $result->path = $this->getPath();
        $result->url = $this->getUrl();

        return $result;
    }

}
