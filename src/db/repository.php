<?php

namespace Db;

/**
 * Repository of \Db\Model, used for live cache of \Db\Model objects
 */
class Repository extends \Db\Collection
{

    /**
     * Repository cache
     * @var array
     */
    protected static $repositoryCache;

    /**
     * model name
     * @var string
     */
    protected $modelName;

    /**
     * The data of collection
     * @var string
     */
    protected $fillData = array();

    /**
     *
     * @param string $modelName
     * @param bool $fill
     * @return \Db\Repository
     */
    public static function getInstance($modelName, $fill = false)
    {
        if (!isset(self::$repositoryCache[$modelName]))
        {
            self::$repositoryCache[$modelName] = new \Db\Repository($modelName, $fill);
        }

        return self::$repositoryCache[$modelName];
    }

    public function __construct($modelName = null, $fill = false)
    {
        $this->setModelName($modelName);

        if ($fill)
        {
            $this->fill();
        }
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function fill()
    {
        $modelName = $this->modelName;
        $result = $modelName::query()->toCollection()->toArray();
        $this->fillData = $result;
        $this->resetCollection();

        return $this;
    }

    public function findOneByPk($pk)
    {
        if (!$this->data[$pk])
        {
            $modelName = $this->modelName;
            $model = $modelName::findOneByPk($pk);
            $this->data[$pk] = $model;
        }

        return $this->data[$pk];
    }

    protected function resetCollection()
    {
        $this->clear();
        $this->add($this->fillData);
        $this->indexByProperty('id');

        return $this;
    }

    public function toCollection()
    {
        $array = $this->getData();
        $this->resetCollection();

        return new \Db\Collection($array);
    }

    public function first()
    {
        $collection = $this->toCollection();

        return $collection->first();
    }

    public function firstOrCreate()
    {
        $first = $this->first();

        if (!$first)
        {
            $className = $this->getModelName();
            $first = new $className();
        }

        return $first;
    }

    public function toCollectionStdClass()
    {
        return $this->toCollection();
    }

    /**
     * Return data as an array of array
     * @return array array of array
     */
    public function toArray()
    {
        $data = $this->toCollection()->getData();

        $result = [];

        foreach ($data as $item)
        {
            $result[] = $item->toArray();
        }

        return $result;
    }

    /**
     * Return data as array of stdClass
     *
     * @return array array of stdClass
     */
    public function toArrayStdClass()
    {
        return $this->toCollectionStdClass()->getData();
    }

}
