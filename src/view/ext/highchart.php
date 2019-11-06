<?php

namespace View\Ext;

class HighChart extends \View\Div
{

    const CHART_TYPE_LINE = 'line';
    const CHART_TYPE_AREA = 'area';
    const CHART_TYPE_BAR = 'bar';
    const CHART_TYPE_COLUMN = 'column';
    const CHART_TYPE_PIE = 'pie';

    /**
     * Chart type
     * @var string
     */
    protected $type = self::CHART_TYPE_LINE;
    protected $pieLabelFormat = '<b>{point.name}</b>: {point.percentage:.2f}%';

    /**
     * DataSource
     *
     * @var \DataSource\DataSource
     */
    protected $ds;

    /**
     * Categories
     * @var array
     */
    protected $categories;

    /**
     * Series data
     * @var array
     */
    protected $seriesData;

    public function __construct($id = \NULL, $ds, $type = self::CHART_TYPE_LINE)
    {
        parent::__construct($id);
        $this->setType($type);

        if ($ds instanceof \DataSource\DataSource)
        {
            $this->setDs($ds);
        }
    }

    function getType()
    {
        return $this->type;
    }

    function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    function getDs()
    {
        return $this->ds;
    }

    function setDs(\DataSource\DataSource $ds)
    {
        $this->ds = $ds;
    }

    function getPieLabelFormat()
    {
        return $this->pieLabelFormat;
    }

    function setPieLabelFormat($pieLabelFormat)
    {
        $this->pieLabelFormat = $pieLabelFormat;

        return $this;
    }

    function getCategories()
    {
        return $this->categories;
    }

    function setCategories($categories)
    {
        $this->categories = $categories;
    }

    function getSeriesData()
    {
        return $this->seriesData;
    }

    function setSeriesData($seriesData)
    {
        $this->seriesData = $seriesData;
    }

    public function mountData()
    {
        //avoid re calculate series data
        if ($this->seriesData)
        {
            return;
        }

        $columns = $this->ds->getColumns();
        $data = $this->ds->getData();

        if (!is_array($data) || is_array($data) && count($data) == 0)
        {
            $this->append('Sem dados para gerar o gráfico!');
            return;
        }

        $groupColumns = NULL;
        $aggColumns = NULL;

        //separate columss in group or agg
        foreach ($columns as $column)
        {
            if ($column->getAgg() == \Db\GroupColumn::METHOD_GROUP)
            {
                $groupColumns[] = $column;
            }
            else
            {
                $aggColumns[] = $column;
            }
        }

        if (!isset($groupColumns[0]))
        {
            throw new \UserException('Impossível encontrar coluna principal!');
        }

        $xAxisColumn = $groupColumns[0];
        $categories = NULL;
        $seriesData = NULL;

        $dom = $this->getDom();

        if ($this->type == self::CHART_TYPE_PIE)
        {
            foreach ($data as $item)
            {
                $value = self::getDataITemForGraph($xAxisColumn, $item);

                $obj = new \stdClass();
                $obj->name = $value;

                $number = self::getDataITemForGraph($aggColumns[0], $item);
                $obj->y = floatval($number);

                $color = null;

                if (method_exists($dom, 'getGraphColor'))
                {
                    $color = $dom->getGraphColor($item);
                }

                if ($color)
                {
                    $obj->color = $color;
                }

                $seriesData[$aggColumns[0]->getLabel()][] = $obj;
            }
        }
        else
        {
            foreach ($data as $item)
            {
                $value = self::getDataITemForGraph($xAxisColumn, $item);
                $categories[] = $value;

                foreach ($aggColumns as $index => $aggColumn)
                {
                    $aggName = $aggColumn->getName();
                    $label = $aggColumns[0]->getLabel();
                    $seriesData[$label][] = floatval($item->$aggName);
                }
            }
        }

        $idx = 0;
        foreach ($seriesData as $name => $linha)
        {
            $series[$idx] = new \stdClass();
            $series[$idx]->name = $name;
            $series[$idx]->data = $linha;
            $idx++;
        }

        $this->setSeriesData($series);
        $this->setCategories($categories);
    }

    /**
     * Create the char
     *
     * @throws \UserException
     */
    public function create()
    {
        $this->mountData();
        $seriesData = $this->getSeriesData();

        $chart = new \stdClass();
        $chart->chart = new \stdClass();
        $chart->chart->type = $this->type;

        $chart->title = new \stdClass();
        $chart->title->text = '';

        $chart->xAxis = new \stdClass();
        $chart->xAxis->categories = $this->getCategories();

        $chart->yAxis = new \stdClass();
        $chart->yAxis->title = new \stdClass();
        $chart->yAxis->title->text = '';

        $chart->yAxis->plotLines[0] = array(
            'value' => 0,
            'width' => 1,
            'color' => '#808080');

        $chart->legend = new \stdClass();
        $chart->legend->layout = 'vertical';
        $chart->legend->align = 'right';
        $chart->legend->verticalAlign = 'middle';
        $chart->legend->borderWidth = 0;

        if ($this->type == 'pie')
        {
            $pie = new \stdClass();
            $pie->allowPointSelect = TRUE;
            $pie->cursor = 'pointer';

            $pieLabel = $this->getPieLabelFormat();

            $dataLabels = new \stdClass();
            $dataLabels->enabled = $pieLabel ? true : false;
            $dataLabels->format = $pieLabel;

            $pie->dataLabels = $dataLabels;

            $chart->plotOptions = new \stdClass();
            $chart->plotOptions->pie = $pie;
        }

        $chart->series = $this->getSeriesData();

        $this->addJs($chart);
    }

    /**
     * Add js to page
     *
     * @param array $chartArray
     */
    public function addJs($chartArray)
    {
        $options = NULL;

        if (defined('JSON_PRETTY_PRINT'))
        {
            $options = JSON_PRETTY_PRINT;
        }

        $js = json_encode($chartArray, $options);

        \App::addJs("$(function() {  $('#{$this->getId()}').highcharts($js); })");
    }

    /**
     * Get data of an item to graph
     *
     * @param \Component\Grid\Column $column
     * @param \Db\Model $item
     * @return string
     */
    public static function getDataITemForGraph($column, $item)
    {
        $columnName = $column->getName();
        $value = \DataSource\Grab::getUserValue($columnName, $item);

        if ($column->getType() == \Db\Column\Column::TYPE_TINYINT)
        {
            $value = $value == 1 ? $column->getLabel() : 'Não ' . lcfirst($column->getLabel());
        }
        else
        {
            $value = $value ? $value : 'Vazio';
        }

        return $value;
    }

    /**
     * List graph types
     *
     * @return array
     */
    public static function listChartTypes()
    {
        $options[self::CHART_TYPE_LINE] = 'Linha';
        $options[self::CHART_TYPE_AREA] = 'Área';
        $options[self::CHART_TYPE_BAR] = 'Barra';
        $options[self::CHART_TYPE_COLUMN] = 'Coluna';
        $options[self::CHART_TYPE_PIE] = 'Pizza';

        return $options;
    }

}