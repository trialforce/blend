<?php

namespace Component\Action;

/**
 * A Action is an result of an "action" of the used
 * Used in any edit page, or in the SearcGrid
 */
class Action extends \Component\Component
{

    protected $class;
    protected $icon;
    protected $label;
    protected $title;
    protected $url;
    protected $pk;
    protected $renderInEdit = true;
    protected $groupInEdit = '';
    protected $renderInGrid = false;
    protected $renderInGridDetail = false;

    /**
     * Create an action
     *
     * @param string $id the action id
     * @param string $icon the icon
     * @param string $label the label
     * @param string $url the url to make post, can be a javascript
     * @param string $class css class
     * @param string $title title (show on mouse over)
     * @param string $groupInEdit group in edit
     */
    public function __construct($id, $icon = null, $label = null, $url = null, $class = NULL, $title = NULL, $groupInEdit = NULL)
    {
        parent::__construct($id);

        //if has construct para is to construct
        if ($id && $label)
        {
            $this->setIcon($icon);
            $this->setLabel($label);
            $this->setUrl($url);
            $this->setClass($class);
            $this->setTitle($title);
            $this->setRenderInEdit(true);
            $this->setGroupInEdit($groupInEdit);
            $this->setRenderInGrid(false);
            $this->setRenderInGridDetail(false);
        }
        //else is to execute
        else
        {
            \App::dontChangeUrl();
            $this->execute();
        }
    }

    public function execute()
    {
        //does nothinh if you don't implement
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    public function getPk()
    {
        return $this->pk;
    }

    public function setPk($pk)
    {
        $this->pk = $pk;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getLabel()
    {
        return $this->label ? $this->label : $this->getId();
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getTitle()
    {
        return $this->title ? $this->title : $this->getLabel();
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getParsedUrl()
    {
        return str_replace(array(':id?', ':?'), $this->getPk(), $this->getUrl());
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getRenderInEdit()
    {
        return $this->renderInEdit;
    }

    public function setRenderInEdit($renderInEdit)
    {
        $this->renderInEdit = $renderInEdit;
        return $this;
    }

    public function getGroupInEdit()
    {
        return $this->groupInEdit;
    }

    public function setGroupInEdit($groupInEdit)
    {
        $this->groupInEdit = $groupInEdit;
        return $this;
    }

    public function getRenderInGrid()
    {
        return $this->renderInGrid;
    }

    public function setRenderInGrid($renderInGrid)
    {
        $this->renderInGrid = $renderInGrid;
        return $this;
    }

    public function getRenderInGridDetail()
    {
        return $this->renderInGridDetail;
    }

    public function setRenderInGridDetail($renderInGridDetail)
    {
        $this->renderInGridDetail = $renderInGridDetail;
        return $this;
    }

}
