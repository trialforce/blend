<?php

namespace Debug;

class Tool extends \View\Layout
{

    public function __construct()
    {
        parent::__construct(null, true);

        $content[] = new \View\Style(null, $this->getCss());
        $content[] = new \Debug\SqlTable();

        $this->append($content);
    }

    public function getCss()
    {
        $css = '

table {
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
';
        return $css;
    }

    public function saveToStorage()
    {
        $html = $this->saveHTML();

        $file = \Disk\File::getFromStorage(\DataHandle\Session::get('user') . '/debugtool.html');
        $file->append($html);
    }

    public static function create()
    {
        $bar = new \Debug\Tool();
        $bar->saveToStorage();

        return $bar;
    }

}
