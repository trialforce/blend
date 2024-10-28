<?php

namespace Cache;

/**
 * Cache system using file in storage folder
 */
class Storage implements \Cache\Service
{

    protected \Cache\ConnInfo $info;

    public function __construct(ConnInfo $info)
    {
        $this->info = $info;

        if (!$this->info->getFolder())
        {
            throw new \Exception('É necessário definir uma pasta para user o cache de Storage');
        }

        $folder = $this->getFolder();
        $folder->createFolderIfNeeded();
    }

    public function getFolder()
    {
        return new \Disk\Folder(\Disk\File::getStoragePath() . '/' . $this->info->getFolder());
    }

    public function getFile($key)
    {
        return new \Disk\File(\Disk\File::getStoragePath() . '/' . $this->info->getFolder() . '/' . $key, TRUE);
    }

    public function set($key, $value)
    {
        $file = $this->getFile($key);
        $file->setContent(serialize($value));
        $file->save();
        return true;
    }

    public function get($key)
    {
        $content = $this->getFile($key)->getContent();
        return $content ? unserialize($content) : false;
    }

    public function del($key)
    {
        return $this->getFile($key)->remove();
    }

    public function exists($key)
    {
        return $this->getFile($key)->exists();
    }

    public function allKeys()
    {
        throw new \Exception('Method ALL in \Cache\Storage not implemented!');
    }

}
