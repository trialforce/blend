<?php

namespace Cache;

class ConnInfo
{
    const TYPE_MEMORY = 'memory';
    const TYPE_STORAGE = 'storage';
    const TYPE_MEMCACHED = 'memcached';
    const TYPE_DATABASE = 'database';

    protected string $id;
    protected string $type;
    protected string $folder;

    public function __construct($id, $type,$folder)
    {
        $this->setId($id);
        $this->setType($type);
        $this->setFolder($folder);

        \Cache\Cache::addConnInfo($this);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): ConnInfo
    {
        $this->id = $id;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): ConnInfo
    {
        $this->type = $type;
        return $this;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): ConnInfo
    {
        $this->folder = $folder;
        return $this;
    }
}