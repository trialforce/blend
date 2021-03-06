<?php

namespace Component\Grid;

/**
 * A Column that link to edit a item
 */
class EditColumn extends \Component\Grid\Column
{

    /**
     * Method/event called when user click in this column
     *
     * @var string
     */
    protected $editEvent = \Page\Crud::EVENT_UPDATE;

    /**
     * Link para página de edição
     *
     * @var string
     */
    protected $editPage = NULL;

    /**
     * Extra params for grid edit link
     * @var string
     */
    protected $editExtraParams = NULL;

    /**
     * if link is enabled
     *
     * @var bool
     */
    protected $linkEnabled = TRUE;

    /**
     * When you want another field as id, not the default.
     *
     * @var string
     */
    protected $identificatorColumn;

    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column\Column::TYPE_VARCHAR)
    {
        parent::__construct($name, $label, $align, $dataType);
        $this->setWidth('10px');
    }

    public function getEditEvent()
    {
        return $this->editEvent;
    }

    public function getEditPage()
    {
        $editPage = $this->editPage;

        if (!$editPage)
        {
            return $this->getGrid()->getPageName();
        }

        return $editPage;
    }

    public function setEditEvent($editEvent)
    {
        $this->editEvent = $editEvent;
        return $this;
    }

    public function setEditPage($editPage)
    {
        $this->editPage = $editPage;
        return $this;
    }

    public function getLinkEnabled()
    {
        return $this->linkEnabled;
    }

    public function setLinkEnabled($linkEnabled)
    {
        $this->linkEnabled = $linkEnabled;
        return $this;
    }

    public function getEditExtraParams()
    {
        $params = $this->editExtraParams;

        if (is_array($params))
        {
            $params = http_build_query($params);
        }

        return $params;
    }

    public function setEditExtraParams($editExtraParams)
    {
        $this->editExtraParams = $editExtraParams;
        return $this;
    }

    public function getIdentificatorColumn()
    {
        return $this->identificatorColumn;
    }

    public function setIdentificatorColumn($identificatorColumn)
    {
        $this->identificatorColumn = $identificatorColumn;
        return $this;
    }

    public function getEditUrl($item, $identificator = NULL)
    {
        $editPage = $this->getEditPage();
        $editEvent = $this->getEditEvent();

        if (!$editEvent)
        {
            return null;
        }

        $page = \View\View::getDom();

        //change edit event to VIEW, when cannot update
        if ($page instanceof \Page\Crud)
        {
            if ($editEvent == \Page\Crud::EVENT_UPDATE && !$page->verifyPermission(\Page\Crud::EVENT_UPDATE))
            {
                $editEvent = \Page\Crud::EVENT_VIEW;
            }
        }

        $identificator = $this->getGrid()->getIdentificatorColumn();

        if (!is_null($this->identificatorColumn))
        {
            $identificator = $this->identificatorColumn;
        }

        if (is_null($identificator))
        {
            throw new \Exception('Impossivel encontrar coluna identificador!');
        }

        $idValue = \DataSource\Grab::getUserValue($identificator, $item);
        $url = $editPage . '/' . $editEvent . '/' . $idValue;
        $url .= $this->getEditExtraParams() ? '?' . $this->getEditExtraParams() : '';

        return $url;
    }

    public function getHeadContent(\View\View $tr, \View\View $th)
    {
        if ($this->identificator)
        {
            $th->addClass('identificator');
        }

        return parent::getHeadContent($tr, $th);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $line = \NULL;
        $value = parent::getValue($item, $tr, $td);

        $identificator = $this->getGrid()->getIdentificatorColumn();
        $idValue = \DataSource\Grab::getUserValue($identificator, $item);
        $columnName = $this->getName();
        $elementId = 'edit' . $columnName . $idValue;

        $tr->setAttribute('ondblclick', 'p(\'' . $this->getEditUrl($item) . '\');');

        $this->makeEditable($item, $line, $tr, $td);

        if ($this->identificator)
        {
            $td->addClass('identificator');
        }

        $link = new \View\A($elementId, null, $this->getEditUrl($item), 'editColumn');
        $link->html(strip_tags($value));

        return $link;
    }

    public function listAvoidPropertySerialize()
    {
        $avoid = parent::listAvoidPropertySerialize();
        $avoid[] = 'identificatorColumn';

        return $avoid;
    }

}
