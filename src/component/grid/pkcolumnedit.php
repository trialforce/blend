<?php

namespace Component\Grid;

/**
 * Coluna prontina para chave primária
 * FIXME Pesadinha também
 */
class PkColumnEdit extends \Component\Grid\EditColumn
{

    public function __construct($name = NULL, $label = NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column::TYPE_INTEGER)
    {
        parent::__construct($name, $label, $align, $dataType);
        $this->setIdentificator(TRUE)->setRender(TRUE)->setRenderInDetail(FALSE);
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $grid = $this->getGrid();
        $identificator = $grid->getIdentificatorColumn();
        $idValue = \DataSource\Grab::getUserValue($identificator, $item);
        $url = $this->getEditUrl($item);

        if ($tr)
        {
            $tr->setAttribute('ondblclick', 'p(\'' . $url . '\');');
            $tr->setData('model-id', $idValue);

            if ($grid->getCanMakeTrDetail())
            {
                $tr->setAttribute('onclick', 'return grid.openTrDetail(this);');
            }

            $td->addClass('pkColumnEdit');
        }

        $content = array();
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
                $link = new \View\Ext\LinkButton('action-link-' . $action->getId(), $action->getIcon(), null, $action->getParsedUrl());
                $link->setData('model-id', $idValue);
                $link->setClass('pkColumnEdit');

                $content[] = $link;
            }
        }

        return $content;
    }

}
