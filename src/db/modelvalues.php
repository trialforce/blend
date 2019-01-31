<?php

namespace Db;

class ModelValues extends \Db\ConstantValues
{

    /**
     * Model
     * @var \Db\Model
     */
    private $model;

    /**
     * Model data
     * @var array
     */
    private $data;

    function getModel()
    {
        return $this->model;
    }

    function setModel($model)
    {
        $this->model = $model;
    }

    public function getArray()
    {
        if ($this->data)
        {
            return $this->data;
        }

        $className = get_class($this->model);
        $result = $className::findForReference();
        $this->data = array();

        if (isIterable($result))
        {
            foreach ($result as $item)
            {
                $item instanceof \Db\Model;
                $this->data[$item->getOptionValue()] = $item->getOptionLabel();
            }
        }

        return $this->data;
    }

    /**
     * Create a \Db\ModelValues
     *
     * @param \Db\Model $model
     * @return \Db\ModelValues
     */
    public static function create($model)
    {
        $values = new \Db\ModelValues();
        $values->setModel($model);

        return $values;
    }

}
