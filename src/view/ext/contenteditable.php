<?php

namespace View\Ext;

/**
 * Simple Content editable div
 *
 */
class ContentEditable extends \View\Div
{

    protected $input;
    protected $div;

    public function __construct($id = \NULL, $innerHtml = \NULL, $class = \NULL, $father = \NULL)
    {
        $content = $this->createContent($id, $innerHtml);
        parent::__construct($id . '-holder', $content, 'content-editable');
    }

    protected function createContent($id, $innerHtml = null)
    {
        $this->div = new \View\Div($id . '-content', $innerHtml, 'input');
        $this->div->attr('contenteditable', 'true');
        $this->div->blur("$('#$id').val($('#$id-content').html());");


        $this->input = new \View\Input($id, \View\Input::TYPE_HIDDEN, $innerHtml);

        $content = [];
        $content[] = $this->div;
        $content[] = $this->input;

        return $content;
    }

    public function setValue($value)
    {
        $this->div->html($value);
        $this->input->val($value);
    }

}
