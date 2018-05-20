<?php

namespace DataSource;

class Sql extends \DataSource\DataSource
{

    protected $file;
    protected $fileCount;
    protected $params;

    public function __construct($sqlRelativePath, $params)
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
        $result = \Db\Conn::getInstance()->query($this->file->getContent(), $this->params);

        if (is_array($result) && isset($result[0]) && !$this->columns)
        {
            $columns = array();

            foreach ($result[0] as $property => $value)
            {
                //avoid unused variable error in PHPMD
                $value = null;
                $columnType = is_numeric($value) ? \Db\Column::TYPE_DECIMAL : \Db\Column::TYPE_VARCHAR;
                $columns[$property] = new \Component\Grid\Column($property, $property, 'left', $columnType);
            }

            $this->setColumns($columns);
        }

        return $result;
    }

}
