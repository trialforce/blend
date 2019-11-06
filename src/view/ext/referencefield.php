<?php

namespace View\Ext;

class ReferenceField extends \View\Select
{

    public function __construct(\Db\Column\Column $column, $columName, $value = NULL, $class = NULL)
    {
        parent::__construct($columName);

        $modelClass = $column->getReferenceModelClass();
        $selectList = $modelClass::findForReference();

        $this->createOptions($selectList);
        $this->setClass($class);

        if ($value)
        {
            $this->setValue($value);
        }
    }

}
