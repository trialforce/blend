<?php

namespace DataSource;

class Sql extends \DataSource\Vector
{

    protected $file;
    protected $query;
    protected $fileCount;
    protected $params;
    protected $connInfo;

    public function __construct($sqlRelativePath = null, $params = null)
    {
        if ($sqlRelativePath)
        {
            $this->file = new \Disk\File(filePath($sqlRelativePath, 'sql'), TRUE);

            $fileCount = new \Disk\File(filePath($sqlRelativePath, 'count.sql'), TRUE);

            if ($fileCount->exists())
            {
                $this->fileCount = $fileCount;
            }
        }

        $this->params = $params;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    public function setParams($params)
    {
        $this->params = $params;
        return $this;
    }

    public function setQuery($query)
    {
        $this->query = $query;
        return $this;
    }

    public function getQuery()
    {
        if (!$this->query && $this->file)
        {
            $this->query = $this->file->getContent();
        }

        return $this->query;
    }

    function getConnInfo()
    {
        return $this->connInfo;
    }

    function setConnInfo($connInfo)
    {
        $this->connInfo = $connInfo;

        return $this;
    }

    public function executeAggregator(Aggregator $aggregator)
    {

    }

    public function getCount()
    {
        if ($this->fileCount)
        {
            $sql = $this->fileCount->getContent();

            $params = $this->params;
            unset($params['limit']);
            unset($params['offset']);

            $result = \Db\Conn::getInstance()->query($sql, $this->params);

            if (isset($result[0]) && isset($result[0]->count))
            {
                return $result[0]->count;
            }

            return NULL;
        }
        else
        {
            return count($this->getData());
        }
    }

    public function getData()
    {
        if (is_null($this->data) || (isIterable($this->data) && count($this->data) == 0))
        {
            $result = \Db\Conn::getInstance($this->connInfo)->query($this->getQuery(), $this->params);

            if (is_array($result) && isset($result[0]) && !$this->columns)
            {
                $columns = array();

                foreach ($result[0] as $property => $value)
                {
                    //avoid unused variable error in PHPMD
                    $value = null;
                    $columnType = is_numeric($value) ? \Db\Column\Column::TYPE_DECIMAL : \Db\Column\Column::TYPE_VARCHAR;
                    $columns[$property] = new \Component\Grid\Column($property, $property, 'left', $columnType);
                }

                $this->setColumns($columns);
            }

            $this->data = $result;
        }

        return $this->data;
    }

}
