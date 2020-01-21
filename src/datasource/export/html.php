<?php

namespace DataSource\Export;

use DataHandle\Session;

/**
 * Bridge from datasource to html
 */
class Html
{

    public static function getStyle()
    {
        $style = new \View\Style(NULL, '
body {
    font-family: \'Arial\';
    font-size:12px;
}

header {
    clear: both;
}

table {
    border-collapse: collapse;
    width: 100%;
}

tr:nth-child(even) {
  background-color: #ededed;
}

/*make persiste througtn pages*/
thead
{
    display:  table-header-group;
}

tbody {
    display:table-row-group;
}

table td {
    border: solid 1px black;
    padding: 4px 6px;
    font-size: 8px;
}

table th{
    font-weight: bold;
    border: solid 1px black;
    padding: 6px;
    font-size: 10px;
}

.h1 {
    font-size: 14px;
    margin-bottom: 10px;
    width: 100%;
    float: left;
}

.alignRight {
    text-align: right;
}

#logoPath{
    float:left;
    margin-right: 10px;
}

p {
    font-size: 8px;
    margin-top:20px;
}

');

        return $style;
    }

    /**
     * Create a html file based on datasource
     *
     * @param \DataSource\DataSource $dataSource
     * @param string $relativePath
     * @param array $reportColumns
     * @param string $pageSize
     * @return \Disk\File
     */
    public static function create(\DataSource\DataSource $dataSource, $relativePath, $reportColumns = NULL, $pageSize = NULL)
    {
        //pagesize is not used in html format
        $pageSize = null;
        $layout = self::generate($dataSource, $reportColumns);

        $path = str_replace('\\', '_', strtolower($relativePath) . '.html');
        $file = \Disk\File::getFromStorage(Session::get('user') . DS . 'grid_export' . DS . $path);
        //remove file to avoid error
        $file->remove();
        $file->save($layout . '');

        return $file;
    }

    /**
     * Create a html file based on datasource
     *
     * @param \DataSource\DataSource $dataSource
     * @param string $relativePath
     * @param array $reportColumns
     * @return \View\Layout
     */
    public static function generate(\DataSource\DataSource $dataSource, $reportColumns = NULL)
    {
        $filterString = '';
        $dataSource = \DataSource\Export\Csv::filterColumns($dataSource, $reportColumns);
        $columns = $dataSource->getColumns();
        $exportColumns = array();

        $today = \Type\DateTime::now();

        $formatedDate = $today->getDay() . ' de ' . $today->getMonthExt($today->getMonth()) . ' de ' . $today->getYear() . ' às ' . $today->getHour() . ':' . str_pad($today->getMinute(), 2, "0", STR_PAD_LEFT);
        $domOriginal = \View\View::getDom();
        $title = self::getReportTitle($domOriginal);

        $layout = new \View\Layout();
        \View\View::setDom($layout);

        $head = new \View\Head(new \View\Title($title . ' - ' . $formatedDate));

        $heads[] = self::getStyle();
        $heads[] = self::getLogo();
        $heads[] = new \View\Div(NULL, $title, 'h1');
        $heads[] = $filterString;

        $body = new \View\Body(new \View\Header(NULL, $heads));

        new \View\Html(array($head, $body));

        $data = $dataSource->getData();
        $tableContent[] = new \View\THead(NULL, self::generateTh($columns));
        $tableContent[] = new \View\TBody(NULL, self::generateData($dataSource, $domOriginal, $layout));

        $table = new \View\Table('table', $tableContent);
        self::makeAggregation($dataSource, $table);
        $body->append($table);
        $body->append(self::generatefooter($data, $formatedDate));

        return $layout;
    }

    protected function getFilterString(\DataSource\DataSource $dataSource)
    {
        $request = \DataHandle\Request::getInstance();
        $html = '';

        foreach ($request as $var => $filterValues)
        {
            $filterName = str_replace('Value', '', $var);
            $conditionName = $filterName . 'Condition';
            $conditionValues = $request->get($conditionName);

            $columnName = $filterName;
            $column = $dataSource->getColumn($filterName);

            if ($column)
            {
                $columnName = $column->getLabel();
            }

            if ($conditionValues)
            {
                $myValue = '';

                foreach ($filterValues as $idx => $filterValue)
                {
                    $conditionValue = $conditionValues[$idx];
                    $hasValue = $filterValue || $filterValue === '0' || $filterValue === 0;

                    if (!$hasValue)
                    {
                        continue;
                    }

                    $myValue = $conditionValue . $filterValue . ' ';
                }

                if (!$myValue)
                {
                    continue;
                }

                $myValue = '[' . trim($myValue) . ']';
                $html .= '<b>' . $columnName . ':</b> ' . $myValue . ' ';
            }
        }

        if ($html)
        {
            $html = 'Filtros: ' . $html . '<br/><br/>';
        }

        return $html;
    }

    protected static function generateTh($columns)
    {
        $typesRight = self::getTypesRight();
        $th = null;

        //list only export columns
        foreach ($columns as $column)
        {
            $th[] = $myTh = new \View\Th(NULL, $column->getLabel());

            if (in_array($column->getType(), $typesRight))
            {
                $myTh->addClass('alignRight');
            }
        }

        return $th;
    }

    /**
     * Get title for report, base on some \DomDocument
     *
     * @param \DOMDocument $dom
     * @return string
     */
    public static function getReportTitle(\DOMDocument $dom)
    {
        $title = '';

        if ($dom instanceof \Page\Crud)
        {
            $titleExtra = '';
            $savedList = \DataHandle\Request::get('savedList');
            $saveList = new \Filter\SavedList();
            $list = $saveList->getObject();

            if (isset($list->$savedList))
            {
                $titleExtra = ' - ' . $list->$savedList->title;
            }

            $title = 'Listar ' . lcfirst($dom->getModel()->getLabel()) . $titleExtra;
        }

        return $title;
    }

    public static function getTypesRight()
    {
        $typesRight = array();
        $typesRight[] = \Db\Column\Column::TYPE_INTEGER;
        $typesRight[] = \Db\Column\Column::TYPE_DECIMAL;
        $typesRight[] = \Db\Column\Column::TYPE_DATETIME;
        $typesRight[] = \Db\Column\Column::TYPE_DATE;

        return $typesRight;
    }

    protected static function getLogo()
    {
        $logoPath = \DataHandle\Config::get('logoPath');

        if ($logoPath)
        {
            $host = \DataHandle\Server::getInstance()->getHost();
            $logoPath = $host . str_replace($host, '', $logoPath);
            return new \View\Img('logoPath', $logoPath, NULL, '26');
        }

        return null;
    }

    protected static function generateData(\DataSource\DataSource $dataSource, $domOriginal, $layout)
    {
        $typesRight = self::getTypesRight();
        $data = $dataSource->getData();
        $columns = $dataSource->getColumns();
        $beforeGridExportRow = $domOriginal instanceof \Page\BeforeGridExportRow;

        if (is_array($data))
        {
            foreach ($data as $index => $model)
            {
                $td = array();

                if ($beforeGridExportRow)
                {
                    $domOriginal->beforeGridExportRow($model, $index);
                }

                foreach ($columns as $column)
                {
                    $column instanceof \Component\Grid\Column;
                    //restore original to locate things, used in group export
                    \View\View::setDom($domOriginal);
                    $value = \DataSource\Grab::getUserValue($column, $model);
                    \View\View::setDom($layout);
                    $td[] = $myTd = new \View\Td(NULL, $value);

                    if (in_array($column->getType(), $typesRight))
                    {
                        $myTd->addClass('alignRight');
                    }
                }

                $tr[] = new \View\Tr(NULL, $td);
            }
        }

        return $tr;
    }

    protected static function generatefooter($data, $formatedDate)
    {
        $exportExtraInfo = \DataHandle\Config::get('exportExtraInfo');

        if ($exportExtraInfo)
        {
            $exportExtraInfo = ' - ' . $exportExtraInfo;
        }

        $foot = new \View\P('foot', 'Total : ' . count($data) . ' registros - Gerado por ' . Session::get('name') . ' - ' . Session::get('email') . ' em ' . $formatedDate . $exportExtraInfo);

        return $foot;
    }

    /**
     * Make Aggregation
     *
     * @param \DataSource\DataSource $dataSource
     * @param \View\Table  $table
     * @return \View\Table the table with aggregation values
     */
    public static function makeAggregation(\DataSource\DataSource $dataSource, \View\Table $table)
    {
        $aggregators = $dataSource->getAggregator();

        if (!is_array($aggregators) || count($aggregators) == 0)
        {
            return;
        }

        $columns = $dataSource->getColumns();

        foreach ($columns as $column)
        {
            if (!$column->getExport())
            {
                continue;
            }

            $value = '';

            if (isset($aggregators[$column->getName()]) && $aggregators[$column->getName()] instanceof \DataSource\Aggregator)
            {
                $aggr = $aggregators[$column->getName()];

                $value = $dataSource->executeAggregator($aggr);
            }

            $class = '';

            if (in_array($column->getType(), array(\Db\Column\Column::TYPE_INTEGER, \Db\Column\Column::TYPE_DECIMAL, \Db\Column\Column::TYPE_DATETIME, \Db\Column\Column::TYPE_DATE)))
            {
                $class = 'alignRight';
            }

            $td[] = new \View\Td('aggr' . $column->getName(), new \View\Strong(NULL, $value), 'aggr ' . $class);
        }

        $table->append(new \View\Tr('aggreLine', $td, 'aggr'));

        return $table;
    }

}
