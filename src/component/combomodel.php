<?php

namespace Component;

/**
 * An combo/autocomplete vinculate with a model/page
 */
abstract class ComboModel extends \Component\Combo
{

    protected $modelName = '';

    public function onCreate()
    {
        //avoid double creation
        if ($this->isCreated())
        {
            return $this->getContent();
        }

        $view = parent::onCreate();

        if ($this->getModelName())
        {

            $btn = new \View\Ext\Button('btn-combo-edit-' . $this->getId(), $this->getIconClass(), null, 'return comboModelClick(\'' . $this->getId() . '\')', 'icon-only combo-edit-btn primary');

            $view->append($btn);

            $this->byId('labelField_' . $this->getId())->addClass('combo-edit-input');
            $this->byId($this->getId())->data('model', $this->getModelName());
        }

        return $view;
    }

    public function getIconClass()
    {
        return 'edit';
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    public function setModelName($modelName)
    {
        $this->modelName = $modelName;
        return $this;
    }

}
