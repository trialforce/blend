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
}

header {
    clear: both;
}

table {
    border-collapse: collapse;
    width: 100%;
}

/*make persiste througtn pages*/
thead
{
    display:  table-header-group;
}

tbody {
    display:table-row-group;
}

table td{
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
    width: 80%;
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
        $columns = \DataSource\Export\Csv::filterColumns($dataSource->getColumns(), $reportColumns);

        $dataSource->setColumns($columns);
        $dataSource->setLimit(NULL);
        $dataSource->setOffset(NULL);
        $exportColumns = array();

        $today = \Type\DateTime::now();

        $formatedDate = $today->getDay() . ' de ' . $today->getMonthExt($today->getMonth()) . ' de ' . $today->getYear() . ' às ' . $today->getHour() . ':' . str_pad($today->getMinute(), 2, "0", STR_PAD_LEFT);
        $domOriginal = \View\View::getDom();
        $title = self::getReportTitle($domOriginal);

        $layout = new \View\Layout();
        \View\View::setDom($layout);

        $head = new \View\Head(new \View\Title($title . ' - ' . $formatedDate));

        $heads[] = self::getStyle();

        $logoPath = \DataHandle\Config::get('logoPath');

        if ($logoPath)
        {
            $host = \DataHandle\Server::getInstance()->getHost();
            $logoPath = $host . str_replace($host, '', $logoPath);
            $heads[] = new \View\Img('logoPath', $logoPath, NULL, '26');
        }

        $heads[] = new \View\Div(NULL, $title, 'h1');

        $body = new \View\Body(new \View\Header(NULL, $heads));

        new \View\Html(array($head, $body));

        $th = null;
        $tr = null;

        //list only export columns
        foreach ($columns as $column)
        {
            if ($column->getExport())
            {
                $exportColumns[$column->getName()] = $column;
                $th[] = $myTh = new \View\Th(NULL, $column->getLabel());

                if (in_array($column->getType(), array(\Db\Column::TYPE_INTEGER, \Db\Column::TYPE_DECIMAL)))
                {
                    $myTh->addClass('alignRight');
                }
            }
        }

        $tableContent[] = new \View\THead(NULL, $th);

        $data = $dataSource->getData();
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

                foreach ($exportColumns as $column)
                {
                    //restore original to locate things, used in group export
                    \View\View::setDom($domOriginal);
                    $value = \Component\Grid\Column::getColumnValue($column, $model);
                    \View\View::setDom($layout);
                    $td[] = $myTd = new \View\Td(NULL, $value);

                    if (in_array($column->getType(), array(\Db\Column::TYPE_INTEGER, \Db\Column::TYPE_DECIMAL, \Db\Column::TYPE_DATETIME, \Db\Column::TYPE_DATE)))
                    {
                        $myTd->addClass('alignRight');
                    }
                }

                $tr[] = new \View\Tr(NULL, $td);
            }
        }

        $tableContent[] = new \View\TBody(NULL, $tr);

        $table = new \View\Table('table', $tableContent);
        self::makeAggregation($dataSource, $table);
        $body->append($table);

        $exportExtraInfo = \DataHandle\Config::get('exportExtraInfo');

        if ($exportExtraInfo)
        {
            $exportExtraInfo = ' - ' . $exportExtraInfo;
        }

        $foot = new \View\P('foot', 'Total : ' . count($data) . ' registros - Gerado por ' . Session::get('name') . ' - ' . Session::get('email') . ' em ' . $formatedDate . $exportExtraInfo);

        $body->append($foot);

        return $layout;
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

        if (count($aggregators) == 0)
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

            if (in_array($column->getType(), array(\Db\Column::TYPE_INTEGER, \Db\Column::TYPE_DECIMAL, \Db\Column::TYPE_DATETIME, \Db\Column::TYPE_DATE)))
            {
                $class = 'alignRight';
            }

            $td[] = new \View\Td('aggr' . $column->getName(), new \View\Strong(NULL, $value), 'aggr ' . $class);
        }

        $table->append(new \View\Tr('aggreLine', $td, 'aggr'));

        return $table;
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
        //remove file to avoid erro
        $file->remove();
        $file->save($layout . '');

        return $file;
    }

}
