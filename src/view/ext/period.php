<?php

namespace View\Ext;
use DataHandle\Request;

class Period extends \View\Div
{

    private $defaultBegin;
    private $defaultEnd;

    public function __construct($id = NULL, $defaultBegin = NULL, $defaultEnd = NULL, $class = NULL, $father = NULL)
    {
        $this->setDefaultBegin($defaultBegin);
        $this->setDefaultEnd($defaultEnd);

        parent::__construct($id, null, $class, $father);
    }

    public function onCreate()
    {
        $fieldBegin = new \View\Ext\DateInput($this->getIdBegin(), $this->getValueBegin());
        $fieldEnd = new \View\Ext\DateInput($this->getIdEnd(), $this->getValueEnd());

        $fields[] = $fieldBegin;
        $fields[] = $fieldEnd;

        $this->append($fields);

        return $this;
    }

    function getDefaultBegin()
    {
        return $this->defaultBegin;
    }

    function getDefaultEnd()
    {
        return $this->defaultEnd;
    }

    function setDefaultBegin($defaultBegin)
    {
        $this->defaultBegin = $defaultBegin;
    }

    function setDefaultEnd($defaultEnd)
    {
        $this->defaultEnd = $defaultEnd;
    }

    public function getValueBegin()
    {
        $requestValue = Request::get($this->getIdBegin());

        if ($requestValue)
        {
            $value = $requestValue;
        }
        else
        {
            $value = $this->defaultBegin;
        }

        return new \Type\Date($value);
    }

    public function getValueEnd()
    {
        $requestValue = Request::get($this->getIdEnd());

        if ($requestValue)
        {
            $value = $requestValue;
        }
        else
        {
            $value = $this->defaultEnd;
        }

        return new \Type\Date($value);
    }

    public function getIdBegin()
    {
        return $this->getId() . '-begin';
    }

    public function getIdEnd()
    {
        return $this->getId() . '-end';
    }

}