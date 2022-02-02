<?php

namespace Debug;

class SqlTable extends \View\Div
{

    protected $slowQueryTime = 0.1;

    public function __construct()
    {
        $logs = \Db\Conn::getSqlLog();
        parent::__construct('sqlTableOut');

        if (!is_array($logs))
        {
            return;
        }

        $table = new \View\Table('sqlTable');
        $tr = [];
        $tr[] = new \View\Caption(null, \Type\DateTime::now()->format(\Type\DateTime::MASK_FORMATED_HOUR) . ' - ' . \DataHandle\Server::getInstance()->getRequestUri(true));

        $th = [];
        $th[0] = new \View\Th(null, 'Create');
        $th[0]->css('width', '6.66%');

        $th[1] = new \View\Th(null, 'Conn');
        $th[1]->css('width', '6.00%');

        $th[2] = new \View\Th(null, 'Log');
        $th[2]->css('width', '12.66%');

        $th[3] = new \View\Th(null, 'Time');
        $th[3]->css('width', '12.66%');

        $th[4] = new \View\Th(null, 'Result');
        $th[4]->css('width', '10.%');

        $th[5] = new \View\Th(null, 'Sql');
        $th[5]->css('width', '52%');

        $tr[] = new \View\Tr(null, $th);

        $totalTime = 0;

        foreach ($logs as $idx => $log)
        {
            $td = [];
            $td[0] = new \View\Td(null, \Type\DateTime::get($log->create)->format(\Type\DateTime::MASK_FORMATED_HOUR));
            $td[1] = new \View\Td(null, $log->idConn);
            $td[2] = new \View\Td(null, $log->logId);
            $td[3] = new \View\Td(null, $log->time);
            $td[4] = new \View\Td(null, $log->result);
            $td[5] = new \View\Td(null, nl2br($log->sql));

            $tr[] = $myTr = new \View\Tr(null, $td);

            if ($log->time > $this->getSlowQueryTime())
            {
                $myTr->css('background-color', '#efadad');
            }

            $totalTime += $log->time;
        }

        $td = [];
        $td[0] = new \View\Th(null, count($logs));
        $td[1] = new \View\Th(null, '');
        $td[2] = new \View\Th(null, '');
        $td[3] = new \View\Th(null, $totalTime);
        $td[4] = new \View\Th(null, '');
        $td[5] = new \View\Th(null, '');

        $tr[] = new \View\Tr(null, $td);

        $table->html($tr);

        $this->html($table);
    }

    public function getSlowQueryTime()
    {
        return $this->slowQueryTime;
    }

    public function setSlowQueryTime($slowQueryTime)
    {
        $this->slowQueryTime = $slowQueryTime;
        return $this;
    }

}
