<?php

namespace Fieldlayout;

/**
 * Field Generator bases on a pre-defined array layout
 * Need a beautifull refactor
 *
 */
class Vector
{

    /**
     *
     * @var \Db\Model
     */
    protected $model;

    /**
     * Array
     * @var array
     */
    protected $array;

    /**
     * If the weight is on field ou container
     * true on field
     * false on container
     * $weight on field
     * @var bool
     */
    protected static $weightOnField = true;

    /**
     * Default class
     *
     * @var string
     */
    protected $defaultClass = 'span3';

    public function __construct($array, $model = null)
    {
        $this->setArray($array);
        $this->setModel($model);
    }

    static function getWeightOnField()
    {
        return self::$weightOnField;
    }

    static function setWeightOnField($weightOnField)
    {
        self::$weightOnField = $weightOnField;
    }

    function getDefaultClass()
    {
        return $this->defaultClass;
    }

    function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function setArray($array)
    {
        $this->array = $array;
    }

    public function getArray()
    {
        $model = $this->getModel();

        if (!$this->array)
        {
            $modelName = $model->getName();
            $columns = $modelName::getColumns();

            foreach ($columns as $column)
            {
                if (!$column || $column instanceof \Db\SearchColumn)
                {
                    continue;
                }

                $this->array[] = array($column->getName() => $this->defaultClass);
            }
        }

        return $this->array;
    }

    public function onCreate()
    {
        if (is_array($this->array))
        {
            $array = array_values($this->array);
            $innerArray = array_values($array[0]);
            $makeTab = is_array($innerArray[0]);
        }
        else
        {
            $makeTab = false;
        }

        if ($makeTab)
        {
            $dom = \View\View::getDom();
            $tabs = $this->array;
            $tab = new \View\Ext\Tab('fieldLayoutTab');
            $formName = '';

            //auto apply formname to id and name
            if (method_exists($dom, 'getFormName') && $dom->getFormName())
            {
                $formName = $dom->getFormName();
            }

            foreach ($tabs as $label => $campos)
            {
                $id = \Type\Text::get($label)->toFile();
                $this->array = $campos;
                $tab->add('tab-' . $formName . $id, $label, $this->createFieldLayout());
            }

            return $tab;
        }
        else
        {
            return $this->createFieldLayout();
        }
    }

    public function createFieldLayout()
    {
        $model = $this->getModel();
        $array = $this->getArray();

        if (!$this->model)
        {
            return false;
        }

        $fields = null;

        //pass trough all line
        foreach ($array as $line => $arrayLine)
        {
            $arrayPosition = 0;

            //passe trough all fields
            foreach ($arrayLine as $columnName => $weight)
            {
                $column = $model->getColumn($columnName);

                if (!$column)
                {
                    continue;
                }

                $label = $this->getLabel($column);
                $original = $this->getInputField($column, $weight);

                $input = $original;

                if ($input instanceof \Component\Component)
                {
                    $input = $original->onCreate();
                }

                $input->setLabel($label);

                $div = new \View\Div('contain_' . $input->getId(), array($label, $input), 'field-contain');
                $input->setContain($div);

                //hide weight null
                if (is_null($weight))
                {
                    $div->hide();
                }

                if (self::$weightOnField)
                {
                    if ($arrayPosition == 0)
                    {
                        $fields[$line] = $div;
                    }
                    else
                    {
                        $fields[$line]->append($div);
                    }
                }
                else
                {
                    $div->addClass($weight . ' clearfix');

                    if ($arrayPosition == 0)
                    {
                        $fields[$line] = new \View\Div('row-' . $input->getId(), $div, 'field-row clearfix');
                    }
                    else
                    {
                        $fields[$line]->append($div);
                    }
                }

                $this->setElementValue($original, $column);

                $arrayPosition++;
            }
        }

        return $fields;
    }

    /**
     * Return the input for the column
     * TODO optimize
     *
     * @param \Db\Column $column
     * @return \View\View
     */
    public function getInputField(\Db\Column $column, $weight = NULL)
    {
        $dom = \View\View::getDom();
        $type = $column->getType();
        $class = $column->getClass();
        $referenceTable = $column->getReferenceTable();
        $constantValues = $column->getConstantValues();
        $property = $column->getProperty();

        //auto apply formname to id and name, to avoid duplicate ids on master form
        if (method_exists($dom, 'getFormName') && $dom->getFormName())
        {
            $property = $dom->getFormName() . '[' . $property . ']';
        }

        //TODO padronize 100% and put it on \Db\Column or not?
        $classes[\Db\Column::TYPE_INTEGER] = '\View\Ext\IntInput';
        $classes[\Db\Column::TYPE_TIME] = '\View\Ext\TimeInput';
        $classes[\Db\Column::TYPE_TIMESTAMP] = '\View\Ext\DateTimeInput';
        $classes[\Db\Column::TYPE_DATETIME] = '\View\Ext\DateTimeInput';
        $classes[\Db\Column::TYPE_DATE] = '\View\Ext\DateInput';
        $classes[\Db\Column::TYPE_TEXT] = '\View\TextArea';
        $classes[\Db\Column::TYPE_BOOL] = '\View\Ext\CheckboxDb';
        $classes[\Db\Column::TYPE_TINYINT] = '\View\Ext\CheckboxDb';
        $classes[\Db\Column::TYPE_VARCHAR] = '\View\Input';

        //class
        if (!is_null($class))
        {
            $field = new $class($property);
        }
        else if ($referenceTable)
        {
            $field = new \View\Ext\ReferenceField($column, $property);
        }
        else if (isIterable($constantValues))
        {
            $field = new \View\Ext\SelectConstantValue($column, $property);
        }
        else if ($type == \Db\Column::TYPE_DECIMAL)
        {
            $field = new \View\Ext\FloatInput($property, NULL, $column->getSize(), NULL);
        }
        else if (isset($classes[$type]))
        {
            $class = $classes[$type];
            $field = new $class($property);
        }
        else //fallback
        {
            $field = new \View\Input($property);
        }

        $original = $field;

        if ($original instanceof \Component\Component)
        {
            $field = $field->onCreate();
        }

        if (self::$weightOnField)
        {
            $field->addClass($weight);
        }

        $this->treatField($field, $column);

        return $original;
    }

    public function getLabel($column)
    {
        $label = new \View\Label('label_' . $column->getProperty(), $column->getProperty(), $column->getLabel(), 'field-label');

        return $label;
    }

    /**
     * Trata algumas condições especiais para o campo
     * @param Input $field
     * @param \Db\Column $column
     * @return Input
     */
    public function treatField($field, \Db\Column $column)
    {
        $field->setAttribute("title", $column->getLabel());
        $field->setAttribute("placeholder", strip_tags($column->getLabel()));

        $size = $column->getSize();

        if ($size)
        {
            $field->setAttribute("maxlength", (int) $size);
        }

        if ($column->isAutoPrimaryKey())
        {
            $field->setAttribute('readonly', 'readonly');
        }

        if (!$column->isNullable() && !$column->isAutoPrimaryKey())
        {
            $field->addClass('required');
        }

        if ($column->isAutoPrimaryKey())
        {
            $field->addClass('pkField');
        }

        $field->addClass('field');

        return $field;
    }

    /**
     * Define o valor de um campo.
     * Considera campos mestre/detalhe.
     *
     * @param \View\View $input
     * @param \Db\Column $column
     */
    public function setElementValue($input, \Db\Column $column)
    {
        $columnName = $column->getName();
        $value = $this->model->getValue($columnName);

        $dom = \View\View::getDom();

        $defaultValue = $dom && method_exists($dom, 'isInsert') && $dom->isInsert();

        $emptyValue = ($value . '' === '') || ($column->getType() == \Db\Column::TYPE_TINYINT && ($value . '') == 0) || ($column->getType() == \Db\Column::TYPE_INTEGER && ($value . '') == 0);

        //database default value
        if ($defaultValue && !is_array($value) && $emptyValue && $value !== 'default')
        {
            $value = $column->getDefaultValue();
        }

        $input->setValue($value);
    }

}
