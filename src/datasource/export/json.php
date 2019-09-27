<?php

namespace DataSource\Export;

use DataHandle\Session;

/**
 * Bridge from datasource to json
 */
class Json
{

    public static function create(\DataSource\DataSource $dataSource, $relativePath, $reportColumns = NULL, $pageSize = NULL)
    {
        //pagesize is not used in html format
        $pageSize = null;
        $dataSource = \DataSource\Export\Csv::filterColumns($dataSource, $reportColumns);
        $columns = $dataSource->getColumns();
        $data = $dataSource->getData();

        $json = array();

        foreach ($data as $item)
        {
            $result = new \stdClass;

            foreach ($columns as $column)
            {
                $colunName = $column->getLabel();
                $value = \Component\Grid\Column::getColumnValue($column, $item);
                $result->$colunName = $value;
            }

            $json[] = $result;
        }

        $file = \Disk\File::getFromStorage(Session::get('user') . DS . 'grid_export' . DS . str_replace('\\', '_', strtolower($relativePath) . '.json'));
        //remove file to avoid error
        $file->remove();
        $file->save(\Disk\Json::encode($json));

        return $file;
    }

}
