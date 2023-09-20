<?php

namespace View;

/**
 * Html textArea element.
 *
 */
class TextArea extends \View\View
{

    /**
     * Construct the textarea
     *
     * @param string|null $id
     * @param string|null $value
     * @param string|null $class
     * @throws \Exception
     */
    public function __construct($id = NULL, string $value = NULL, string $class = NULL)
    {
        parent::__construct('textarea', $id, $value, $class);
        $this->setName($id);
        $this->setRows(4);  //a simple default value
    }

    /**
     * Define the value
     *
     * @param string $value
     */
    public function setValue($value = NULL)
    {
        if ($this->getOutputJs())
        {
            $valueSlashes = str_replace(array("\r\n", PHP_EOL, "\r", "\n"), '\n', $value);
            \App::addJs($this->getSelector() . ".val('$valueSlashes')");
        }
        else
        {
            $this->clearChildren();
            $this->append($value);
        }
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
        return intval($this->getAttribute('rows'));
    }
}
