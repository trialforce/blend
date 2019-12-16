<?php

namespace DataSource;

/**
 * Query datasource
 */
class Vector extends DataSource
{

    /**
     * Data
     *
     * @var array
     */
    protected $data;
    protected $fullData;

    public function __construct($data)
    {
        if ($data instanceof \Db\Collection)
        {
            $data = $data->getData();
        }

        $this->setData($data);
    }

    public function getCount()
    {
        return count($this->fullData);
    }

    public function setData($data)
    {
        $this->data = $data;
        $this->fullData = $data;
    }

    public function getData()
    {
        //make order if needed
        if (!is_null($this->getOrderBy()))
        {
            usort($this->fullData, array($this, "compareToOrder"));
        }

        //make pagination work on array
        if ($this->getLimit() && $this->getOffset() || $this->getOffset() == '0')
        {
            $this->data = array_slice($this->fullData, $this->getOffset(), $this->getLimit());
        }

        return $this->data;
    }

    public function compareToOrder($first, $second)
    {
        $columnName = $this->getOrderBy();
        $orderWay = $this->getOrderWay();
        $firstl = '';
        $secondl = '';

        if (isset($first->$columnName))
        {
            $firstl = strtolower($first->$columnName);

            //obtém só os caracteres numéricos caso seja um valor numérico
            if (\Type\Integer::isNumeric($firstl))
            {
                $firstl = \Type\Integer::onlyNumbers($firstl);
            }
        }

        if (isset($second->$columnName))
        {
            $secondl = strtolower($second->$columnName);

            if (\Type\Integer::isNumeric($secondl))
            {
                $secondl = \Type\Integer::onlyNumbers($secondl);
            }
        }

        if ($firstl == $secondl)
        {
            return 0;
        }
        else if (strtoupper($orderWay) == \Db\Model::ORDER_ASC)
        {
            return ($firstl > $secondl) ? +1 : -1;
        }
        else
        {
            return ($firstl > $secondl) ? -1 : +1;
        }
    }

    public function executeAggregator(Aggregator $aggregator)
    {
        $data = $this->fullData;
        $columnName = $aggregator->getColumnName();
        $money = false;
        $total = 0;

        if (is_array($data))
        {
            //TODO make other aggregation methods
            if ($aggregator->getMethod() == Aggregator::METHOD_SUM)
            {
                foreach ($data as $item)
                {
                    $value = $item->$columnName;

                    if ($value instanceof \Type\Generic)
                    {
                        $value = $value->toDb();
                    }

                    //add suporte for braizilian real
                    if (stripos($value, 'R$') === 0)
                    {
                        $money = true;
                        $value = \Type\Money::get($value)->toDb();
                    }
                    else
                    {
                        $value = \Type\Decimal::get($value)->toDb();
                    }

                    $total += $value;
                }
            }
            else if ($aggregator->getMethod() == Aggregator::METHOD_COUNT)
            {
                $total = count($data);
            }
        }

        if ($money)
        {
            $total = \Type\Money::get($total);
        }
        else
        {
            $total = \Type\Decimal::get($total);
        }

        return $aggregator->getLabelledValue($total);
    }

}
