<?php

namespace DataSource\Export;

//big data explode default time
set_time_limit(0);
ini_set('memory_limit', '-1');

use DataHandle\Session;

/**
 * Bridge from datasource to pdf
 */
class Pdf extends Html
{

    /**
     * Export datasource to pdf
     *
     * @param \DataSource\DataSource $dataSource
     * @param string $relativePath
     * @param array $reportColumns
     * @param string $pageSize
     * @return \Disk\File
     */
    public static function create(\DataSource\DataSource $dataSource, $relativePath, $reportColumns = NULL, $pageSize = NULL)
    {
        $layout = self::generate($dataSource, $reportColumns);

        $path = str_replace('\\', '_', strtolower($relativePath) . '.pdf');
        $file = \Disk\File::getFromStorage(Session::get('user') . DS . 'grid_export' . DS . $path);
        //remove the file to avoid error (if exists)
        $file->remove();

        $reportTool = new \ReportTool\Engine();
        $reportTool->getLayout()->loadHTML(utf8_encode($layout));
        $reportTool->setPageSize($pageSize ? $pageSize : 'A4');
        $reportTool->setExportFile($file);
        $reportTool->setFooter(' ');
        $reportTool->setHeader(' ');

        $fileOut = $reportTool->generateFile('pdf');

        return $fileOut;
    }

}
