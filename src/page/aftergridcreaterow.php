<?php

namespace Page;

/**
 * After grid create row
 */
interface AfterGridCreateRow
{

    public function afterGridCreateRow( $item, $line, \View\Tr $tr );
}
