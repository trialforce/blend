<?php

namespace DataSource\Export;
//big data explode default time
set_time_limit(0);
ini_set('memory_limit', '512M');
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
        $file->save($layout . '');

        $pageSize = $pageSize ? $pageSize : 'A4';

        //remove the file to avoid error
        unlink($file->getPath());

        $mpdf = new \mPDF('utf-8', $pageSize, 0, '', 5, 5, 5, 5, 5, 5);
        $mpdf->WriteHTML(utf8_encode($layout));
        $mpdf->Output($file->getPath());

        return $file;
    }

}