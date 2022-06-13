<?php

namespace Component\Grid;

/**
 * Column used to a represent a primary key in grid
 */
class PkColumnEdit extends \Component\Grid\EditColumn
{

    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column\Column::TYPE_INTEGER)
    {
        parent::__construct($name, $label, $align, $dataType);
        $this->setIdentificator(TRUE)->setRender(TRUE)->setRenderInDetail(FALSE);
    }

    protected function getIdValue($item)
    {
        $grid = $this->getGrid();
        $identificator = $grid->getIdentificatorColumn();
        $idValue = \DataSource\Grab::getUserValue($identificator, $item);

        return $idValue;
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $url = $this->getEditUrl($item);
        $idValue = $this->getIdValue($item);

        if ($tr && $url)
        {
            $tr->setAttribute('ondblclick', 'p(\'' . $url . '\');');
            $tr->setData('model-id', $idValue);
            $td->addClass('pkColumnEdit');
        }

        $content = $this->mountActions($item, $line);

        return new \View\Div(null, $content, 'grid-action-column-holder');
    }

    protected function mountActions($item, $line)
    {
        $content = [];
        $idValue = $this->getIdValue($item);
        $actions = $this->getGrid()->getActions();

        if (isIterable($actions))
        {
            foreach ($actions as $action)
            {
                $action instanceof \Component\Action\Action;

                //don't render group in grid action
                if (!$action->getRenderInGrid())
                {
                    continue;
                }

                $action->setPk($idValue);
                $action->setModel($item);
                $link = $action->mountButtonGrid($line);

                $content[] = $link;
            }
        }

        return $content;
    }

}
