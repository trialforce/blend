<?php

namespace Page;

/**
 * Before grid create row
 */
interface BeforeGridCreateRow
{

    public function beforeGridCreateRow($item, $line, $tr);
}
