<?php

namespace Validator;

/**
 * Generic Validator class
 */
class Validator implements \Type\Generic
{

    /**
     * Related \Db\Column\Column
     *
     * @var \Db\Column\Column
     */
    protected $column;

    /**
     * Value to validate
     *
     * @var string
     */
    protected $value;

    /**
     * Validator label
     *
     * @var String
     */
    protected $label;

    public function __construct($value = NULL, $column = NULL)
    {
        $this->setValue($value);
        $this->setColumn($column);
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setColumn($column)
    {
        $this->column = $column;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Define a value to this validator
     *
     * @param string $value
     *
     * @return \Validator\Validator Description
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getLabel()
    {
        if ($this->label)
        {
            return $this->label;
        }
        else if ($this->column)
        {
            return $this->column->getLabel();
        }
        else
        {
            return get_class($this);
        }
    }

    /**
     * Verify if current value is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        $error = $this->validate($this->value);

        if (is_array($error) && count($error) > 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Make the validation of a passed value
     *
     * @return array
     */
    public function validate($value = NULL)
    {
        if ($value || $value == '0' || $value == 0)
        {
            $this->value = $value;
        }

        $error = [];

        if ($this->column)
        {
            if (!$this->validateRequired() && $this->column->getType() != \Db\Column\Column::TYPE_TINYINT)
            {
                $error[] = "Preenchimento obrigatório.";
            }

            if ($this->value)
            {
                $sizeInt = intval($this->column->getSize());

                if ($this->column->getSize() && $this->validateMaxSize())
                {
                    $error[] = "Não pode exceder $sizeInt caracteres.";
                }

                if ($this->column->getMinSize() && $this->validateMinSize())
                {
                    $error[] = "Deve ter $this->column->getMinSize() caracteres no mínimo.";
                }

                if (!$this->validateConstantValues())
                {
                    $error[] = "Valores estão fora do esperado.";
                }

                if (!$this->validateType())
                {
                    $error[] = "Tipo incorreto.";
                }
            }
        }

        return array_filter($error);
    }

    /**
     * Make the validation or throw a Exception
     *
     * @param string $value
     * @return boolean
     *
     * @throws \Exception
     */
    public function validateOrThrow($value = null)
    {
        $value = $value ?: $this->getValue();
        $error = $this->validate($value);

        if (isset($error[0]))
        {
            throw new \UserException($this->getLabel() . ' - ' . $error[0]);
        }

        return true;
    }

    /**
     * Verify the default requited situation
     *
     * @return boolean
     */
    protected function validateRequired()
    {
        if (is_array($this->value))
        {
            $empty = false;
        }
        else
        {
            $value = trim($this->value);
            $empty = $value === '';
        }

        if ($this->column instanceof \Db\Column\Column)
        {
            if ($this->column->isAutoPrimaryKey() || $this->column->getType() == \Db\Column\Column::TYPE_BOOL)
            {
                return true;
            }

            if (!$this->column->isNullable() && $empty)
            {
                return false;
            }
        }
        else //validation without column
        {
            return !$empty;
        }

        return true;
    }

    /**
     * Validate default max sizes situation
     *
     * @return bool
     */
    protected function validateMaxSize()
    {
        if (is_array($this->value))
        {
            return false;
        }

        $collum = $this->getColumn();
        $value = $this->value;

        if ($collum && $collum->getType() == \Db\Column\Column::TYPE_DECIMAL)
        {
            $valueType = new \Type\Decimal($this->value);
            $value = $valueType->getIntPart();
        }

        $sizeInt = intval($this->column->getSize());
        return mb_strlen($value) > $sizeInt;
    }

    /**
     * Validate default min sizes situation
     *
     * @return bool
     */
    protected function validateMinSize()
    {
        if (is_array($this->value))
        {
            return false;
        }

        $sizeInt = intval($this->column->getMinSize());

        return mb_strlen($this->value) < $sizeInt;
    }

    /**
     * Validate constant values
     */
    protected function validateConstantValues()
    {
        if (is_array($this->value))
        {
            return true;
        }

        $values = $this->column->getConstantValues();

        if ($values instanceof \Db\ConstantValues)
        {
            $values = $values->getArray();
        }

        if (is_array($values))
        {
            return in_array($this->value, array_keys($values));
        }

        return TRUE;
    }

    /**
     * Validate type of the value
     */
    protected function validateType()
    {
        $ok = TRUE;
        $type = $this->column->getType();

        if ($type == \Db\Column\Column::TYPE_INTEGER)
        {
            if (!self::isInteger($this->value))
            {
                $ok = FALSE;
            }
        }
        else if ($type == \Db\Column\Column::TYPE_DECIMAL)
        {
            if (!is_numeric(str_replace(',', '', $this->value)))
            {
                $ok = FALSE;
            }
        }

        return $ok;
    }

    /**
     * Default method to remove mask of value.
     * By default is remove all chacaters letting only numbers
     *
     * @param string $value
     * @return string
     */
    public static function unmask($value)
    {
        if (is_array($value))
        {
            return '';
        }

        return preg_replace("/[^0-9]/", "", $value.'');
    }

    /**
     * Verify if is am integer, even if the type is string
     *
     * @param string $var
     * @return boolean
     */
    public static function isInteger($var)
    {
        $int = intval($var);

        if ("$int" == "$var")
        {
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Get some Validator, used to avoid by php limitation "new Class()->function()"
     *
     * @param string $value
     * @param \Db\Column\Column $column
     * @return \Validator\Validator
     */
    public static function get($value = NULL, $column = NULL)
    {
        $class = get_called_class();
        return new $class($value, $column);
    }

    /**
     * Get instance and returns it's default value
     *
     * @param mixed $value
     * @return string the value
     */
    public static function value($value)
    {
        return self::get($value)->getValue();
    }

    /**
     * Return the string representation of the datatype
     *
     * @return string
     */
    public function __toString()
    {
        return $this->value;
    }

    /**
     * Return the value parsed to database
     * @return string
     */
    public function toDb()
    {
        return $this->value;
    }

    /**
     * Return the string representation of this type to human,
     * used in grid, list e etc
     *
     * @return string
     */
    public function toHuman()
    {
        return $this->value;
    }

}
