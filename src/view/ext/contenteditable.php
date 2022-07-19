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
        $this->setData('create-menu', 'true');
    }

    protected function createContent($id, $innerHtml = null)
    {
        $this->div = new \View\Div($id . '-content', $innerHtml, 'input');
        $this->div->attr('contenteditable', 'true');

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

    public function getCreateMenu()
    {
        return $this->getData('create-menu');
    }

    public function setCreateMenu($createMenu)
    {
        if (!$createMenu)
        {
            $this->removeAttr('data-create-menu');
        }
        else
        {

            $this->setData('create-menu', 'true');
        }
    }

}
