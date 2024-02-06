<?php
namespace FastDom;

/**
 * DomText
 */
class Text extends \FastDom\Node
{
    private $textContent;

    public function __construct($textContent)
    {
        $this->textContent = $textContent;
    }

    public function render()
    {
        return $this->textContent;
    }

    public function __toString()
    {
        return $this->render();
    }
}