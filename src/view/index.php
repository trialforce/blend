<?php

use View\View;
use View\H1;
use View\H2;
use View\H3;
use View\H4;
use View\H5;
use View\H6;
use View\A;
use View\Br;
use View\B;
use View\Strong;
use View\I;
use View\Button;
use View\Img;
use View\P;
use View\Pre;
use View\Span;
use View\Script;
use View\Tr;
use View\Td;
use View\Caption;
use View\TBody;
use View\THead;
use View\Table;
use View\Label;
use View\Ext\CheckboxDb;
use View\Input;
use View\Radio;
use View\Range;
use View\TextArea;
use View\Select;
use View\Legend;
use View\Form;
use View\Fieldset;
use View\IFrame;
use View\Ul;
use View\Ol;
use View\Head;
use View\Body;
use View\Div;
use View\Html;

define('APP_PATH', '../../');
require APP_PATH . '/lib/auto.php';

$layout = new \View\Layout();
\View\View::setDom($layout);

$head = array(
    new \View\Base('id', '/'),
    new \View\Link('#'),
    new \View\Script('//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js'),
    new \View\Title('How to use simple view elements')
);

$head[] = $metaCharset = new \View\Meta();
$metaCharset->setAttribute('http-equiv', 'Content-Type');
$metaCharset->setAttribute('content', 'text/html;charset=utf-8');

$views = array(
    new H1('h1', 'h1', 'h1'),
    new H2('h2', 'h2'),
    new H3(NULL, 'h3'),
    new H4(NULL, 'h4'),
    new H5(NULL, 'h5'),
    new H6( ),
    new A(null, 'Link', '#'),
    new Br(),
    new B(NULL, 'Bold text'),
    new Br(),
    new Strong(NULL, 'Strong text'),
    new Br(),
    new I(NULL, 'Italic text'),
    new Br(),
    new Button(NULL, 'Button', 'alert(1);'),
    new Br(),
    new Img(NULL, 'https://validator-suite.w3.org/icons/vs-blue-256.png', '100', '40', 'Alternative text'),
    new P(NULL, 'Paragraf'),
    new Pre(NULL, 'Pre'),
    new Span(NULL, 'Span'),
    new Script(NULL, "console.log('Log');"),
    new Br()
);

$tr = array(
    new Tr(NULL, array('td1', 'td2', 'td3')),
    new Tr(NULL, array('td4', 'td5', 'td6')),
    new Tr(NULL, array(new Td('td7', 'td7'), new Td('td8', 'td8'), new Td('td9', 'td9')))
);

$tableInner = array(
    $caption = new Caption(NULL, 'Table caption'),
    new THead(NULL, array('th1', 'th2', 'th3')),
    new TBody(\NULL, $tr)
);

$views[] = $table = new Table('table', $tableInner, 'table');

$table->css('width', '100%');
$table->css('border', 'solid 1px gray');
$caption->append(' - element count = ' . count($table));

$views[] = new Br();

$fields = array(new Label(NULL, 'checkbox', 'Checkbox'),
    new \View\Ext\CheckboxDb('checkbox', 1, TRUE),
    new Br(),
    new Label(NULL, 'radio1', 'Radio'),
    new Radio('radio1', 1, TRUE),
    new Radio('radio2', 1, TRUE),
    new Br(),
    new Label(NULL, 'idName', 'Input'),
    new Input('idName'),
    new Br(),
    new Label(NULL, 'range', 'Range'),
    new Range('range', 20),
    new TextArea('textarea', 'textarea'),
    new Br(),
    new Label(NULL, 'select', 'Select'),
    new Select('select', array(1 => 'One', 2 => 'two', 3 => 'Tree'), 2)
);

$views2 = array(new Fieldset(NULL, new Legend(NULL, 'Legend'), new Form('form', $fields)),
    new Br(),
    new IFrame('iframe', '/'),
    new Ul(\NULL, get_included_files()),
    new Ol(\NULL, array('li1', 'li2', 'li3')),
    new View('div', \NULL, '\View\View', 'divClass')
);

$range = $layout->byId('range')->setStep(20);

new Html(array(new Head(NULL, $head), new Body(new Div('div', array($views, $views2), 'class'))));

echo $layout;
