<?php

namespace Component\Grid;

/**
 * Um link qualquer que leve essa chave primária
 */
class LinkColumn extends \Component\Grid\Column
{

    /**
     * Url para onde será mandado use :? para coringa da chave primária
     *
     * @var string
     */
    protected $url;

    /**
     * Ícone que aparece na listagem
     *
     * @var string
     */
    protected $icon;
    protected $hint;
    protected $target;
    protected $ajax = FALSE;

    public function __construct($name, $label = \NULL, $align = Column::ALIGN_LEFT, $dataType = \Db\Column\Column::TYPE_VARCHAR)
    {
        parent::__construct($name, $label, $align, $dataType);
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function setTarget($target)
    {
        $this->target = $target;
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

    public function getHint()
    {
        return $this->hint;
    }

    public function setHint($hint)
    {
        $this->hint = $hint;
        return $this;
    }

    public function getAjax()
    {
        return $this->ajax;
    }

    public function setAjax($ajax)
    {
        $this->ajax = $ajax;
        return $this;
    }

    public function getValue($item, $line = NULL, \View\View $tr = NULL, \View\View $td = NULL)
    {
        $line = \NULL;
        $originalValue = parent::getValue($item, $line, $tr, $td);

        $value[] = strip_tags($originalValue.'');
        
        if (strlen($this->getIcon().'') > 0)
        {
            $value[] = new \View\Ext\Icon($this->getIcon());
        }
        else
        {
            $value[] = $this->getLabel();
        }

        $url = $this->replaceDataInString($this->getUrl(), $item);
        $link = new \View\A('edit', $value, $url, null, $this->getTarget());

        if ($this->getAjax())
        {
            $link->setAjax(TRUE);
        }

        if ($this->getHint())
        {
            $link->setTitle($this->getHint());
        }

        if ($this->getFixedHeight())
        {
            $view = new \View\Div(NULL, $link, 'fixedHeight');
            return $view;
        }

        return $link;
    }

}
