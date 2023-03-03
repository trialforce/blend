<?php

namespace Debug;

class Head extends \View\Div
{

    public function __construct(\Debug\Data $data)
    {
        parent::__construct();

        $tr = [];
        $tr[] = new \View\Tr(null, [new \View\Th(null, $data->requestMethod.': '.$data->host.' - '.$data->requestUri), new \View\Td(null, $data->dateTime->format(\Type\DateTime::MASK_FORMATED_HOUR))]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'Memory Limit'), new \View\Td(null, $data->memoryLimit)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'Memory allocated'), new \View\Td(null, $data->memoryAllocated)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'Memory used'), new \View\Td(null, $data->memoryUsed)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'Server time'), new \View\Td(null,$data->timeServer) ]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'PHP time'), new \View\Td(null, $data->timePhp)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'SQL time'), new \View\Td(null, $data->timeSql)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'SQL Count'), new \View\Td(null, $data->sqlCount)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'SQL Count Repeated'), new \View\Td(null, $data->sqlCountRepeated)]);
        $tr[] = new \View\Tr(null, [new \View\Th(null, 'SQL Count Slow'), new \View\Td(null, $data->sqlCountSlow)]);

        $table = new \View\Table('memory', $tr);

        $content = [];
        $content[] = $table;

        $this->append($content);
    }

}
