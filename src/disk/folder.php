<?php

namespace Disk;

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
        return $this->isDir();
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
     * Return the first file
     *
     * @param type $relativeGlob
     * @return \Disk\File
     */
    public function findOneFile($relativeGlob)
    {
        $files = \Disk\File::find($this->path . DS . $relativeGlob);

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
    public function listFiles($search = '*', $recursive = FALSE)
    {
        return \Disk\File::find($this->path . DS . $search, NULL, $recursive);
    }

    /**
     * Lista images
     *
     * @return array of \Disk\File
     */
    public function listImages()
    {
        return \Disk\File::find($this->path . DS . '*.{jpg,jpeg,png,gif}', GLOB_BRACE);
    }

    /**
     * Clear the files of folder
     */
    public function clear()
    {
        $files = $this->listFiles();

        foreach ($files as $file)
        {
            $file->remove();
        }
    }

}
