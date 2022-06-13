<?php

namespace Debug;

class Tool extends \View\Layout
{

    public function __construct()
    {
        parent::__construct(null, true);

        $this->strictErrorChecking = FALSE;
        $this->loadHTML($this->getHtml());

        $content[] = new \Debug\Head();
        $content[] = new \Debug\SqlTable();
        $this->byId('body')->append($content);
    }

    public function getHtml()
    {
        $html = '
<!DOCTYPE html>
<html>
    <head>
        <title>Debug bar</title>
    </head>
    <style>

    table {
        font-family: "Arial";
        margin-top: 20px;
        width: 100%;
        border-collapse: collapse ;
        border: solid 1px #111;
        font-size: 12px;
    }

    caption {
        padding: 10px;
        border: solid 1px #111;
    }

    table th, table td {
        padding: 6px;
        border: solid 1px #111;
    }

    pre.open {
        height: auto !important;
    }

    </style>
    <body id="body">
    </body>
</html>
';
        return $html;
    }

    public function getFile()
    {
        return \Disk\File::getFromStorage(\DataHandle\Session::get('user') . '/debugtool.html');
    }

    public function saveToStorage()
    {
        $html = $this->saveHTML();

        $file = $this->getFile();
        $file->save($html);

        return $file;
    }

    public function open()
    {
        $file = $this->saveToStorage();
        $rand = rand();
        echo "<script>window.open('{$file->getUrl()}?_={$rand}');</script>";
    }

    public static function start()
    {
        \Log::setLogSql(true);
        \Misc\Timer::activeGlobalTimer();
    }

    public static function create()
    {
        return new \Debug\Tool();
    }

}
