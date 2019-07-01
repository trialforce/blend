<?php

namespace DataSource\Export;

use DataHandle\Session;

/**
 * Bridge from datasource to csv. Support MS Office default csv style.
 */
class Csv
{

    /**
     * Create a csv file based on datasource
     *
     * @param \DataSource\DataSource $dataSource
     * @param string $relativePath
     * @param array $reportColumns
     * @param string $pageSize
     * @return \Disk\File
     */
    public static function create(\DataSource\DataSource $dataSource, $relativePath, $reportColumns = NULL, $pageSize = NULL)
    {
        //page size is not used in CSV export
        $pageSize = null;
        $columns = self::filterColumns($dataSource->getColumns(), $reportColumns);

        $dataSource->setColumns($columns);
        $dataSource->setLimit(NULL);
        $dataSource->setOffset(NULL);
        $exportColumns = array();

        //list only exportation columns
        foreach ($columns as $column)
        {
            if ($column->getExport())
            {
                $exportColumns[$column->getName()] = $column;
                $labels[$column->getName()] = $column->getLabel();
            }
        }

        $data = $dataSource->getData();
        $csv = implode(';', $labels) . PHP_EOL;

        $beforeGridExportRow = \View\View::getDom() instanceof \Page\BeforeGridExportRow;

        if (isIterable($data))
        {
            foreach ($data as $index => $model)
            {
                $columsLine = array();

                if ($beforeGridExportRow)
                {
                    \View\View::getDom()->beforeGridExportRow($model, $index);
                }

                foreach ($exportColumns as $column)
                {
                    $value = strip_tags(\Component\Grid\Column::getColumnValue($column, $model));
                    $columsLine[] = '"' . $value . '"';
                }

                $csv .= implode(';', $columsLine) . PHP_EOL;
            }
        }

        $csv .= self::makeAggregation($dataSource);

        //store file on disk
        $csvPath = str_replace('\\', '_', strtolower($relativePath) . '.csv');
        $file = \Disk\File::getFromStorage(Session::get('user') . DS . 'grid_export' . DS . $csvPath);
        //remove file to avoid error
        $file->remove();
        $file->save(utf8_decode($csv));

        return $file;
    }

    /**
     * Filter the columns passed
     *
     * @param array $columns
     * @param array $reportColumns
     *
     * @return array
     */
    public static function filterColumns($columns, $reportColumns = NULL)
    {
        if ($reportColumns)
        {
            foreach ($columns as $column)
            {
                if (!in_array($column->getName(), $reportColumns))
                {
                    unset($columns[$column->getName()]);
                }
            }
        }

        return $columns;
    }

    /**
     * Add suporte to make Aggregation
     *
     * @param \DataSource\DataSource $dataSource
     * @return string
     */
    public static function makeAggregation(\DataSource\DataSource $dataSource)
    {
        $aggregators = $dataSource->getAggregator();

        if (!is_array($aggregators) || count($aggregators) == 0)
        {
            return '';
        }

        $line = NULL;

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

                $value = strip_tags($dataSource->executeAggregator($aggr));
            }

            $class = '';

            if (in_array($column->getType(), array(\Db\Column::TYPE_INTEGER, \Db\Column::TYPE_DECIMAL, \Db\Column::TYPE_DATETIME, \Db\Column::TYPE_DATE)))
            {
                $class = 'alignRight';
            }

            $line[] = $value;
        }

        return implode(';', $line);
    }

}
