<?php

namespace Page;

/**
 * After grid create row
 */
interface BeforeGridCreateRow
{

    public function beforeGridCreateRow($item, $line, $tr);
}
