<?php

namespace View\Ext;

use View\Div;
use View\A;

/**
 * Html tab component
 */
class Tab extends \View\View
{

    protected $head;
    protected $body;

    /**
     * Internal tab count (don't trust this information if is ajax)
     * @var int
     */
    protected $tabCount = 0;

    /**
     * Construct a tab
     *
     * @param string $id
     * @param string $class
     * @param \DOMElement $father
     * @throws \Exception
     */
    public function __construct($id, $class = NULL, $father = NULL)
    {
        parent::__construct('div', NULL, NULL, 'tab ' . $class, $father);
        $this->setId($id)->setServerClass();

        $this->head = new Div($id . 'Head', NULL, 'tabHead clearfix', $this);
        $this->body = new Div($id . 'Body', NULL, 'tabBody clearfix', $this);

        if ($this->getSelectedTab())
        {
            $this->select($this->getSelectedTab());
        }
    }

    public function getSelectedTab()
    {
        return \DataHandle\Request::get('selectedTab');
    }

    /**
     * Add and tab item to tab
     *
     * @param string $id id
     * @param string|array $label Label
     * @param mixed $innerHtml content
     * @throws \Exception
     */
    public function add($id, $label, $innerHtml = NULL, $makeSelect = true, $icon = null)
    {
        $title = [];

        if ($icon)
        {
            $title[] = new \View\Ext\Icon($icon, null, null, 'tab-icon');
            $title[] = new \View\Span($id . 'Title', $label, 'tab-title');
        }
        else
        {
            //background compatibilty
            $title = $label;
        }

        $link = new A($id . 'Label', $title, '#', 'item', NULL);
        $link->setAjax(FALSE)->click("return selectTab('{$id}');");

        $this->head->append($link);

        $bodyItem = new Div($id, $innerHtml, 'item clearfix');
        $bodyItem->hide();

        $this->body->append($bodyItem);

        //auto select first tab
        if (!$this->getOutputJs() && $this->tabCount == 0 && $makeSelect && !$this->getSelectedTab())
        {
            $this->select($id);
        }

        $this->tabCount++;

        return $bodyItem;
    }

    /**
     * Return current tab count
     *
     * @return int
     */
    public function getTabCount()
    {
        return $this->tabCount;
    }

    /**
     * Mark as "selected" some tab
     *
     * @param string $id
     */
    public function select($id)
    {
        self::$dom->byId($id . 'Label')->addClass('selected');
        self::$dom->byId($id)->show();

        \App::addJs("selectTab('{$id}')");
    }

    /**
     * Static select a tab
     *
     * @param string $id
     */
    public static function selectTab($id)
    {
        \App::addJs("selectTab('{$id}')");
    }

}
