<?php

namespace Debug;

class SqlTable extends \View\Div
{

    public function __construct(\Debug\Data $data)
    {
        parent::__construct('sqlTableOut');
        $logs = $data->getSqlLog();

        if (!is_array($logs))
        {
            return;
        }

        \Debug\SqlFormatter::$word_attributes = 'style="color: #6f8000;"';
        \Debug\SqlFormatter::$backtick_quote_attributes = 'style="color: #6f8000;"';
        \Debug\SqlFormatter::$reserved_attributes = 'style="font-weight:bold; color: #0000ff;"';

        $table = new \View\Table('sqlTable');
        $tr = [];

        $caption = [];
        $caption[] = "SQL queries";
        $caption[] = $btn = new \View\Button(null, 'Open all', "openAll()");
        $btn->css('float', 'right');

        $tr[] = new \View\Caption(null, $caption);

        $th = [];
        $th[-1] = new \View\Th(null, '#');
        $th[-1]->css('width', '1.0%');

        $th[0] = new \View\Th(null, 'Create');
        $th[0]->css('width', '6.66%');

        $th[1] = new \View\Th(null, 'Conn');
        $th[1]->css('width', '5.00%');

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
            $sql = \Debug\SqlFormatter::format($log->sql);
            $string = '<pre id="sql-pre-' . $idx . '" onclick="this.classList.toggle(\'open\')" style="color: black; background-color: white; height: 14px;overflow: hidden;">';
            $sql = str_replace('<pre style="color: black; background-color: white;">', $string, $sql);

            $color = '';

            if ($log->repeated)
            {
                $color = 'orange';
            }

            if ($log->slowQuery === true)
            {
                $color = 'red';
            }

            $td = [];
            $td[] = new \View\Td(null, $idx + 1);
            $td[] = new \View\Td(null, \Type\DateTime::get($log->create)->format(\Type\DateTime::MASK_FORMATED_HOUR));
            $td[] = new \View\Td(null, $log->idConn);
            $td[] = new \View\Td(null, $log->logId);
            $td[] = new \View\Td(null, $log->time);
            $td[] = new \View\Td(null, $log->result);
            $td[] = new \View\Td('sql-' . $idx, $sql);

            $tr[] = $myTr = new \View\Tr(null, $td);

            if ($color)
            {
                $myTr->css('background-color', $color);
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

        $content = [];
        $content[] = $table;
        $content[] = new \View\Script(null, "function openAll()
{
    var pres = document.querySelectorAll('pre');
    for (var i = 0; i<pres.length; i++)
    {
        pres[i].classList.toggle('open');
    }
}");

        $this->html($content);
    }

}
