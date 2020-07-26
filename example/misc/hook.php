<?php

require 'config.php';

date_default_timezone_set("America/Sao_Paulo");
mb_internal_encoding("UTF-8");
ini_set("display_errors", "1");

echo '<pre>';

class MyModel
{

    public function save($arg1, $arg2)
    {
        $this->save = 0;
        \Misc\Hook::execute('MyModel', 'save', 'before', $this);

        echo "MyModel->save($arg1, $arg2)\r\n";
        $this->save = 1;

        \Misc\Hook::exec('after');
    }

    public static function estatico($arg1, $arg2)
    {
        \Misc\Hook::exec('before');
        echo "MyModel::estatico($arg1, $arg2)\r\n";
        \Misc\Hook::exec('after');
    }

}

\Misc\Hook::add('MyModel', 'save', 'before', function($model)
{
    echo "MyModel->hook save before ($model->save) \r\n";
});

\Misc\Hook::add('MyModel', 'save', 'before', function($model)
{
    echo "MyModel->hook save before 2 ($model->save) \r\n";
});

\Misc\Hook::add('MyModel', 'save', 'after', function($model)
{
    echo "MyModel->hook save after ($model->save) \r\n";
});

\Misc\Hook::add('MyModel', 'estatico', 'before', 'funcao');

function funcao()
{
    echo "MyModel->hook estatico after \r\n";
}

$myModel = new MyModel();
$myModel->save('a', 'b');
MyModel::estatico('1', '2');
