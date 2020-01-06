<?php

namespace Validator;

/**
 * Validador genérico
 */
class Validator implements \Disk\JsonAvoidPropertySerialize, \Type\Generic
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
        $this->setColumn($column);
        $this->setValue($value);
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
     * Define o valor
     *
     * @param string $value
     *
     * @return Validator\Validator Description
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
            $label = $this->column->getLabel();
        }
        else
        {
            $label = get_class($this);
        }

        return $label;
    }

    /**
     * Make the validation
     *
     * @return array
     */
    public function validate($value = NULL)
    {
        if ($value || $value == '0' || $value == 0)
        {
            $this->value = $value;
        }

        $error = array();

        if ($this->column)
        {

            if (!$this->validateRequired() && $this->column->getType() != \Db\Column\Column::TYPE_TINYINT)
            {
                $error[] = "Preenchimento obrigatório.";
            }

            if ($this->value)
            {
                $sizeInt = intval($this->column->getSize());

                if ($this->column->getSize() && $this->validateMaxSize($this->value))
                {
                    $error[] = "Não pode exceder {$sizeInt} caracteres.";
                }

                if ($this->column->getMinSize() && $this->validateMinSize($this->value))
                {
                    $error[] = "Deve ter {$this->column->getMinSize()} caracteres no mínimo.";
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
     * @throws Exception
     */
    public function validateOrThrow($value = null)
    {
        $value = $value ? $value : $this->getValue();
        $error = $this->validate($value);

        if (isset($error[0]))
        {
            throw new \UserException($this->getLabel() . ' - ' . $error[0]);
        }

        return true;
    }

    /**
     * Verifica se um campo requerido foi corretamente preenchido
     *
     * @param string $value
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

        if ($this->column)
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
     * Verifica tamanho máximo do campo
     *
     * @return type
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
     * verifica tamanho mínimo do campo
     * @return type
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
     * valida os valores constantes
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
     * Valida os types (int and float)
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
     * Propertys to avoid when serialize
     *
     * @return string
     */
    public function listAvoidPropertySerialize()
    {
        $avoid[] = 'column';
        $avoid[] = 'value';

        return $avoid;
    }

    /**
     * Tira máscara de um campo
     *
     * Somente números
     *
     * @param string $value
     * @return string
     */
    public static function unmask($value)
    {
        return preg_replace("/[^0-9]/", "", $value);
    }

    /**
     * Necessário para descobrir se negativos são inteiro
     *
     * @param type $var
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
     * @param \Db\Column\Column $column
     * @param string $value
     * @return \Validator\Validator
     */
    public static function get($value = NULL, $column = NULL)
    {
        $class = get_called_class();
        return new $class($column, $value);
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
     * @return stirng
     */
    public function toHuman()
    {
        return $this->value;
    }

}
