<?php

namespace Page;

/**
 * After grid create cell
 */
interface AfterGridCreateCell
{

    public function afterGridCreateCell( \Component\Grid\Column $column, \Db\Model $item, $line, \View\Tr $tr, \View\Td $td );
}
