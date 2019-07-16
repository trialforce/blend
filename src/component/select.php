<?php

namespace Component;

abstract class Select extends \Component\Component
{

    protected $selectValue;

    public function __construct($id = null)
    {
        parent::__construct($id);
    }

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $id = $this->getId();

        if (stripos($id, '['))
        {
            $id = str_replace(array('[', ']'), '', $id);
        }

        $class = get_class($this);
        $dataSource = $class::getDataSource();
        $data = $dataSource->getData();
        $select = new \View\Select($id, $data, 'inputValue');

        $this->setContent($select);
        $this->selectValue = $select;

        return $this->selectValue;
    }

    /**
     * Mount the data of the combo
     */
    public abstract function getDataSource();

    public function setValue($value)
    {
        if (!$value)
        {
            return $this;
        }

        $this->selectValue->setValue($value);
    }

    /**
     * Return the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->selectValue->getValue();
    }

}
