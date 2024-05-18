<?php

namespace View\Ext;

/**
 * Simple Content editable div
 *
 */
class ContentEditable extends \View\Div
{

    /**
     * The real posted content
     * @var \View\Input
     */
    protected $input;
    /**
     * @var \View\Div the content editable element
     */
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

        $this->input = new \View\Input($id, \View\Input::TYPE_HIDDEN, self::treatValue($innerHtml));

        $content = [];
        $content[] = $this->div;
        $content[] = $this->input;

        return $content;
    }

    public function setValue($value)
    {
        $this->div->html($value);
        $this->input->val(self::treatValue($value));
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

    /**
     * Treat the value to make html work inside html
     *
     * @param $value
     * @return string
     */
    private function treatValue($value)
    {
        return htmlentities($value);
    }

}
