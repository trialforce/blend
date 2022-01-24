<?php

namespace Db\Model;

trait History
{

    protected $oldModel;

    public function historyPre()
    {
        $class = static::getName();
        $oldModel = $class::findOneByPk($this->getId());
        $this->oldModel = $oldModel ? $oldModel : new $class();
    }

    public function historyPos()
    {
        $action = $this->oldModel->getId() ? 'Atualizar' : 'Inserir';
        $diff = \Db\Model\History::diffModel($this, $this->oldModel);
        $this->historyReg($this::getName(), $this->getId(), $action, $diff);
    }

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
                    $table = '\Model\\' . $column->getReferenceTable();
                    $repository = $table::repository();
                    $repository instanceof \Db\Repository;

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

    public static function diffModel(\Db\Model $modelA, \Db\Model $modelB)
    {
        $arrayA = $modelA->getArray();
        $arrayB = $modelB->getArray();
        unset($arrayB['oldModel']);
        unset($arrayA['oldModel']);

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

        $historyIgnore = $className::historyIgnore();

        foreach ($historyIgnore as $property)
        {
            if (isset($diff[$property]))
            {
                unset($diff[$property]);
            }
        }

        return $diff;
    }

}
