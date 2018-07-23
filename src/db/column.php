<?php

namespace Db;

/**
 * Informations about one database column
 */
class Column implements \Disk\JsonAvoidPropertySerialize
{

    /**
     * Text
     */
    const TYPE_TEXT = 'text';

    /**
     * Varchar
     */
    const TYPE_VARCHAR = 'varchar';

    /**
     * Char
     */
    const TYPE_CHAR = 'char';

    /**
     * Integer
     */
    const TYPE_INTEGER = 'integer';

    /**
     * Decimal
     */
    const TYPE_DECIMAL = 'decimal';

    /**
     * Bool
     */
    const TYPE_BOOL = 'bool';

    /**
     * Tinyint
     */
    const TYPE_TINYINT = 'tinyint';

    /**
     * Date
     */
    const TYPE_DATE = 'date';

    /**
     * Time
     */
    const TYPE_TIME = 'time';

    /**
     * Timestamp
     */
    const TYPE_TIMESTAMP = 'timestamp';

    /**
     * Datetime
     */
    const TYPE_DATETIME = 'datetime';

    /**
     * Unknow database column tipe
     */
    const TYPE_UNKNOW = 'unknow';

    /**
     * Auto increment
     */
    const EXTRA_AUTO_INCREMENT = 'auto_increment';

    /**
     * Name of the table
     *
     * @var string
     */
    protected $tableName;

    /**
     * Label of the column
     *
     * @var string
     */
    protected $label;

    /**
     * Name of the column
     *
     * @var string
     */
    protected $name;

    /**
     * Type of column
     *
     * @var string
     */
    protected $type;

    /**
     * Max size
     *
     * @var int
     */
    protected $size;

    /**
     * Min size
     *
     * @var int
     */
    protected $minSize = NULL;

    /**
     * Define if can be nullable
     * @var boolean
     */
    protected $nullable;

    /**
     * Define if is primary key
     * @var boolean
     */
    protected $isPrimaryKey;

    /**
     * Default value
     * @var string
     */
    protected $defaultValue;

    /**
     * Any extra information send by database, like AUTO_INCREMENT.
     * @var string
     */
    protected $extra;

    /**
     * Reference table (Foreign key)
     *
     * @var string
     */
    protected $referenceTable;

    /**
     * Reference field (fielf of foreign key)
     *
     * @var string
     */
    protected $referenceField;

    /**
     * Reference description from related table
     *
     * @var string
     */
    protected $referenceDescription;

    /**
     * Define if column has a array of defined values
     *
     * @var array
     */
    protected $constantValues = NULL;

    /**
     * Class that the column will be instancied when will become a view
     * (Case null) default will be choosen by type
     * @var string
     */
    protected $class = NULL;

    /**
     * Define the property that will be used in PHP class.
     *
     * If not informed (NULL) the default is the same name of the column.
     *
     * @var string
     */
    protected $property = NULL;

    /**
     * List of validators
     * @var array of Validator
     */
    protected $validators = NULL;

    /**
     * Construct a column
     *
     * @param string $label
     * @param string $name
     * @param string $type
     * @param int $size
     * @param boolean $nullable
     * @param boolean $isPrimaryKey
     * @param string $defaultValue
     * @param string $extra
     *
     * @return \Db\Column
     */
    public function __construct($label = NULL, $name = NULL, $type = \Db\Column::TYPE_VARCHAR, $size = null, $nullable = false, $isPrimaryKey = false, $defaultValue = NULL, $extra = NULL)
    {
        $this->label = $label;
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->nullable = $nullable;
        $this->isPrimaryKey = $isPrimaryKey;
        $this->defaultValue = $defaultValue;
        $this->extra = $extra;
        /* $this->setLabel( $label );
          $this->setName( $name );
          $this->setType( $type );
          $this->setSize( $size );
          $this->setNullable( $nullable );
          $this->setIsPrimaryKey( $isPrimaryKey );
          $this->setDefaultValue( $defaultValue );
          $this->setExtra( $extra ); */
        //optimize it to not need it
        $this->addValidator(new \Validator\Validator($this));

        return $this;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    public function isNullable()
    {
        return $this->nullable;
    }

    public function getNullable()
    {
        return $this->nullable;
    }

    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
        return $this;
    }

    public function isPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    public function getIsPrimaryKey()
    {
        return $this->isPrimaryKey;
    }

    public function setIsPrimaryKey($isPrimaryKey)
    {
        $this->isPrimaryKey = $isPrimaryKey;
        return $this;
    }

    public function getDefaultValue()
    {
        $defaultValue = $this->defaultValue;

        return $defaultValue;
    }

    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function setMinSize($size)
    {
        $this->minSize = $size;
        return $this;
    }

    /**
     * Limit the min e max length
     *
     * @param int $min
     * @param int $max
     * @return \Db\Column
     */
    public function setSizes($min, $max)
    {
        $this->minSize = $min;
        $this->size = $max;

        return $this;
    }

    public function getMinSize()
    {
        return $this->minSize;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    public function getReferenceTable()
    {
        return $this->referenceTable;
    }

    public function setReferenceTable($table, $field = 'id', $referenceDescription = NULL)
    {
        $this->referenceTable = $table;
        $this->referenceField = $field;
        $this->referenceDescription = $referenceDescription;

        return $this;
    }

    public function getReferenceField()
    {
        return $this->referenceField;
    }

    public function setReferenceField($referenceField)
    {
        $this->referenceField = $referenceField;
        return $this;
    }

    public function getReferenceDescription()
    {
        return $this->referenceDescription;
    }

    public function setReferenceDescription($referenceDescription)
    {
        $this->referenceDescription = $referenceDescription;
        return $this;
    }

    public function getReferenceSql($withAs = TRUE)
    {
        $referenceClass = '\Model\\' . $this->getReferenceTable();
        $catalog = $referenceClass::getCatalogClass();
        $referenceTable = $catalog::parseTableNameForQuery($referenceClass::getTableName());

        $tableName = $catalog::parseTableNameForQuery($this->getTableName());

        $top = '';
        $limit = '';

        if (strtolower($catalog) == '\db\mssqlcatalog')
        {
            $top = 'TOP 1 ';
        }
        else
        {
            $limit = ' LIMIT 1 ';
        }

        $sql = '( SELECT ' . $top . $this->getReferenceDescription() . ' FROM ' . $referenceTable . ' A WHERE A.' . $this->getReferenceField() . '=' . $tableName . '.' . $this->getName() . $limit . ')';

        if ($withAs)
        {
            $sql .= ' AS ' . $this->getName() . 'Description ';
        }

        return $sql;
    }

    public function getReferenceSqlForValue($value)
    {
        $referenceClass = '\Model\\' . $this->getReferenceTable();
        $catalog = $referenceClass::getCatalogClass();
        $referenceTable = \Db\Catalog::parseTableNameForQuery($referenceClass::getTableName());

        $sql = 'SELECT ' . $this->getReferenceDescription() . ' FROM ' . $referenceTable . ' A WHERE A.' . $this->getReferenceField() . '=' . $value . ' LIMIT 1 ';

        return $sql;
    }

    public function getConstantValues()
    {
        return $this->constantValues;
    }

    public function setConstantValues($constantValues)
    {
        $this->constantValues = $constantValues;
        return $this;
    }

    /**
     * Verifies if is a auto increment primary key
     *
     * @return boolean
     */
    public function isAutoPrimaryKey()
    {
        return $this->isPrimaryKey() && $this->getExtra() === \Db\Model::DB_AUTO_INCREMENT;
    }

    /**
     * Return the label of the column
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Define the label of the column
     *
     * @param strng $label
     * @return \Db\Column
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Return the name of the column
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Define the name of the column
     *
     * @param string $name
     * @return \Db\Column
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Return the type of the column
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Define the type of the column
     *
     * @param string $type
     * @return \Db\Column
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Return the column view 'class'
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * The View class that represents the type, it broke the MVC but works really fine
     *
     * @param string $class
     * @return \Db\Column
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * Return the name of the table this column is related
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Define the name of the table of this column
     *
     * @param string $tableName
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;

        return $this;
    }

    /**
     * Return the property, if null return the name
     *
     * @return string
     */
    public function getProperty()
    {
        return $this->property ? $this->property : $this->getName();
    }

    /**
     * Define the propertyu
     *
     * @param string $property
     * @return \Db\Column
     */
    public function setProperty($property)
    {
        $this->property = $property;
        return $this;
    }

    /**
     * Validate passed data
     *
     * @param string $value
     * @return array
     */
    public function validate($value)
    {
        $error = null;

        if (is_array($this->validators))
        {
            foreach ($this->validators as $validator)
            {
                $error = is_array($error) ? $error : array();
                $thisError = $validator->validate($value);
                $thisError = is_array($thisError) ? $thisError : array();
                $error = array_merge($error, $thisError);
            }
        }

        return $error;
    }

    /**
     * Return all column validators
     *
     * @return array
     */
    public function getValidators()
    {
        return $this->validators;
    }

    /**
     * Define a list of validators
     *
     * @param array $validators
     * @return \Db\Column
     */
    public function setValidators($validators)
    {
        if ($validators instanceof \Validator\Validator)
        {
            $validators->setColumn($this);
            $validators = array($validators);
        }

        $this->validators = $validators;

        return $this;
    }

    /**
     * Add an validadtor
     *
     * @param \Validator\Validator $validator
     */
    public function addValidator(\Validator\Validator $validator)
    {
        $this->validators[] = $validator;
        $validator->setColumn($this);
    }

    /**
     * Clear all validator of column
     */
    public function clearValidators()
    {
        unset($this->validators);
    }

    /**
     * Convert the type from sql to php and return it
     *
     * @return string
     */
    public function getPHPType()
    {
        $dbType = $this->type;
        $phpType = 'String';

        if ($dbType == \Db\Column::TYPE_BOOL || $dbType == \Db\Column::TYPE_TINYINT)
        {
            $phpType = 'boolean';
        }
        else if ($dbType == \Db\Column::TYPE_CHAR || $dbType == \Db\Column::TYPE_VARCHAR || $dbType == \Db\Column::TYPE_TEXT)
        {
            $phpType = 'string';
        }
        else if ($dbType == \Db\Column::TYPE_INTEGER)
        {
            $phpType = 'int';
        }
        else
        {
            $phpType = $dbType;
        }

        return $phpType;
    }

    /**
     * Try to convert a column name to a column label.
     *
     * Used when columns don't has a label
     *
     * @return string
     */
    public function mountLabel()
    {
        $name = $this->getName();

        //brazilian portuguese
        if (strtolower($name) == 'id')
        {
            $label = 'Código';
        }
        else if (strtolower($name) == 'descricao')
        {
            $label = 'Descrição';
        }
        else if (strtolower($name) == 'observacao')
        {
            $label = 'Observação';
        }
        else
        {
            $label = ucfirst(str_replace(array(' id'), ' ', str_replace(array('_'), ' ', $name)));
        }

        return trim($label);
    }

    /**
     * Create a rando valid value for this type of column
     *
     * @return mixed
     */
    public function createRandomValue()
    {
        $value = NULL;
        $type = $this->getType();

        if ($this->getIsPrimaryKey() && $this->getExtra() == \Db\Column::EXTRA_AUTO_INCREMENT)
        {
            return NULL;
        }

        //pula valores nulos, as vezes
        if ($this->getNullable() && \Type\Check::rand(80))
        {
            return NULL;
        }

        $constantValues = $this->getConstantValues();

        if ($constantValues)
        {
            $count = count($constantValues);
            $selected = intval(rand(0, $count - 1));
            $values = array_values($constantValues);
            $value = $values[$selected];
        }
        else if ($this->getReferenceTable())
        {
            $referenceTable = $this->getReferenceTable();
            $modelClass = '\Model\\' . $referenceTable;
            $selectList = $modelClass::findForReference();
            $count = count($selectList);
            $selected = intval(rand(0, $count - 1));
            $obj = $selectList[$selected];

            if ($obj instanceof $modelClass)
            {
                $value = $obj->getValue($this->getReferenceField());
            }
            else
            {
                return NULL;
            }
        }
        else if (in_array($type, array(\Db\Column::TYPE_BOOL, \Db\Column::TYPE_TINYINT)))
        {
            $value = \Type\Check::rand();
        }
        else if (in_array($type, array(\Db\Column::TYPE_CHAR, \Db\Column::TYPE_VARCHAR, \Db\Column::TYPE_TEXT)))
        {
            $size = $this->getSize();
            $minSize = $this->getMinSize();
            $value = \Type\Text::rand(intval(rand($minSize, $size)));
        }
        else if (in_array($type, array(\Db\Column::TYPE_DATE, \Db\Column::TYPE_DATETIME, \Db\Column::TYPE_TIMESTAMP)))
        {
            $value = \Type\DateTime::now();
        }
        else if ($type == \Db\Column::TYPE_DECIMAL)
        {
            $value = floatval(rand(0, 1000));
        }
        else if ($type == \Db\Column::TYPE_INTEGER)
        {
            $value = intval(rand(0, 1000) / 100);
        }

        return $value;
    }

    /**
     * Return the sql to use in query
     */
    public function getSql()
    {
        $result = NULL;
        $columnName = $this->getName();

        if ($this->getProperty() != $this->getName())
        {
            $columnName = $columnName . ' AS ' . $this->getProperty();
        }

        $result[] = $columnName;

        if ($this->getReferenceDescription())
        {
            $result[] = $this->getReferenceSql();
        }

        return $result;
    }

    /**
     * List distinct types
     *
     * @return array
     */
    public static function listDistinctTypes()
    {
        $types[self::TYPE_TINYINT] = 'Sim/Não';
        $types[self::TYPE_VARCHAR] = 'Texto';
        $types[self::TYPE_TIME] = 'Hora';
        $types[self::TYPE_DATE] = 'Data';
        $types[self::TYPE_DATETIME] = 'Data e hora';
        $types[self::TYPE_DECIMAL] = 'Decimal';
        $types[self::TYPE_INTEGER] = 'Inteiro';
        $types[self::TYPE_TEXT] = 'Área de texto';

        return $types;
    }

    /**
     * Return the name of the column
     *
     * @return String
     */
    public function __toString()
    {
        return $this->name;
    }

    public function listAvoidPropertySerialize()
    {
        $avoid[] = 'tableName';

        return $avoid;
    }

}
