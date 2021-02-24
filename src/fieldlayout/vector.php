<?php

namespace Fieldlayout;

/**
 * Field Generator bases on a pre-defined array layout
 * TODO: Need a beautifull refactor
 */
class Vector
{

    /**
     * If the weight is on field ou container
     * true on field
     * false on container
     * $weight on field
     * @var bool
     */
    protected static $weightOnField = true;

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
     * Default class
     *
     * @var string
     */
    protected static $defaultClass = 'span3';

    /**
     * Define if is to create the question or not
     * @var bool
     */
    protected $createQuestion = true;

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

    public static function getDefaultClass()
    {
        return self::$defaultClass;
    }

    public static function setDefaultClass($defaultClass)
    {
        self::$defaultClass = $defaultClass;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getCreateQuestion()
    {
        return $this->createQuestion;
    }

    public function setCreateQuestion($createQuestion)
    {
        $this->createQuestion = $createQuestion;
        return $this;
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
                if (!$column || $column instanceof \Db\Column\Search)
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
            return $this->createTab($this->array, 'fieldLayoutTab');
        }
        else
        {
            return $this->createFieldLayout($this->getArray());
        }
    }

    public function createTab($tabs, $tabId)
    {
        $tab = new \View\Ext\Tab($tabId);

        foreach ($tabs as $label => $campos)
        {
            $id = \Type\Text::get($label)->toFile();
            $tab->add('tab-' . $id, $label, $this->createFieldLayout($campos));
        }

        return $tab;
    }

    public function createFieldLayout($array)
    {
        $model = $this->getModel();

        if (!$this->model)
        {
            return false;
        }

        $fields = [];
        $tabArray = [];

        //pass trough all line
        foreach ($array as $line => $arrayLine)
        {
            if (is_string($line))
            {
                $tabArray[$line] = $arrayLine;
                continue;
            }

            $arrayPosition = 0;

            //passe trough all fields
            foreach ($arrayLine as $columnName => $weight)
            {
                $column = $model->getColumn($columnName);

                if (!$column)
                {
                    $tabArray[$columnName] = $weight;
                    continue;
                }

                $label = $this->getLabel($column);
                $input = $this->getInputField($column, $weight);
                $div = $this->getContain($input, $label, $weight);

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

                $this->setElementValue($input, $column);

                $arrayPosition++;
            }
        }

        if (count($tabArray) > 0)
        {
            $keys = array_keys($tabArray);

            if (is_string($keys[0]))
            {
                $mainInnerTab = \Type\Text::get($keys[0])->toFile();
                $fields[] = $this->createTab($tabArray, 'inner-tab-' . $mainInnerTab);
            }
        }

        return $fields;
    }

    /**
     * Create the container
     *
     * @param \View\View $input
     * @param \View\Lanel $label
     * @param string $weight
     * @return \View\Div
     */
    public function getContain($input, $label, $weight)
    {
        if ($input instanceof \Component\Component)
        {
            $input = $input->onCreate();
        }

        $input->setLabel($label);

        $div = new \View\Div('contain_' . $input->getId(), array($label, $input), 'field-contain');
        $input->setContain($div);

        //hide weight null
        if (is_null($weight))
        {
            $div->hide();
        }

        return $div;
    }

    /**
     * Return the input for the column
     * TODO optimize
     *
     * @param \Db\Column\Column $column
     * @return \View\View
     */
    public function getInputField(\Db\Column\Column $column, $weight = NULL)
    {
        $dom = \View\View::getDom();
        $type = $column->getType();
        $class = $column->getClass();
        $referenceTable = $column->getReferenceTable();
        $constantValues = $column->getConstantValues();
        $property = $column->getProperty();

        //TODO padronize 100% and put it on \Db\Column\Column or not?
        $classes[\Db\Column\Column::TYPE_INTEGER] = '\View\Ext\IntInput';
        $classes[\Db\Column\Column::TYPE_TIME] = '\View\Ext\TimeInput';
        $classes[\Db\Column\Column::TYPE_TIMESTAMP] = '\View\Ext\DateTimeInput';
        $classes[\Db\Column\Column::TYPE_DATETIME] = '\View\Ext\DateTimeInput';
        $classes[\Db\Column\Column::TYPE_DATE] = '\View\Ext\DateInput';
        $classes[\Db\Column\Column::TYPE_TEXT] = '\View\TextArea';
        $classes[\Db\Column\Column::TYPE_BOOL] = '\View\Ext\CheckboxDb';
        $classes[\Db\Column\Column::TYPE_TINYINT] = '\View\Ext\CheckboxDb';
        $classes[\Db\Column\Column::TYPE_VARCHAR] = '\View\Input';

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
        else if ($type == \Db\Column\Column::TYPE_DECIMAL)
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

    public function getLabel(\Db\Column\Column $column)
    {
        $label = new \View\Label('label_' . $column->getProperty(), $column->getProperty(), $column->getLabel(), 'field-label');

        if ($column->isRequired())
        {
            $label->data('required', '1');
        }

        if ($this->getCreateQuestion() && strlen(trim($column->getDescription())) > 0)
        {
            $pageUrl = \View\View::getDom()->getPageUrl();
            $url = "p('{$pageUrl}/columnQuestion/{$column->getName()}');";
            $icon = new \View\Ext\Icon('question', 'question-' . $column->getName(), $url, 'column-question');
            $label->append($icon);
        }

        return $label;
    }

    /**
     * Trata algumas condições especiais para o campo
     * @param Input $field
     * @param \Db\Column\Column $column
     * @return Input
     */
    public function treatField($field, \Db\Column\Column $column)
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
     * @param \Db\Column\Column $column
     */
    public function setElementValue($input, \Db\Column\Column $column)
    {
        $columnName = $column->getName();
        $value = $this->model->getValue($columnName);

        $dom = \View\View::getDom();

        $defaultValue = $dom && method_exists($dom, 'isInsert') && $dom->isInsert();

        $emptyValue = ($value . '' === '') || ($column->getType() == \Db\Column\Column::TYPE_TINYINT && ($value . '') == 0) || ($column->getType() == \Db\Column\Column::TYPE_INTEGER && ($value . '') == 0);

        //database default value
        if ($defaultValue && !is_array($value) && $emptyValue && $value !== 'default')
        {
            $value = $column->getDefaultValue();
        }

        $input->setValue($value);
    }

}
