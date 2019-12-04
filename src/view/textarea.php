<?php

namespace View;

/**
 * Html textArea element.
 *
 * Can make an html editor using niceditor.
 *
 */
class TextArea extends \View\View
{

    /**
     * Construct the textarea
     *
     * @param string $idName
     * @param string $value
     */
    public function __construct($idName = NULL, $value = NULL, $class = NULL)
    {
        parent::__construct('textarea', $idName, $value, $class);
        //a simple default value
        $this->setRows(4);
    }

    /**
     * Define the value
     *
     * @param string $value
     */
    public function setValue($value = NULL)
    {
        $this->clearChildren();
        $this->append($value);
    }

    /**
     * Return the value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->textContent;
    }

    /**
     * Define the quantity of rows
     *
     * @param int $rows
     *
     * @return \View\TextArea
     */
    public function setRows($rows)
    {
        $this->setAttribute('rows', $rows);

        return $this;
    }

    /**
     * Return the quantity of rows
     *
     * @return int
     */
    public function getRows()
    {
        return $this->getAttribute('rows');
    }

    public function setReadOnly($readOnly, $setInChilds = FALSE)
    {
        parent::setReadOnly($readOnly, $setInChilds);

        //suporta editor html caso exista
        if ($this->getData('type') === 'htmlEditor')
        {
            \App::addJs("setTimeout('if (isset(\"nicEditors\") && nicEditors.findEditor(\"{$this->getId()}\")) { nicEditors.findEditor(\"{$this->getId()}\").disable(); }',500);");
        }
    }

    /**
     * Transform the textarea in an html editor
     * @deprecated since version 2019-09-22
     *
     * TODO optimize to simulataneos editors
     *
     * @param string $maxHeight
     * @param array   $extraParam
     */
    public function makeHtmlEditor($maxHeight = NULL, $extraParam = NULL)
    {
        $this->css('min-height', '100px');
        $this->setData('type', 'htmlEditor');

        //only add property if necessary
        if ($maxHeight)
        {
            $maxHeight = ",maxHeight : $maxHeight";
        }
        if ($extraParam)
        {
            $extraParam = "," . $extraParam;
        }

        $id = $this->getAttribute('id');

        //create the nicEditor
        \App::addJs("new nicEditor({iconsPath : $('base').attr('href')+'/theme/img/nicEditorIcons.gif'{$maxHeight}{$extraParam}}).panelInstance('{$id}');");

        // insert the father in a new div taking textarea classes
        if ($this->getContain())
        {
            $htmlEditor = new \View\Div(NULL, NULL, 'htmlEditor ' . $this->getClass());
            $this->getContain()->append($htmlEditor);
            $htmlEditor->append($this);
        }

        return $this;
    }

}
