<?php

namespace Debug;

class Head extends \View\Div
{

    public function __construct($id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL)
    {
        parent::__construct($id, $innerHtml, $class, $father);

        $serverTime = \Misc\Timer::getGlobalTimer()->stop()->diff();
        $sqlTime = \Db\Conn::$totalSqlTime;

        $tr = [];
        $tr[] = new \View\Tr(nul, [new \View\TH(null, \DataHandle\Server::getInstance()->getRequestUri(true)), new \View\Td(null, \Type\DateTime::now()->format(\Type\DateTime::MASK_FORMATED_HOUR))]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'Memory Limit'), new \View\Td(null, ini_get('memory_limit') . '')]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'Memory alocated'), new \View\Td(null, \Type\bytes::get(memory_get_usage(true)) . '')]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'Memory used'), new \View\Td(null, \Type\bytes::get(memory_get_peak_usage(true) . ''))]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'Server time'), new \View\Td(null, \Type\Decimal::get($serverTime)->setDecimals(4))]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'PHP time'), new \View\Td(null, \Type\Decimal::get($serverTime - $sqlTime)->setDecimals(4))]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'SQL time'), new \View\Td(null, \Type\Decimal::get($sqlTime)->setDecimals(4))]);
        $tr[] = new \View\Tr(nul, [new \View\TH(null, 'SQL Count'), new \View\Td(null, count(\Db\Conn::getSqlLog()))]);

        $table = new \View\Table('memory', $tr);

        $content = [];
        $content[] = $table;

        $this->append($content);
    }

}
