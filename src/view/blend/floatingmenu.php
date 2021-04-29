<?php

namespace View\Blend;

/**
 * A Simple floating/popup menu
 */
class FloatingMenu extends \View\Ul
{

    /**
     * Create a simple Floating Menu
     *
     * @param string $id
     * @param mixed $innerHtml
     * @param string $class
     */
    public function __construct($id = NULL, $innerHtml = NULL, $class = NULL)
    {
        parent::__construct($id, $innerHtml, $class);
        $this->addClass('blend-floating-menu');
    }

    public function addContent($id, $content, $class = NULL, $title = NULL, $group = NULL)
    {
        $item = new \View\Li($id, $content, $class);
        $item->setData('group', $group . '');
        $this->append($item);

        return $this;
    }

    /**
     * Add and item to menu
     *
     * @param string $icon icon
     * @param sring $label label
     * @param string $action action
     * @param string $class class
     * @param string $title tittle
     * @param bool $formChangeAdvice form change advice
     *
     * @return $this
     */
    public function addItem($id, $icon, $label, $action, $class = NULL, $title = NULL, $group = NULL)
    {
        $icon = is_string($icon) ? new \View\Ext\Icon($icon) : $icon;
        $field = new \View\Span(null, array($icon, $label));

        $item = new \View\Li($id, $field, $class);
        $item->setTitle($title ? $title : $label);
        $item->setData('group', $group . '');

        if ($action)
        {
            $item->click($action);
        }

        $this->append($item);

        return $item;
    }

    /**
     * Add and item to menu
     *
     * @param string $icon
     * @param sring $label
     * @param string $action
     * @param string $class
     * @param string $title
     * @param bool $formChangeAdvice
     *
     * @return $this
     */
    public function addItemLink($id, $icon, $label, $action, $class = NULL, $title = NULL, $group = NULL)
    {
        $field = new \View\A(null, array(new \View\Ext\Icon($icon), $label), $action);
        $field->setTarget('_BLANK');

        $item = new \View\Li($id, $field, $class);
        $item->setTitle($title ? $title : $label);
        $item->setData('group', $group . '');
        $this->append($item);

        return $this;
    }

    /**
     * Add an action to the floating menu
     *
     * @param \Component\Action\Action $action
     * @param integer $pk
     * @return $this
     */
    public function addAction(\Component\Action\Action $action, $pk = null)
    {
        $action->setPk($pk);

        //without link/url
        if (!$action->getUrl())
        {
            $this->addContent($action->getId(), $action->getLabel(), $action->getClass(), $action->getTitle(), $action->getGroupInEdit());
        }
        //in case it's a JS function
        else if (stripos($action->getUrl(), '(') > 0)
        {
            $this->addItem($action->getId(), $action->getIcon(), $action->getLabel(), $action->getParsedUrl(), $action->getClass(), $action->getTitle(), $action->getGroupInEdit());
        }
        //in case it's a link
        else if (stripos($action->getUrl(), 'p(') === 0)
        {
            $this->addItem($action->getId(), $action->getIcon(), $action->getLabel(), $action->getParsedUrl(), $action->getClass(), $action->getTitle(), $action->getGroupInEdit());
        }
        else
        {
            $this->addItemLink($action->getId(), $action->getIcon(), $action->getLabel(), $action->getParsedUrl(), $action->getClass(), $action->getTitle(), $action->getGroupInEdit());
        }

        return $this;
    }

    public function addActions($actions, $pk)
    {
        //transform in an array if is an action
        if (!is_array($actions))
        {
            $result = array();
            $result[] = $actions;
            $actions = $result;
        }

        foreach ($actions as $action)
        {
            $this->addAction($action, $pk);
        }
    }

}
