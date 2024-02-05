<?php
namespace FastDom;

/**
 * Fast Dom Element
 * Does not support attribute nameaspaces
 * Does not support attribute nones (DomAttr)
 * Does not supporte method setIdAttribute (id is always id)
 * Does not supoort getLineNo()
 *
 */
class Element extends \FastDom\Node
{
    public $attributes = [];

    public function __construct($tagName, $id = null, $childs = null)
    {
        parent::__construct();
        $this->tagName = $tagName;
        $this->nodeName = $tagName;

        if (!is_null($id))
        {
            $this->setAttribute('id', $id);
        }

        $this->append($childs);
    }

    /**
     * Receive an string with all element attributes e convert it to attributes in element
     *
     * @param string $stringAttributes
     * @return $this
     */
    public function parseAttributes(string $stringAttributes)
    {
        // Parse attributes
        if (preg_match_all('/(\w+)="([^"]*)"/', $stringAttributes, $attrMatches, PREG_SET_ORDER))
        {
            foreach ($attrMatches as $attrMatch)
            {
                $this->setAttribute($attrMatch[1],$attrMatch[2]);
            }
        }

        return $this;
    }

    /**
     * Adds new attribute node to element
     *
     * @param string $attribute The name of the attribute.
     * @param mixed $value
     * @return $this
     */
    public function setAttribute($attribute, $value)
    {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * Returns value of attribute
     *
     * @param string $attribute the name of the attribute
     * @return mixed|null the value of the attribute
     */
    public function getAttribute($attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    public function removeAttribute($attribute)
    {
        if (isset($attributes[$attribute]))
        {
            unset($attributes[$attribute]);
        }

        return $this;
    }

    /**
     * Checks to see if attribute exists
     * @param string $attribute attribute name
     * @return bool true on success or false on failure.
     */
    public function hasAtribute($attribute)
    {
        return isset($this->attributes[$attribute]);
    }

    /**
     * Checks if node has attributes
     *
     * @return bool true on success or false on failure.
     */
    public function hasAttributes()
    {
        return count($this->attributes) > 0;
    }

    //getElementsById()
    //getElementsByTagName()

    /**
     * Prepends one or many nodes to the list of children before the first child node.
     *
     * @param mixed $nodes
     * @return $this;
     */
    public function prepend($nodes)
    {

        return $this;
    }

    public function remove()
    {

    }

    /**
     * Add passed node(s) before the current node
     * @param mixed $node
     *
     * @return void
     */
    public function before($node)
    {

    }

    /**
     * Add passed node(s) after the current node
     *
     * @param mixed $node
     * @return void
     */
    public function after($node)
    {

    }

    /**
     *
     * @return void
     */
    public function replaceWith($node)
    {

    }

    public function render()
    {
        $attributes = '';

        if (count($this->attributes)>0)
        {
            $attributes = ' ';

            foreach ($this->attributes as $attribute => $value)
            {
                $attributes .= $attribute . '="' . $value . '" ';
            }
        }

        $content = '';

        if (count($this->childNodes) == 1 && is_string($this->childNodes[0]))
        {
            $content .= $this->childNodes[0];
        }
        else
        {
            for ($i = 0; $i < count($this->childNodes); $i++)
            {
                $content .= "\r\n" . $this->childNodes[$i];
            }
        }

        //@todo simple tag input, meta, link
        return '<'.$this->tagName.rtrim($attributes).'>'.$content.'</'.$this->tagName.'>'."\r\n";
    }

    /**
     * Canonicalize nodes to a string
     * Calls render
     *
     * @return string
     */
    public function C14N()
    {
        return $this->render();
    }

    /**
     * Return the string representation, same as c14 or render
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}