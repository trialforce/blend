<?php

namespace Db;

/**
 * Uma condição para uma query
 */
class Cond
{

    /**
     * Operador adicional
     */
    const COND_AND = 'AND';

    /**
     * Operador condicional
     */
    const COND_OR = 'OR';

    /**
     * Operador de negação
     */
    const COND_NOT = 'NOT';

    /**
     * Uma condição normal
     */
    const TYPE_NORMAL = '';

    /**
     * Define que a condição deve ser incluida na parte Having da query
     */
    const TYPE_HAVING = 'having';

    /**
     * O filtro
     *
     * @var string
     */
    protected $filter;

    /**
     * O valor
     * @var string
     */
    protected $value;

    /**
     * A condiçao em si
     *
     * @var string
     */
    protected $condition;

    /**
     * Tipo de condição normal, ou having
     */
    protected $type;

    /**
     * Cria uma condição para o banco.
     *
     * A quantidade de ? deve ser igual a quantidade de argumentos.
     *
     * Então é possível fazer filtros de duas formas;
     * 1)
     *
     *  $filters[ ] = new \Db\Cond( 'usuario = ?', $usuario );
     *  $filters[ ] = new \Db\Cond( 'senha = ?', $senha , \Db\Cond::COND_AND );
     * 2)
     *
     *  $filters[ ] = new \Db\Cond( '(usuario = ? AND Senha)', array($usuario, $senha));
     *
     * @param string $filter
     * @param mixed $value pode ser simples ou array
     * @param string $condition condição
     * @param string $type tipo, normal ou having
     */
    public function __construct($filter = NULL, $value = NULL, $condition = self::COND_AND, $type = self::TYPE_NORMAL)
    {
        $this->setCondition($condition);
        $this->filter = $filter;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Retorna a condição (And, or , not)
     * @return string
     */
    public function getCondition()
    {
        return $this->condition;
    }

    /**
     * Define a condição (and, or, not).
     *
     * Use as constantes.
     *
     * @param string $condition
     * @return \Db\Cond
     */
    public function setCondition($condition)
    {
        //condição padrão
        if (!$condition)
        {
            $condition = self::COND_AND;
        }

        $this->condition = $condition;

        return $this;
    }

    /**
     * Retorna a string de filtro
     *
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Define o filtro
     *
     * @param string $filter
     * @return \Db\Cond
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Define o valor
     *
     * @return string
     */
    public function getValue()
    {
        if (is_null($this->value))
        {
            return NULL;
        }

        if (!is_array($this->value))
        {
            $value = $this->value;

            if ($value instanceof \Type\Generic)
            {
                $value = $value->toDb();
            }

            return array($value);
        }

        return $this->value;
    }

    /**
     * Define o valor
     *
     * @param string $value
     * @return \Db\Cond
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Retorna o tipo da condição, normal ou having
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Define o tipo da condição, normal ou having
     * @param string $type
     *
     * @return \Db\Cond
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Retorna a condição em sql
     *
     * @param string $first define se é o primeiro ou não (deve incluir a condição na frente)
     * @return string
     */
    public function getWhere($first = false)
    {
        $where = $this->filter . ' ';

        if ($first)
        {
            return $where;
        }
        else
        {
            return $this->condition . ' ' . $where;
        }
    }

    /**
     * Retorno padrão da condição como string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getWhere(false);
    }

}