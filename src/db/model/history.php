<?php

namespace Db\Model;

/**
 * Trait that allows control of json history of modications of any model
 */
trait History
{

    protected $oldModel;

    /**
     * Called before register history
     * @return void
     */
    public function historyPre()
    {
        $class = static::getName();
        $oldModel = $class::findOneByPk($this->getId());
        $this->oldModel = $oldModel ?: new $class();
    }

    /**
     * Called after register history
     * @return void
     */
    public function historyPos()
    {
        $action = $this->oldModel->getId() ? 'Atualizar' : 'Inserir';
        $diff = self::diffModel($this, $this->oldModel);
        $this->historyReg($this::getName(), $this->getId(), $action, $diff);
    }

    /**
     * Feed the diff object with "description" values
     *
     * @param $diff object|array diff
     * @param $className string the model class name
     * @return \stdClass
     */
    public static function feed($diff, $className)
    {
        $result = new \stdClass();

        foreach ($diff as $property => $value)
        {
            $name = $property;
            $right = $value;
            $column = $className::getColumn($property);

            if ($column instanceof \Db\Column\Column)
            {
                $name = $column->getLabel();

                if ($column->getType() == \Db\Column\Column::TYPE_DATETIME)
                {
                    $right = \Type\DateTime::get($value);
                }
                else if ($column->getType() == \Db\Column\Column::TYPE_DATE)
                {
                    $right = \Type\Date::get($value);
                }

                $cValues = $column->getConstantValuesArray();

                if ($cValues && isset($cValues[$value]))
                {
                    $right = $cValues[$value];
                }

                if ($column->getReferenceTable() && $column->getReferenceDescription())
                {
                    $table = $column->getReferenceTable();
                    $table = \Db\Column\Column::getModelClassForReference($table);
                    /** @var \Db\Repository $repository */
                    $repository = $table::repository();

                    $model = $repository->findOneByPk($value);

                    if (!$model)
                    {
                        continue;
                    }

                    $right = $model->getValue($column->getReferenceDescription());

                    if (!$right)
                    {
                        $right = $model->getOptionLabel();
                    }
                }
            }

            $result->$name = $right;
        }

        return $result;
    }

    /**
     * Compare two models (the same model in diferent moments) and return
     * the difference between
     *
     * @param \Db\Model $modelA
     * @param \Db\Model $modelB
     * @return array
     */
    public static function diffModel(\Db\Model $modelA, \Db\Model $modelB)
    {
        $arrayA = $modelA->getArray();
        $arrayB = $modelB->getArray();
        unset($arrayB['oldModel']);
        unset($arrayA['oldModel']);

        //remove inner arrays because array_diff_assoc do not accepted it
        foreach ($arrayA as $key =>$value)
        {
            if (is_array($value))
            {
                unset($arrayA[$key]);
            }
        }

        //remove inner arrays because array_diff_assoc do not accepted it
        foreach ($arrayB as $key =>$value)
        {
            if (is_array($value))
            {
                unset($arrayB[$key]);
            }
        }

        $diff = array_diff_assoc($arrayA, $arrayB);

        //remove all description fields
        foreach ($diff as $item => $value)
        {
            if (str_ends_with($item, 'Description'))
            {
                if (isset($diff[$item]))
                {
                    unset($diff[$item]);
                }
            }

            // some apis send arrays instead of plain values
            if (is_array($value) || is_object($value))
            {
                $diff[$item] = '';
            }
        }

        $className = $modelA::getName();
        $columns = $className::getColumns();

        foreach ($columns as $column)
        {
            if ($column instanceof \Db\Column\Search)
            {
                $property = $column->getName();

                //if (isset($diff[$property]))
                //{
                unset($diff[$property]);
                //}
            }
        }

        //avoid some property defined in historyIgnore method
        $historyIgnore = $className::historyIgnore();

        if (is_array($historyIgnore))
        {
            foreach ($historyIgnore as $property)
            {
                if (isset($diff[$property]))
                {
                    unset($diff[$property]);
                }
            }
        }

        return $diff;
    }

}
