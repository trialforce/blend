<?php

namespace Page;

/**
 * After grid export row
 */
interface BeforeGridExportRow
{

    public function beforeGridExportRow( \Db\Model $item, $line );
}
