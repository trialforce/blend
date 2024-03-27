<?php

namespace Disk;

/**
 * Class used to deal with a disk folder
 */
class Folder
{

    protected $path;

    /**
     * Construct the folder defining its path
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->setPath($path);
    }

    /**
     * Return the path of the foler
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Define the path of the folder
     *
     * @param string $path
     * @return \Disk\Folder
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * Verify if is really a dir
     * @return bool
     */
    public function isDir()
    {
        return is_dir($this->path);
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
     * Verify if folder exists
     *
     * @return bool
     */
    public function exists()
    {
        return $this->isDir() || file_exists($this->path);
    }

    public function getBasename()
    {
        return basename($this->path);
    }

    /**
     * Create a folder
     *
     * @param int $mode
     * @return boolean
     */
    public function create($mode = 0777)
    {
        return mkdir($this->path, $mode, TRUE);
    }

    /**
     * Create the folder recursively if needed
     */
    public function createFolderIfNeeded()
    {
        if (!$this->exists())
        {
            $this->create();
        }
        else
        {
            return true;
        }
    }

    /**
     * Return the first file
     *
     * @param type $relativeGlob
     * @return \Disk\File
     */
    public function findOneFile($relativeGlob)
    {
        $files = \Disk\File::find($this->path . '/' . $relativeGlob);

        if (isset($files[0]))
        {
            return $files[0];
        }

        return NULL;
    }

    /**
     * Return the list of files of the folder
     *
     * @return array of \Disk\File
     */
    public function listFiles($search = '*', $recursive = FALSE, $flags = null)
    {
        return \Disk\File::find($this->path . '/' . $search, $flags, $recursive);
    }

    /**
     * Lista images
     *
     * @return array of \Disk\File
     */
    public function listImages()
    {
        return \Disk\File::find($this->path . '/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
    }

    public function remove()
    {
        return rmdir($this->getPath());
    }

    /**
     * Clear the files of folder
     */
    public function clear()
    {
        return \Disk\Folder::clearRecursive($this);
    }

    public static function clearRecursive(\Disk\Folder $folder)
    {
        $files = $folder->listFiles();

        foreach ($files as $file)
        {
            if ($file->isDir())
            {
                $folder = $file->getFolder();
                \Disk\Folder::clearRecursive($folder);
                $folder->remove();
            }
            else
            {
                $file->remove();
            }
        }

        return true;
    }

}
